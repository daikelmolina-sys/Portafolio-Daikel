<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Team (Club / Equipo de Fútbol)
 * Representa un club participante en torneos (ej. 'Barcelona', 'Real Madrid').
 * 
 * Atributos:
 * - name: Nombre completo del equipo.
 * - short_name: Abreviatura o nombre corto (ej. 'BAR', 'RMA').
 * - logo_url: URL o ruta de la imagen/escudo del club.
 */
class Team extends Model
{
    // Campos autorizados para asignación masiva
    protected $fillable = ['name', 'short_name', 'logo_url'];

    /**
     * Relación 1 a N: Todos los partidos donde este equipo jugó como Local.
     */
    public function homeGames()
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    /**
     * Relación 1 a N: Todos los partidos donde este equipo jugó como Visitante.
     */
    public function awayGames()
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }

    /**
     * Relación 1 a N: Registros de posición en tablas de clasificación a lo largo de las fechas.
     */
    public function standings()
    {
        return $this->hasMany(Standing::class);
    }
}

