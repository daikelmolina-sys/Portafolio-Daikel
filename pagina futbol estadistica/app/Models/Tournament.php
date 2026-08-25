<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Tournament (Torneo de Fútbol)
 * Representa una competición o liga (ej. "La Liga 2026", "Premier League").
 * 
 * Atributos:
 * - name: Nombre del torneo.
 * - season: Temporada (ej. '2026').
 * - start_date: Fecha de inicio de la competición.
 * - end_date: Fecha de finalización programada.
 */
class Tournament extends Model
{
    // Campos que se pueden asignar masivamente
    protected $fillable = ['name', 'season', 'start_date', 'end_date'];

    // Casteo automático de fechas a objetos Carbon / Date
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relación 1 a N: Un torneo tiene múltiples jornadas (Matchdays).
     */
    public function matchdays()
    {
        return $this->hasMany(Matchday::class);
    }

    /**
     * Relación 1 a N: Un torneo tiene múltiples registros de tablas de posiciones.
     */
    public function standings()
    {
        return $this->hasMany(Standing::class);
    }
}

