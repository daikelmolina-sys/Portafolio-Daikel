<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SportsApiService;
use App\Services\StandingsCalculatorService;
use App\Models\Tournament;
use App\Models\Matchday;
use App\Models\Team;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class SyncMatchesCommand extends Command
{
    protected $signature = 'sync:matches {--force : Force recalculation of frozen standings}';
    protected $description = 'Consume API to fill local DB idempotently and trigger standings calculation';

    public function handle(SportsApiService $apiService, StandingsCalculatorService $standingsService)
    {
        $this->info('Fetching data from API...');
        
        $data = $apiService->fetchAllData();

        DB::beginTransaction();
        try {
            // 1. Sync Tournaments
            $tournamentIdMap = [];
            foreach ($data['tournaments'] as $tData) {
                $tournament = Tournament::updateOrCreate(
                    ['id' => $tData['external_id']], // Assuming external_id maps 1:1 to local ID for simplicity
                    [
                        'name' => $tData['name'],
                        'season' => $tData['season'],
                        'start_date' => $tData['start_date'],
                        'end_date' => $tData['end_date'],
                    ]
                );
                $tournamentIdMap[$tData['external_id']] = $tournament->id;
            }

            // 2. Sync Teams
            $teamIdMap = [];
            foreach ($data['teams'] as $teamData) {
                // Find by name to avoid id overriding issues, or just use updateOrCreate by name
                $team = Team::updateOrCreate(
                    ['name' => $teamData['name']],
                    [
                        'short_name' => $teamData['short_name'] ?? null,
                    ]
                );
                $teamIdMap[$teamData['external_id']] = $team->id;
            }

            // 3. Sync Matchdays
            $matchdayIdMap = [];
            foreach ($data['matchdays'] as $mdData) {
                $matchday = Matchday::updateOrCreate(
                    [
                        'tournament_id' => $tournamentIdMap[$mdData['tournament_external_id']],
                        'number' => $mdData['number']
                    ],
                    [
                        'start_date' => $mdData['start_date'],
                        'end_date' => $mdData['end_date'],
                    ]
                );
                $matchdayIdMap[$mdData['external_id']] = $matchday->id;
            }

            // 4. Sync Matches
            foreach ($data['matches'] as $mData) {
                Game::updateOrCreate(
                    [
                        'matchday_id' => $matchdayIdMap[$mData['external_matchday_id']],
                        'home_team_id' => $teamIdMap[$mData['home_team_id']],
                        'away_team_id' => $teamIdMap[$mData['away_team_id']],
                    ],
                    [
                        'home_score' => $mData['home_score'],
                        'away_score' => $mData['away_score'],
                        'status' => $mData['status'],
                        'match_date' => $mData['match_date'],
                    ]
                );
            }

            DB::commit();
            $this->info('Data synced successfully.');

            // 5. Trigger Standings Calculation
            // We calculate standings for all matchdays sequentially.
            $this->info('Calculating standings...');
            $force = $this->option('force');
            foreach ($tournamentIdMap as $tId) {
                $matchdays = Matchday::where('tournament_id', $tId)->orderBy('start_date')->get();
                foreach ($matchdays as $matchday) {
                    $standingsService->calculateForMatchday($matchday, $force);
                }
            }
            $this->info('Standings calculation complete.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
