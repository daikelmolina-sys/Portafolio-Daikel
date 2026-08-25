<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tournament;
use App\Models\Team;
use App\Models\Matchday;
use App\Models\Game;
use App\Services\StandingsCalculatorService;

class RealFootballDataSeeder extends Seeder
{
    /**
     * Llena la base de datos con equipos, torneos, jornadas y partidos de la vida real.
     */
    public function run(): void
    {
        $calculator = new StandingsCalculatorService();

        // 1. Torneo 1: LaLiga EA Sports
        $laliga = Tournament::updateOrCreate(
            ['name' => 'LaLiga EA Sports', 'season' => '2025/2026'],
            ['start_date' => '2025-08-15', 'end_date' => '2026-05-24']
        );

        // Equipos de LaLiga
        $teamsLaliga = [
            'RMA' => Team::updateOrCreate(['name' => 'Real Madrid'], ['short_name' => 'RMA']),
            'FCB' => Team::updateOrCreate(['name' => 'FC Barcelona'], ['short_name' => 'FCB']),
            'ATM' => Team::updateOrCreate(['name' => 'Atlético de Madrid'], ['short_name' => 'ATM']),
            'ATH' => Team::updateOrCreate(['name' => 'Athletic Club'], ['short_name' => 'ATH']),
            'VIL' => Team::updateOrCreate(['name' => 'Villarreal CF'], ['short_name' => 'VIL']),
            'BET' => Team::updateOrCreate(['name' => 'Real Betis'], ['short_name' => 'BET']),
            'RSO' => Team::updateOrCreate(['name' => 'Real Sociedad'], ['short_name' => 'RSO']),
            'SEV' => Team::updateOrCreate(['name' => 'Sevilla FC'], ['short_name' => 'SEV']),
        ];

        // Jornadas de LaLiga
        $m1 = Matchday::updateOrCreate(['tournament_id' => $laliga->id, 'number' => 1], ['start_date' => '2025-08-15', 'end_date' => '2025-08-17']);
        $m2 = Matchday::updateOrCreate(['tournament_id' => $laliga->id, 'number' => 2], ['start_date' => '2025-08-22', 'end_date' => '2025-08-24']);
        $m3 = Matchday::updateOrCreate(['tournament_id' => $laliga->id, 'number' => 3], ['start_date' => '2025-08-29', 'end_date' => '2025-08-31']);
        $m4 = Matchday::updateOrCreate(['tournament_id' => $laliga->id, 'number' => 4], ['start_date' => '2025-09-12', 'end_date' => '2025-09-14']);
        $m5 = Matchday::updateOrCreate(['tournament_id' => $laliga->id, 'number' => 5], ['start_date' => '2025-09-19', 'end_date' => '2025-09-21']);

        // Partidos J1
        Game::updateOrCreate(['matchday_id' => $m1->id, 'home_team_id' => $teamsLaliga['RMA']->id, 'away_team_id' => $teamsLaliga['SEV']->id], ['home_score' => 3, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-15 21:00:00']);
        Game::updateOrCreate(['matchday_id' => $m1->id, 'home_team_id' => $teamsLaliga['FCB']->id, 'away_team_id' => $teamsLaliga['BET']->id], ['home_score' => 2, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-16 19:00:00']);
        Game::updateOrCreate(['matchday_id' => $m1->id, 'home_team_id' => $teamsLaliga['ATM']->id, 'away_team_id' => $teamsLaliga['VIL']->id], ['home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-16 21:30:00']);
        Game::updateOrCreate(['matchday_id' => $m1->id, 'home_team_id' => $teamsLaliga['ATH']->id, 'away_team_id' => $teamsLaliga['RSO']->id], ['home_score' => 1, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-17 17:00:00']);

        // Partidos J2
        Game::updateOrCreate(['matchday_id' => $m2->id, 'home_team_id' => $teamsLaliga['BET']->id, 'away_team_id' => $teamsLaliga['RMA']->id], ['home_score' => 1, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-22 21:00:00']);
        Game::updateOrCreate(['matchday_id' => $m2->id, 'home_team_id' => $teamsLaliga['SEV']->id, 'away_team_id' => $teamsLaliga['FCB']->id], ['home_score' => 1, 'away_score' => 4, 'status' => 'finalizado', 'match_date' => '2025-08-23 19:00:00']);
        Game::updateOrCreate(['matchday_id' => $m2->id, 'home_team_id' => $teamsLaliga['VIL']->id, 'away_team_id' => $teamsLaliga['ATH']->id], ['home_score' => 2, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-23 21:30:00']);
        Game::updateOrCreate(['matchday_id' => $m2->id, 'home_team_id' => $teamsLaliga['RSO']->id, 'away_team_id' => $teamsLaliga['ATM']->id], ['home_score' => 0, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-24 17:00:00']);

        // Partidos J3
        Game::updateOrCreate(['matchday_id' => $m3->id, 'home_team_id' => $teamsLaliga['FCB']->id, 'away_team_id' => $teamsLaliga['ATM']->id], ['home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-29 21:00:00']);
        Game::updateOrCreate(['matchday_id' => $m3->id, 'home_team_id' => $teamsLaliga['RMA']->id, 'away_team_id' => $teamsLaliga['RSO']->id], ['home_score' => 4, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-30 19:00:00']);
        Game::updateOrCreate(['matchday_id' => $m3->id, 'home_team_id' => $teamsLaliga['ATH']->id, 'away_team_id' => $teamsLaliga['SEV']->id], ['home_score' => 2, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-30 21:30:00']);
        Game::updateOrCreate(['matchday_id' => $m3->id, 'home_team_id' => $teamsLaliga['VIL']->id, 'away_team_id' => $teamsLaliga['BET']->id], ['home_score' => 3, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-31 17:00:00']);

        // Partidos J4
        Game::updateOrCreate(['matchday_id' => $m4->id, 'home_team_id' => $teamsLaliga['ATM']->id, 'away_team_id' => $teamsLaliga['RMA']->id], ['home_score' => 1, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-09-12 21:00:00']);
        Game::updateOrCreate(['matchday_id' => $m4->id, 'home_team_id' => $teamsLaliga['RSO']->id, 'away_team_id' => $teamsLaliga['FCB']->id], ['home_score' => 1, 'away_score' => 3, 'status' => 'finalizado', 'match_date' => '2025-09-13 19:00:00']);
        Game::updateOrCreate(['matchday_id' => $m4->id, 'home_team_id' => $teamsLaliga['BET']->id, 'away_team_id' => $teamsLaliga['ATH']->id], ['home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-09-13 21:30:00']);
        Game::updateOrCreate(['matchday_id' => $m4->id, 'home_team_id' => $teamsLaliga['SEV']->id, 'away_team_id' => $teamsLaliga['VIL']->id], ['home_score' => 0, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-09-14 17:00:00']);

        // Partidos J5 (En vivo y programados)
        Game::updateOrCreate(['matchday_id' => $m5->id, 'home_team_id' => $teamsLaliga['RMA']->id, 'away_team_id' => $teamsLaliga['FCB']->id], ['home_score' => 2, 'away_score' => 1, 'status' => 'en_vivo', 'match_date' => '2025-09-19 21:00:00']);
        Game::updateOrCreate(['matchday_id' => $m5->id, 'home_team_id' => $teamsLaliga['ATH']->id, 'away_team_id' => $teamsLaliga['ATM']->id], ['home_score' => 1, 'away_score' => 0, 'status' => 'en_vivo', 'match_date' => '2025-09-20 19:00:00']);
        Game::updateOrCreate(['matchday_id' => $m5->id, 'home_team_id' => $teamsLaliga['VIL']->id, 'away_team_id' => $teamsLaliga['RSO']->id], ['home_score' => null, 'away_score' => null, 'status' => 'programado', 'match_date' => '2025-09-20 21:30:00']);
        Game::updateOrCreate(['matchday_id' => $m5->id, 'home_team_id' => $teamsLaliga['BET']->id, 'away_team_id' => $teamsLaliga['SEV']->id], ['home_score' => null, 'away_score' => null, 'status' => 'programado', 'match_date' => '2025-09-21 17:00:00']);

        // Calcular tablas de posiciones para cada jornada
        $calculator->calculateForMatchday($m1, true);
        $calculator->calculateForMatchday($m2, true);
        $calculator->calculateForMatchday($m3, true);
        $calculator->calculateForMatchday($m4, true);
        $calculator->calculateForMatchday($m5, true);

        // 2. Torneo 2: Premier League
        $premier = Tournament::updateOrCreate(
            ['name' => 'Premier League', 'season' => '2025/2026'],
            ['start_date' => '2025-08-16', 'end_date' => '2026-05-25']
        );

        $teamsPremier = [
            'MCI' => Team::updateOrCreate(['name' => 'Manchester City'], ['short_name' => 'MCI']),
            'ARS' => Team::updateOrCreate(['name' => 'Arsenal FC'], ['short_name' => 'ARS']),
            'LIV' => Team::updateOrCreate(['name' => 'Liverpool FC'], ['short_name' => 'LIV']),
            'CHE' => Team::updateOrCreate(['name' => 'Chelsea FC'], ['short_name' => 'CHE']),
            'AVL' => Team::updateOrCreate(['name' => 'Aston Villa'], ['short_name' => 'AVL']),
            'TOT' => Team::updateOrCreate(['name' => 'Tottenham Hotspur'], ['short_name' => 'TOT']),
            'MUN' => Team::updateOrCreate(['name' => 'Manchester United'], ['short_name' => 'MUN']),
            'NEW' => Team::updateOrCreate(['name' => 'Newcastle United'], ['short_name' => 'NEW']),
        ];

        $pm1 = Matchday::updateOrCreate(['tournament_id' => $premier->id, 'number' => 1], ['start_date' => '2025-08-16', 'end_date' => '2025-08-18']);
        $pm2 = Matchday::updateOrCreate(['tournament_id' => $premier->id, 'number' => 2], ['start_date' => '2025-08-23', 'end_date' => '2025-08-25']);
        $pm3 = Matchday::updateOrCreate(['tournament_id' => $premier->id, 'number' => 3], ['start_date' => '2025-08-30', 'end_date' => '2025-09-01']);
        $pm4 = Matchday::updateOrCreate(['tournament_id' => $premier->id, 'number' => 4], ['start_date' => '2025-09-13', 'end_date' => '2025-09-15']);

        // Partidos Premier
        Game::updateOrCreate(['matchday_id' => $pm1->id, 'home_team_id' => $teamsPremier['MCI']->id, 'away_team_id' => $teamsPremier['CHE']->id], ['home_score' => 3, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-16 12:30:00']);
        Game::updateOrCreate(['matchday_id' => $pm1->id, 'home_team_id' => $teamsPremier['ARS']->id, 'away_team_id' => $teamsPremier['MUN']->id], ['home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-16 15:00:00']);
        Game::updateOrCreate(['matchday_id' => $pm1->id, 'home_team_id' => $teamsPremier['LIV']->id, 'away_team_id' => $teamsPremier['AVL']->id], ['home_score' => 3, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-17 16:30:00']);
        Game::updateOrCreate(['matchday_id' => $pm1->id, 'home_team_id' => $teamsPremier['TOT']->id, 'away_team_id' => $teamsPremier['NEW']->id], ['home_score' => 1, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-18 20:00:00']);

        Game::updateOrCreate(['matchday_id' => $pm2->id, 'home_team_id' => $teamsPremier['AVL']->id, 'away_team_id' => $teamsPremier['ARS']->id], ['home_score' => 0, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-23 12:30:00']);
        Game::updateOrCreate(['matchday_id' => $pm2->id, 'home_team_id' => $teamsPremier['CHE']->id, 'away_team_id' => $teamsPremier['TOT']->id], ['home_score' => 2, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-23 15:00:00']);
        Game::updateOrCreate(['matchday_id' => $pm2->id, 'home_team_id' => $teamsPremier['MUN']->id, 'away_team_id' => $teamsPremier['LIV']->id], ['home_score' => 0, 'away_score' => 3, 'status' => 'finalizado', 'match_date' => '2025-08-24 16:30:00']);
        Game::updateOrCreate(['matchday_id' => $pm2->id, 'home_team_id' => $teamsPremier['NEW']->id, 'away_team_id' => $teamsPremier['MCI']->id], ['home_score' => 1, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-25 20:00:00']);

        $calculator->calculateForMatchday($pm1, true);
        $calculator->calculateForMatchday($pm2, true);
    }
}
