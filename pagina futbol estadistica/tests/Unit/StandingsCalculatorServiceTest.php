<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tournament;
use App\Models\Matchday;
use App\Models\Team;
use App\Models\Game;
use App\Models\Standing;
use App\Services\StandingsCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StandingsCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StandingsCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StandingsCalculatorService();
    }

    public function test_calculates_standings_with_temporal_isolation(): void
    {
        $tournament = Tournament::create([
            'name' => 'Liga Test',
            'season' => '2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]);

        $teamA = Team::create(['name' => 'Team A', 'short_name' => 'TMA']);
        $teamB = Team::create(['name' => 'Team B', 'short_name' => 'TMB']);
        $teamC = Team::create(['name' => 'Team C', 'short_name' => 'TMC']);

        $md1 = Matchday::create([
            'tournament_id' => $tournament->id,
            'number' => 'Jornada 1',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
        ]);

        $md2 = Matchday::create([
            'tournament_id' => $tournament->id,
            'number' => 'Jornada 2',
            'start_date' => '2026-08-08',
            'end_date' => '2026-08-10',
        ]);

        // MD1 match: Team A 2 - 1 Team B
        Game::create([
            'matchday_id' => $md1->id,
            'home_team_id' => $teamA->id,
            'away_team_id' => $teamB->id,
            'home_score' => 2,
            'away_score' => 1,
            'status' => 'finalizado',
            'match_date' => '2026-08-01 15:00:00',
        ]);

        // MD2 match: Team B 3 - 0 Team C (Happens after MD1)
        Game::create([
            'matchday_id' => $md2->id,
            'home_team_id' => $teamB->id,
            'away_team_id' => $teamC->id,
            'home_score' => 3,
            'away_score' => 0,
            'status' => 'finalizado',
            'match_date' => '2026-08-08 15:00:00',
        ]);

        // Calculate for MD1
        $this->service->calculateForMatchday($md1);

        $standingA = Standing::where('matchday_id', $md1->id)->where('team_id', $teamA->id)->first();
        $standingB = Standing::where('matchday_id', $md1->id)->where('team_id', $teamB->id)->first();

        $this->assertNotNull($standingA);
        $this->assertEquals(1, $standingA->played);
        $this->assertEquals(1, $standingA->won);
        $this->assertEquals(0, $standingA->lost);
        $this->assertEquals(3, $standingA->points);
        $this->assertEquals(2, $standingA->goals_for);
        $this->assertEquals(1, $standingA->goals_against);
        $this->assertEquals(1, $standingA->goal_difference);

        $this->assertNotNull($standingB);
        $this->assertEquals(1, $standingB->played);
        $this->assertEquals(0, $standingB->won);
        $this->assertEquals(1, $standingB->lost);
        $this->assertEquals(0, $standingB->points);
        // Team B's MD2 win must NOT leak into MD1
        $this->assertEquals(1, $standingB->goals_for);
        $this->assertEquals(2, $standingB->goals_against);

        // Now calculate for MD2
        $this->service->calculateForMatchday($md2);
        $standingB_md2 = Standing::where('matchday_id', $md2->id)->where('team_id', $teamB->id)->first();
        $this->assertNotNull($standingB_md2);
        $this->assertEquals(2, $standingB_md2->played);
        $this->assertEquals(1, $standingB_md2->won);
        $this->assertEquals(1, $standingB_md2->lost);
        $this->assertEquals(3, $standingB_md2->points);
        $this->assertEquals(4, $standingB_md2->goals_for);
        $this->assertEquals(2, $standingB_md2->goals_against);
        $this->assertEquals(2, $standingB_md2->goal_difference);
    }

    public function test_calculates_projections_based_on_recent_matches(): void
    {
        $tournament = Tournament::create([
            'name' => 'Liga Proyeccion',
            'season' => '2026',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]);

        $teamA = Team::create(['name' => 'Team A', 'short_name' => 'TMA']);
        $teamB = Team::create(['name' => 'Team B', 'short_name' => 'TMB']);

        $md1 = Matchday::create([
            'tournament_id' => $tournament->id,
            'number' => 'Jornada 1',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
        ]);

        $md2 = Matchday::create([
            'tournament_id' => $tournament->id,
            'number' => 'Jornada 2',
            'start_date' => '2026-08-08',
            'end_date' => '2026-08-10',
        ]);

        // MD1: Team A wins (3 pts)
        Game::create([
            'matchday_id' => $md1->id,
            'home_team_id' => $teamA->id,
            'away_team_id' => $teamB->id,
            'home_score' => 1,
            'away_score' => 0,
            'status' => 'finalizado',
            'match_date' => '2026-08-01 15:00:00',
        ]);

        // MD2: Team A draws (1 pt)
        Game::create([
            'matchday_id' => $md2->id,
            'home_team_id' => $teamB->id,
            'away_team_id' => $teamA->id,
            'home_score' => 1,
            'away_score' => 1,
            'status' => 'finalizado',
            'match_date' => '2026-08-08 15:00:00',
        ]);

        $this->service->calculateForMatchday($md1);
        $this->service->calculateForMatchday($md2);

        // Total 4 matchdays in tournament. At MD2, Team A has 4 points in 2 matches. Avg = 2 pts/game.
        // Remaining matches = 4 - 2 = 2.
        // Projected additional = 2 * 2 = 4. Total projected = 4 + 4 = 8.0
        $projection = $this->service->calculateProjection($md2, $teamA->id, 4, 5);
        $this->assertEquals(8.0, $projection);
    }
}
