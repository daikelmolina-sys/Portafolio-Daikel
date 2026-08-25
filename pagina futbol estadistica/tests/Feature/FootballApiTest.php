<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tournament;
use App\Models\Matchday;
use App\Models\Team;
use App\Models\Game;
use App\Services\StandingsCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FootballApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('StatPro');
    }

    public function test_external_mock_endpoint(): void
    {
        $response = $this->getJson('/api/external-mock');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'tournaments',
            'teams',
            'matchdays',
            'matches'
        ]);
    }

    public function test_tournaments_endpoint(): void
    {
        Tournament::create([
            'name' => 'La Liga Pro',
            'season' => '2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-15'
        ]);

        $response = $this->getJson('/api/tournaments');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'La Liga Pro']);
    }

    public function test_matchdays_and_standings_and_matches_endpoints(): void
    {
        $tournament = Tournament::create([
            'name' => 'Liga Master',
            'season' => '2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-15'
        ]);

        $teamA = Team::create(['name' => 'Real Madrid', 'short_name' => 'RMA']);
        $teamB = Team::create(['name' => 'Barcelona', 'short_name' => 'BAR']);

        $md = Matchday::create([
            'tournament_id' => $tournament->id,
            'number' => 'Jornada 1',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03'
        ]);

        $game = Game::create([
            'matchday_id' => $md->id,
            'home_team_id' => $teamA->id,
            'away_team_id' => $teamB->id,
            'home_score' => 3,
            'away_score' => 1,
            'status' => 'finalizado',
            'match_date' => '2026-08-01 20:00:00'
        ]);

        // Standings calculation
        $calculator = new StandingsCalculatorService();
        $calculator->calculateForMatchday($md);

        // 1. Test Matchdays endpoint
        $resMd = $this->getJson("/api/tournaments/{$tournament->id}/matchdays");
        $resMd->assertStatus(200);
        $resMd->assertJsonCount(1);
        $resMd->assertJsonFragment(['number' => 'Jornada 1']);

        // 2. Test Standings endpoint with projections
        $resStandings = $this->getJson("/api/matchdays/{$md->id}/standings");
        $resStandings->assertStatus(200);
        $resStandings->assertJsonCount(2);
        $resStandings->assertJsonStructure([
            '*' => [
                'id',
                'tournament_id',
                'matchday_id',
                'team_id',
                'played',
                'won',
                'drawn',
                'lost',
                'goals_for',
                'goals_against',
                'goal_difference',
                'points',
                'projected_points',
                'team'
            ]
        ]);

        // 3. Test Matches endpoint
        $resMatches = $this->getJson("/api/matchdays/{$md->id}/matches");
        $resMatches->assertStatus(200);
        $resMatches->assertJsonCount(1);
        $resMatches->assertJsonFragment(['home_score' => 3, 'away_score' => 1]);
    }
}
