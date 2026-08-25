<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Matchday (Jornada o Fecha de un Torneo)
 * Representa una fecha específica dentro de un torneo (ej. "Jornada 1", "Jornada 2").
 * 
 * Atributos:
 * - tournament_id: ID foráneo del torneo al que pertenece.
 * - number: Número o identificador de la jornada (ej. "Jornada 1").
 * - start_date: Fecha de inicio de la jornada.
 * - end_date: Fecha límite de cierre de la jornada (utilizado para el cálculo temporal aislado).
 */
class Matchday extends Model
{
    // Campos permitidos para asignación masiva
    protected $fillable = ['tournament_id', 'number', 'start_date', 'end_date'];

    // Conversión de fechas a tipo date de Carbon
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relación N a 1: Una jornada pertenece a un único Torneo.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Relación 1 a N: Una jornada contiene múltiples partidos (Games).
     */
    public function games()
    {
        return $this->hasMany(Game::class);
    }

    /**
     * Relación 1 a N: Una jornada tiene registros de tabla de posiciones congelados a esa fecha.
     */
    public function standings()
    {
        return $this->hasMany(Standing::class);
    }
}

