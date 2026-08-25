<?php

use Illuminate\Support\Facades\Route;
use App\Models\Tournament;
use App\Models\Matchday;
use App\Models\Standing;
use App\Models\Game;
use App\Services\StandingsCalculatorService;

/*
|--------------------------------------------------------------------------
| Rutas Web y Endpoints API — Dashboard de Estadísticas de Fútbol
|--------------------------------------------------------------------------
| Este archivo define la ruta principal que sirve la vista del Dashboard (SPA)
| y el grupo de rutas /api encargadas de entregar los datos de torneos,
| jornadas, partidos, tablas de posiciones y proyecciones matemáticas.
*/

// =========================================================================
// 1. RUTA PRINCIPAL (FRONTEND / SPA)
// =========================================================================
// Retorna la vista Blade 'welcome' que contiene la interfaz visual del dashboard.
Route::get('/', function () {
    return view('welcome');
});

// =========================================================================
// 2. GRUPO DE RUTAS DE LA API (/api/...)
// =========================================================================
Route::prefix('api')->group(function () {
    
    /**
     * [GET] /api/external-mock
     * Simula la respuesta de una API externa de proveedores deportivos (usada para pruebas/Guzzle).
     * Devuelve una estructura JSON con torneos, equipos, jornadas y partidos precargados.
     */
    Route::get('/external-mock', function () {
        return response()->json([
            'tournaments' => [
                [
                    'external_id' => 1,
                    'name' => 'Liga de Espana',
                    'season' => '2026',
                    'start_date' => '2026-08-01',
                    'end_date' => '2026-12-15'
                ]
            ],
            'teams' => [
                ['external_id' => 101, 'name' => 'Barcelona', 'short_name' => 'BAR'],
                ['external_id' => 102, 'name' => 'Real Madrid', 'short_name' => 'RMA'],
                ['external_id' => 103, 'name' => 'Atl.Madrid', 'short_name' => 'ATM'],
                ['external_id' => 104, 'name' => 'Sevilla', 'short_name' => 'SEV'],
            ],
            'matchdays' => [
                ['external_id' => 10, 'tournament_external_id' => 1, 'number' => 'Jornada 1', 'start_date' => '2026-08-01', 'end_date' => '2026-08-03'],
                ['external_id' => 11, 'tournament_external_id' => 1, 'number' => 'Jornada 2', 'start_date' => '2026-08-08', 'end_date' => '2026-08-10'],
                ['external_id' => 12, 'tournament_external_id' => 1, 'number' => 'Jornada 3', 'start_date' => '2026-08-15', 'end_date' => '2026-08-17'],
                ['external_id' => 13, 'tournament_external_id' => 1, 'number' => 'Jornada 4', 'start_date' => '2026-08-22', 'end_date' => '2026-08-24'],
                ['external_id' => 14, 'tournament_external_id' => 1, 'number' => 'Jornada 5', 'start_date' => '2026-08-29', 'end_date' => '2026-08-31'],
            ],
            'matches' => [
                ['external_matchday_id' => 10, 'home_team_id' => 101, 'away_team_id' => 102, 'home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2026-08-01 15:00:00'],
                ['external_matchday_id' => 10, 'home_team_id' => 103, 'away_team_id' => 104, 'home_score' => 0, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2026-08-02 17:00:00'],
                ['external_matchday_id' => 11, 'home_team_id' => 104, 'away_team_id' => 101, 'home_score' => 1, 'away_score' => 3, 'status' => 'finalizado', 'match_date' => '2026-08-08 15:00:00'],
                ['external_matchday_id' => 11, 'home_team_id' => 102, 'away_team_id' => 103, 'home_score' => 2, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2026-08-09 17:00:00'],
                ['external_matchday_id' => 12, 'home_team_id' => 101, 'away_team_id' => 103, 'home_score' => null, 'away_score' => null, 'status' => 'programado', 'match_date' => '2026-08-16 15:00:00'],
                ['external_matchday_id' => 12, 'home_team_id' => 102, 'away_team_id' => 104, 'home_score' => null, 'away_score' => null, 'status' => 'programado', 'match_date' => '2026-08-17 17:00:00'],
                ['external_matchday_id' => 13, 'home_team_id' => 101, 'away_team_id' => 104, 'home_score' => 5, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2026-08-05 20:00:00'],
                ['external_matchday_id' => 13, 'home_team_id' => 103, 'away_team_id' => 102, 'home_score' => null, 'away_score' => null, 'status' => 'programado', 'match_date' => '2026-08-23 15:00:00'],
            ]
        ]);
    });

    /**
     * [GET] /api/tournaments
     * Obtiene la lista completa de torneos disponibles en la base de datos.
     */
    Route::get('/tournaments', function () {
        return Tournament::all();
    });

    /**
     * [GET] /api/tournaments/{id}/matchdays
     * Obtiene todas las jornadas pertenecientes a un torneo específico, ordenadas cronológicamente.
     * @param int $id ID del torneo
     */
    Route::get('/tournaments/{id}/matchdays', function ($id) {
        return Matchday::where('tournament_id', $id)->orderBy('start_date')->get();
    });

    /**
     * [GET] /api/matchdays/{id}/standings
     * Obtiene la tabla de posiciones calculada para una jornada específica,
     * incluyendo el cálculo de puntos proyectados al final del torneo.
     * @param int $id ID de la jornada
     * @param StandingsCalculatorService $calculator Servicio inyectado para cálculo de proyecciones
     */
    Route::get('/matchdays/{id}/standings', function ($id, StandingsCalculatorService $calculator) {
        // 1. Obtener la jornada solicitada
        $matchday = Matchday::findOrFail($id);
        
        // 2. Obtener la tabla de posiciones con sus equipos relacionados, ordenada por puntos y diferencia de gol
        $standings = Standing::with('team')
            ->where('matchday_id', $id)
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->get();
            
        // 3. Obtener el total de jornadas para proyectar el final de la temporada
        $tournament = Tournament::findOrFail($matchday->tournament_id);
        $totalMatchdays = $tournament->matchdays()->count();
        
        // 4. Mapear cada equipo y calcular su proyección en base al promedio de los últimos 5 partidos
        $standingsWithProjections = $standings->map(function ($standing) use ($calculator, $matchday, $totalMatchdays) {
            $standing->projected_points = $calculator->calculateProjection($matchday, $standing->team_id, $totalMatchdays, 5);
            return $standing;
        });

        return $standingsWithProjections;
    });

    /**
     * [GET] /api/matchdays/{id}/matches
     * Obtiene la lista de partidos de una jornada con sus respectivos equipos local y visitante.
     * @param int $id ID de la jornada
     */
    Route::get('/matchdays/{id}/matches', function ($id) {
        return Game::with(['homeTeam', 'awayTeam'])
            ->where('matchday_id', $id)
            ->orderBy('match_date')
            ->get();
    });
});

