<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Game (Partido de Fútbol)
 * Representa un encuentro entre dos equipos en una jornada dada.
 * Nota: En la base de datos la tabla subyacente se llama 'matches'.
 * 
 * Atributos:
 * - matchday_id: ID foráneo de la jornada a la que pertenece.
 * - home_team_id: ID foráneo del equipo local.
 * - away_team_id: ID foráneo del equipo visitante.
 * - home_score: Goles marcados por el local (null si no se ha jugado).
 * - away_score: Goles marcados por el visitante (null si no se ha jugado).
 * - status: Estado del partido ('programado', 'en_juego', 'finalizado', etc.).
 * - match_date: Fecha y hora exacta de realización del partido.
 */
class Game extends Model
{
    // Nombre explícito de la tabla en la base de datos
    protected $table = 'matches';

    // Campos autorizados para asignación masiva
    protected $fillable = [
        'matchday_id', 'home_team_id', 'away_team_id',
        'home_score', 'away_score', 'status', 'match_date'
    ];

    // Casteo de fecha y hora
    protected $casts = [
        'match_date' => 'datetime',
    ];

    /**
     * Relación N a 1: Un partido pertenece a una jornada específica.
     */
    public function matchday()
    {
        return $this->belongsTo(Matchday::class);
    }

    /**
     * Relación N a 1: El equipo que juega como local.
     */
    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Relación N a 1: El equipo que juega como visitante.
     */
    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}

