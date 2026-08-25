<?php

namespace App\Services;

use App\Models\Matchday;
use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;

/**
 * Servicio StandingsCalculatorService
 * 
 * Contiene la lógica de negocio para:
 * 1. Calcular la tabla de posiciones con Aislamiento Temporal (congelando resultados a la fecha de cada jornada).
 * 2. Proyectar los puntos finales esperados para cada equipo basado en su rendimiento reciente (últimos N partidos).
 */
class StandingsCalculatorService
{
    /**
     * Calcula y almacena en la BD la fotografía (snapshot) de la tabla de posiciones para una jornada.
     * Aplica la regla de negocio de Aislamiento Temporal (no considera partidos futuros).
     * 
     * @param Matchday $currentMatchday Jornada objetivo
     * @param bool $forceRecalculate Si es true, recalcula incluso si ya existe snapshot previo
     */
    public function calculateForMatchday(Matchday $currentMatchday, bool $forceRecalculate = false)
    {
        // 0. Regla de Snapshot Congelado: Si ya existe un cálculo previo y no se fuerza, no alterar.
        if (!$forceRecalculate && Standing::where('matchday_id', $currentMatchday->id)->exists()) {
            return;
        }

        // 1. Obtener todas las jornadas del torneo ocurridas hasta la fecha de la jornada actual
        $validMatchdayIds = Matchday::where('tournament_id', $currentMatchday->tournament_id)
            ->where('end_date', '<=', $currentMatchday->end_date)
            ->pluck('id');

        // 2. Obtener partidos finalizados antes o durante el cierre de la jornada actual
        $matches = Game::whereIn('matchday_id', $validMatchdayIds)
            ->where('status', 'finalizado')
            ->where('match_date', '<=', $currentMatchday->end_date . ' 23:59:59')
            ->get();

        $standings = [];

        // 3. Identificar todos los equipos participantes en el torneo
        $allTeamsInTournament = Game::whereIn('matchday_id', Matchday::where('tournament_id', $currentMatchday->tournament_id)->pluck('id'))
            ->get()
            ->flatMap(function ($game) {
                return [$game->home_team_id, $game->away_team_id];
            })->unique();

        // 4. Inicializar estadísticas en cero para cada equipo
        foreach ($allTeamsInTournament as $teamId) {
            $standings[$teamId] = [
                'tournament_id' => $currentMatchday->tournament_id,
                'matchday_id' => $currentMatchday->id,
                'team_id' => $teamId,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ];
        }

        // 5. Procesar resultados de cada partido y acumular puntos / goles
        foreach ($matches as $match) {
            $home = $match->home_team_id;
            $away = $match->away_team_id;

            // Actualizar estadísticas del equipo Local
            $standings[$home]['played'] += 1;
            $standings[$home]['goals_for'] += $match->home_score;
            $standings[$home]['goals_against'] += $match->away_score;
            $standings[$home]['goal_difference'] += ($match->home_score - $match->away_score);

            // Actualizar estadísticas del equipo Visitante
            $standings[$away]['played'] += 1;
            $standings[$away]['goals_for'] += $match->away_score;
            $standings[$away]['goals_against'] += $match->home_score;
            $standings[$away]['goal_difference'] += ($match->away_score - $match->home_score);

            // Asignación de estadísticas según resultado del partido:
            // Victoria: 3 Pts (PG + 1) | Empate: 1 Pt cada uno (PE + 1) | Derrota: 0 Pts (PP + 1)
            if ($match->home_score > $match->away_score) {
                $standings[$home]['won'] += 1;
                $standings[$away]['lost'] += 1;
            } elseif ($match->home_score < $match->away_score) {
                $standings[$away]['won'] += 1;
                $standings[$home]['lost'] += 1;
            } else {
                $standings[$home]['drawn'] += 1;
                $standings[$away]['drawn'] += 1;
            }

            // Cálculo matemático estricto: Pts = (PG * 3) + (PE * 1)
            $standings[$home]['points'] = ($standings[$home]['won'] * 3) + ($standings[$home]['drawn'] * 1);
            $standings[$away]['points'] = ($standings[$away]['won'] * 3) + ($standings[$away]['drawn'] * 1);
        }

        // 6. Guardar o actualizar los registros de posición en la base de datos
        foreach ($standings as $teamId => $data) {
            Standing::updateOrCreate(
                [
                    'tournament_id' => $data['tournament_id'],
                    'matchday_id' => $data['matchday_id'],
                    'team_id' => $data['team_id']
                ],
                $data
            );
        }
    }

    /**
     * Calcula los puntos finales proyectados para un equipo al terminar la temporada.
     * Fórmula: Puntos_Actuales + (Partidos_Restantes * Promedio_Puntos_Últimos_N_Partidos)
     * 
     * @param Matchday $targetMatchday Jornada de referencia
     * @param int $teamId ID del equipo
     * @param int $totalMatchdaysInTournament Total de fechas del torneo
     * @param int $nMatchdays Ventana de partidos recientes (por defecto 5)
     * @return float Puntos proyectados estimados
     */
    public function calculateProjection(Matchday $targetMatchday, int $teamId, int $totalMatchdaysInTournament, int $nMatchdays = 5)
    {
        // 1. Obtener los IDs de las últimas N jornadas hasta la jornada de referencia
        $recentMatchdays = Matchday::where('tournament_id', $targetMatchday->tournament_id)
            ->where('end_date', '<=', $targetMatchday->end_date)
            ->orderBy('end_date', 'desc')
            ->take($nMatchdays)
            ->pluck('id');

        if ($recentMatchdays->isEmpty()) {
            return 0;
        }

        // 2. Consultar los partidos finalizados del equipo en esa ventana temporal
        $matches = Game::whereIn('matchday_id', $recentMatchdays)
            ->where(function ($q) use ($teamId) {
                $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId);
            })
            ->where('status', 'finalizado')
            ->where('match_date', '<=', $targetMatchday->end_date . ' 23:59:59')
            ->get();

        $pointsEarned = 0;
        $gamesPlayed = 0;

        // 3. Sumar puntos obtenidos en los partidos evaluados
        foreach ($matches as $match) {
            $gamesPlayed++;
            if ($match->home_team_id == $teamId) {
                if ($match->home_score > $match->away_score) $pointsEarned += 3;
                elseif ($match->home_score == $match->away_score) $pointsEarned += 1;
            } else {
                if ($match->away_score > $match->home_score) $pointsEarned += 3;
                elseif ($match->home_score == $match->away_score) $pointsEarned += 1;
            }
        }

        // Si no ha jugado partidos en la ventana, no hay datos para proyectar
        if ($gamesPlayed === 0) {
            return 0;
        }

        // 4. Calcular promedio de puntos por partido
        $averagePointsPerGame = $pointsEarned / $gamesPlayed;
        
        // 5. Consultar los puntos y partidos jugados actuales
        $currentStanding = Standing::where('matchday_id', $targetMatchday->id)
            ->where('team_id', $teamId)
            ->first();

        $playedSoFar = $currentStanding ? $currentStanding->played : 0;
        $currentPoints = $currentStanding ? $currentStanding->points : 0;
        
        // 6. Calcular partidos restantes y proyección final
        $gamesLeft = $totalMatchdaysInTournament - $playedSoFar;
        $projectedAdditionalPoints = $gamesLeft * $averagePointsPerGame;
        
        return round($currentPoints + $projectedAdditionalPoints, 2);
    }
}

