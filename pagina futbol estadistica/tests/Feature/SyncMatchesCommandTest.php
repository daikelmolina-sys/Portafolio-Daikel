<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SportsApiService;
use App\Models\Tournament;
use App\Models\Matchday;
use App\Models\Team;
use App\Models\Game;
use App\Models\Standing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class SyncMatchesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_matches_command_populates_database_and_calculates_standings(): void
    {
        $mockData = [
            'tournaments' => [
                [
                    'external_id' => 1,
                    'name' => 'Premier Test',
                    'season' => '2026',
                    'start_date' => '2026-08-01',
                    'end_date' => '2026-12-15'
                ]
            ],
            'teams' => [
                ['external_id' => 101, 'name' => 'Arsenal', 'short_name' => 'ARS'],
                ['external_id' => 102, 'name' => 'Chelsea', 'short_name' => 'CHE'],
            ],
            'matchdays' => [
                ['external_id' => 10, 'tournament_external_id' => 1, 'number' => 'Jornada 1', 'start_date' => '2026-08-01', 'end_date' => '2026-08-03'],
            ],
            'matches' => [
                ['external_matchday_id' => 10, 'home_team_id' => 101, 'away_team_id' => 102, 'home_score' => 2, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2026-08-01 15:00:00'],
            ]
        ];

        $mockApiService = Mockery::mock(SportsApiService::class);
        $mockApiService->shouldReceive('fetchAllData')->once()->andReturn($mockData);

        $this->app->instance(SportsApiService::class, $mockApiService);

        $this->artisan('sync:matches')
            ->assertExitCode(0);

        $this->assertDatabaseHas('tournaments', ['name' => 'Premier Test']);
        $this->assertDatabaseHas('teams', ['name' => 'Arsenal']);
        $this->assertDatabaseHas('teams', ['name' => 'Chelsea']);
        $this->assertDatabaseHas('matchdays', ['number' => 'Jornada 1']);
        $this->assertDatabaseHas('matches', ['home_score' => 2, 'away_score' => 0]);
        $this->assertDatabaseHas('standings', ['points' => 3]);
    }
}
