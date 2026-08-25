<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Standing (Registro de Tabla de Posiciones)
 * Almacena la foto fija (snapshot) del rendimiento acumulado de un equipo
 * hasta una jornada determinada, garantizando aislamiento temporal.
 * 
 * Atributos:
 * - tournament_id: ID del torneo.
 * - matchday_id: ID de la jornada en la que se congeló la tabla.
 * - team_id: ID del equipo.
 * - played: Partidos Jugados (PJ).
 * - won: Partidos Ganados (PG).
 * - drawn: Partidos Empatados (PE).
 * - lost: Partidos Perdidos (PP).
 * - goals_for: Goles a Favor (GF).
 * - goals_against: Goles en Contra (GC).
 * - goal_difference: Diferencia de Gol (DG = GF - GC).
 * - points: Puntos Totales (3 por PG, 1 por PE).
 */
class Standing extends Model
{
    // Campos asignables masivamente
    protected $fillable = [
        'tournament_id', 'matchday_id', 'team_id',
        'played', 'won', 'drawn', 'lost',
        'goals_for', 'goals_against', 'goal_difference', 'points'
    ];

    // Formateo de números decimales
    protected $casts = [
        'goal_difference' => 'decimal:2',
        'points' => 'decimal:2',
    ];

    /**
     * Relación N a 1: Pertenece a un Torneo.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Relación N a 1: Corresponde al estado al cierre de una Jornada concreta.
     */
    public function matchday()
    {
        return $this->belongsTo(Matchday::class);
    }

    /**
     * Relación N a 1: Equipo al que corresponden las estadísticas.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}

