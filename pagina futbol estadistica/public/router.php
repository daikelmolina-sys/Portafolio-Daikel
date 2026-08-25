<?php
/**
 * Router / Local Dev Server para StatPro — Dashboard de Estadísticas de Fútbol
 * Proporciona soporte completo para servir el frontend y los endpoints API /api/...
 * con datos reales de fútbol (LaLiga EA Sports y Premier League).
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// 1. Servir archivos estáticos existentes (CSS, JS, imágenes, etc.)
$filePath = __DIR__ . $uri;
if (strpos($uri, '/public/') === 0) {
    $filePath = __DIR__ . substr($uri, 7);
}

if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimes = [
        'css'  => 'text/css; charset=UTF-8',
        'js'   => 'application/javascript; charset=UTF-8',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'svg'  => 'image/svg+xml',
        'json' => 'application/json',
        'ico'  => 'image/x-icon'
    ];
    header('Content-Type: ' . ($mimes[$ext] ?? 'text/plain'));
    readfile($filePath);
    exit;
}

// 2. Base de Datos en Memoria con Equipos Reales
$DATA = [
    'tournaments' => [
        [
            'id' => 1,
            'name' => 'LaLiga EA Sports',
            'season' => '2025/2026',
            'start_date' => '2025-08-15',
            'end_date' => '2026-05-24',
            'total_matchdays' => 38
        ],
        [
            'id' => 2,
            'name' => 'Premier League',
            'season' => '2025/2026',
            'start_date' => '2025-08-16',
            'end_date' => '2026-05-25',
            'total_matchdays' => 38
        ]
    ],
    'teams' => [
        // LaLiga
        101 => ['id' => 101, 'tournament_id' => 1, 'name' => 'Real Madrid', 'short_name' => 'RMA'],
        102 => ['id' => 102, 'tournament_id' => 1, 'name' => 'FC Barcelona', 'short_name' => 'FCB'],
        103 => ['id' => 103, 'tournament_id' => 1, 'name' => 'Atlético de Madrid', 'short_name' => 'ATM'],
        104 => ['id' => 104, 'tournament_id' => 1, 'name' => 'Athletic Club', 'short_name' => 'ATH'],
        105 => ['id' => 105, 'tournament_id' => 1, 'name' => 'Villarreal CF', 'short_name' => 'VIL'],
        106 => ['id' => 106, 'tournament_id' => 1, 'name' => 'Real Betis', 'short_name' => 'BET'],
        107 => ['id' => 107, 'tournament_id' => 1, 'name' => 'Real Sociedad', 'short_name' => 'RSO'],
        108 => ['id' => 108, 'tournament_id' => 1, 'name' => 'Sevilla FC', 'short_name' => 'SEV'],

        // Premier League
        201 => ['id' => 201, 'tournament_id' => 2, 'name' => 'Manchester City', 'short_name' => 'MCI'],
        202 => ['id' => 202, 'tournament_id' => 2, 'name' => 'Arsenal FC', 'short_name' => 'ARS'],
        203 => ['id' => 203, 'tournament_id' => 2, 'name' => 'Liverpool FC', 'short_name' => 'LIV'],
        204 => ['id' => 204, 'tournament_id' => 2, 'name' => 'Chelsea FC', 'short_name' => 'CHE'],
        205 => ['id' => 205, 'tournament_id' => 2, 'name' => 'Aston Villa', 'short_name' => 'AVL'],
        206 => ['id' => 206, 'tournament_id' => 2, 'name' => 'Tottenham Hotspur', 'short_name' => 'TOT'],
        207 => ['id' => 207, 'tournament_id' => 2, 'name' => 'Manchester United', 'short_name' => 'MUN'],
        208 => ['id' => 208, 'tournament_id' => 2, 'name' => 'Newcastle United', 'short_name' => 'NEW'],
    ],
    'matchdays' => [
        // LaLiga
        ['id' => 1, 'tournament_id' => 1, 'number' => 1, 'start_date' => '2025-08-15', 'end_date' => '2025-08-17'],
        ['id' => 2, 'tournament_id' => 1, 'number' => 2, 'start_date' => '2025-08-22', 'end_date' => '2025-08-24'],
        ['id' => 3, 'tournament_id' => 1, 'number' => 3, 'start_date' => '2025-08-29', 'end_date' => '2025-08-31'],
        ['id' => 4, 'tournament_id' => 1, 'number' => 4, 'start_date' => '2025-09-12', 'end_date' => '2025-09-14'],
        ['id' => 5, 'tournament_id' => 1, 'number' => 5, 'start_date' => '2025-09-19', 'end_date' => '2025-09-21'],

        // Premier League
        ['id' => 6, 'tournament_id' => 2, 'number' => 1, 'start_date' => '2025-08-16', 'end_date' => '2025-08-18'],
        ['id' => 7, 'tournament_id' => 2, 'number' => 2, 'start_date' => '2025-08-23', 'end_date' => '2025-08-25'],
        ['id' => 8, 'tournament_id' => 2, 'number' => 3, 'start_date' => '2025-08-30', 'end_date' => '2025-09-01'],
        ['id' => 9, 'tournament_id' => 2, 'number' => 4, 'start_date' => '2025-09-13', 'end_date' => '2025-09-15'],
    ],
    'matches' => [
        // ==================== LALIGA ====================
        // Jornada 1
        ['id' => 1, 'matchday_id' => 1, 'home_team_id' => 101, 'away_team_id' => 108, 'home_score' => 3, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-15 21:00:00'],
        ['id' => 2, 'matchday_id' => 1, 'home_team_id' => 102, 'away_team_id' => 106, 'home_score' => 2, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-16 19:00:00'],
        ['id' => 3, 'matchday_id' => 1, 'home_team_id' => 103, 'away_team_id' => 105, 'home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-16 21:30:00'],
        ['id' => 4, 'matchday_id' => 1, 'home_team_id' => 104, 'away_team_id' => 107, 'home_score' => 1, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-17 17:00:00'],

        // Jornada 2
        ['id' => 5, 'matchday_id' => 2, 'home_team_id' => 106, 'away_team_id' => 101, 'home_score' => 1, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-22 21:00:00'],
        ['id' => 6, 'matchday_id' => 2, 'home_team_id' => 108, 'away_team_id' => 102, 'home_score' => 1, 'away_score' => 4, 'status' => 'finalizado', 'match_date' => '2025-08-23 19:00:00'],
        ['id' => 7, 'matchday_id' => 2, 'home_team_id' => 105, 'away_team_id' => 104, 'home_score' => 2, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-23 21:30:00'],
        ['id' => 8, 'matchday_id' => 2, 'home_team_id' => 107, 'away_team_id' => 103, 'home_score' => 0, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-24 17:00:00'],

        // Jornada 3
        ['id' => 9, 'matchday_id' => 3, 'home_team_id' => 102, 'away_team_id' => 103, 'home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-29 21:00:00'],
        ['id' => 10, 'matchday_id' => 3, 'home_team_id' => 101, 'away_team_id' => 107, 'home_score' => 4, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-30 19:00:00'],
        ['id' => 11, 'matchday_id' => 3, 'home_team_id' => 104, 'away_team_id' => 108, 'home_score' => 2, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-30 21:30:00'],
        ['id' => 12, 'matchday_id' => 3, 'home_team_id' => 105, 'away_team_id' => 106, 'home_score' => 3, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-31 17:00:00'],

        // Jornada 4
        ['id' => 13, 'matchday_id' => 4, 'home_team_id' => 103, 'away_team_id' => 101, 'home_score' => 1, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-09-12 21:00:00'],
        ['id' => 14, 'matchday_id' => 4, 'home_team_id' => 107, 'away_team_id' => 102, 'home_score' => 1, 'away_score' => 3, 'status' => 'finalizado', 'match_date' => '2025-09-13 19:00:00'],
        ['id' => 15, 'matchday_id' => 4, 'home_team_id' => 106, 'away_team_id' => 104, 'home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-09-13 21:30:00'],
        ['id' => 16, 'matchday_id' => 4, 'home_team_id' => 108, 'away_team_id' => 105, 'home_score' => 0, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-09-14 17:00:00'],

        // Jornada 5 (En vivo y programados)
        ['id' => 17, 'matchday_id' => 5, 'home_team_id' => 101, 'away_team_id' => 102, 'home_score' => 2, 'away_score' => 1, 'status' => 'en_vivo', 'match_date' => '2025-09-19 21:00:00'],
        ['id' => 18, 'matchday_id' => 5, 'home_team_id' => 104, 'away_team_id' => 103, 'home_score' => 1, 'away_score' => 0, 'status' => 'en_vivo', 'match_date' => '2025-09-20 19:00:00'],
        ['id' => 19, 'matchday_id' => 5, 'home_team_id' => 105, 'away_team_id' => 107, 'home_score' => 0, 'away_score' => 0, 'status' => 'programado', 'match_date' => '2025-09-20 21:30:00'],
        ['id' => 20, 'matchday_id' => 5, 'home_team_id' => 106, 'away_team_id' => 108, 'home_score' => 0, 'away_score' => 0, 'status' => 'programado', 'match_date' => '2025-09-21 17:00:00'],

        // ==================== PREMIER LEAGUE ====================
        // Jornada 1
        ['id' => 21, 'matchday_id' => 6, 'home_team_id' => 201, 'away_team_id' => 204, 'home_score' => 3, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-16 12:30:00'],
        ['id' => 22, 'matchday_id' => 6, 'home_team_id' => 202, 'away_team_id' => 207, 'home_score' => 2, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-16 15:00:00'],
        ['id' => 23, 'matchday_id' => 6, 'home_team_id' => 203, 'away_team_id' => 205, 'home_score' => 3, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-17 16:30:00'],
        ['id' => 24, 'matchday_id' => 6, 'home_team_id' => 206, 'away_team_id' => 208, 'home_score' => 1, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-18 20:00:00'],

        // Jornada 2
        ['id' => 25, 'matchday_id' => 7, 'home_team_id' => 205, 'away_team_id' => 202, 'home_score' => 0, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-23 12:30:00'],
        ['id' => 26, 'matchday_id' => 7, 'home_team_id' => 204, 'away_team_id' => 206, 'home_score' => 2, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-23 15:00:00'],
        ['id' => 27, 'matchday_id' => 7, 'home_team_id' => 207, 'away_team_id' => 203, 'home_score' => 0, 'away_score' => 3, 'status' => 'finalizado', 'match_date' => '2025-08-24 16:30:00'],
        ['id' => 28, 'matchday_id' => 7, 'home_team_id' => 208, 'away_team_id' => 201, 'home_score' => 1, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-08-25 20:00:00'],

        // Jornada 3
        ['id' => 29, 'matchday_id' => 8, 'home_team_id' => 201, 'away_team_id' => 203, 'home_score' => 1, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-30 12:30:00'],
        ['id' => 30, 'matchday_id' => 8, 'home_team_id' => 202, 'away_team_id' => 206, 'home_score' => 3, 'away_score' => 1, 'status' => 'finalizado', 'match_date' => '2025-08-30 15:00:00'],
        ['id' => 31, 'matchday_id' => 8, 'home_team_id' => 204, 'away_team_id' => 205, 'home_score' => 2, 'away_score' => 0, 'status' => 'finalizado', 'match_date' => '2025-08-31 16:30:00'],
        ['id' => 32, 'matchday_id' => 8, 'home_team_id' => 207, 'away_team_id' => 208, 'home_score' => 1, 'away_score' => 2, 'status' => 'finalizado', 'match_date' => '2025-09-01 20:00:00'],

        // Jornada 4 (En vivo y programados)
        ['id' => 33, 'matchday_id' => 9, 'home_team_id' => 203, 'away_team_id' => 202, 'home_score' => 2, 'away_score' => 2, 'status' => 'en_vivo', 'match_date' => '2025-09-13 12:30:00'],
        ['id' => 34, 'matchday_id' => 9, 'home_team_id' => 206, 'away_team_id' => 201, 'home_score' => 0, 'away_score' => 1, 'status' => 'en_vivo', 'match_date' => '2025-09-13 15:00:00'],
        ['id' => 35, 'matchday_id' => 9, 'home_team_id' => 205, 'away_team_id' => 207, 'home_score' => 0, 'away_score' => 0, 'status' => 'programado', 'match_date' => '2025-09-14 16:30:00'],
        ['id' => 36, 'matchday_id' => 9, 'home_team_id' => 208, 'away_team_id' => 204, 'home_score' => 0, 'away_score' => 0, 'status' => 'programado', 'match_date' => '2025-09-15 20:00:00'],
    ]
];

// Helper para calcular Standings con Aislamiento Temporal y Proyecciones
function calculateStandingsForMatchday($matchdayId, $DATA) {
    // 1. Encontrar la jornada
    $targetMatchday = null;
    foreach ($DATA['matchdays'] as $m) {
        if ($m['id'] == $matchdayId) {
            $targetMatchday = $m;
            break;
        }
    }
    if (!$targetMatchday) return [];

    $tournamentId = $targetMatchday['tournament_id'];

    // 2. Obtener jornadas válidas hasta la fecha actual
    $validMatchdayIds = [];
    foreach ($DATA['matchdays'] as $m) {
        if ($m['tournament_id'] == $tournamentId && $m['end_date'] <= $targetMatchday['end_date']) {
            $validMatchdayIds[] = $m['id'];
        }
    }

    // 3. Obtener equipos del torneo
    $teamIds = [];
    foreach ($DATA['teams'] as $tid => $team) {
        if ($team['tournament_id'] == $tournamentId) {
            $teamIds[] = $tid;
        }
    }

    $table = [];
    foreach ($teamIds as $tid) {
        $table[$tid] = [
            'team_id' => $tid,
            'team' => $DATA['teams'][$tid],
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
            'projected_points' => 0
        ];
    }

    // 4. Acumular estadísticas de partidos finalizados y en vivo con marcador
    foreach ($DATA['matches'] as $match) {
        if (in_array($match['matchday_id'], $validMatchdayIds)) {
            // Solo procesar si el partido ha finalizado o está en vivo con goles registrados
            $isCompleted = ($match['status'] === 'finalizado');
            $isLiveWithScore = ($match['status'] === 'en_vivo' && $match['home_score'] !== null && $match['away_score'] !== null);

            if ($isCompleted || $isLiveWithScore) {
                $h = $match['home_team_id'];
                $a = $match['away_team_id'];

                if (isset($table[$h]) && isset($table[$a])) {
                    // Partidos Jugados
                    $table[$h]['played'] += 1;
                    $table[$a]['played'] += 1;

                    // Goles a favor y en contra
                    $table[$h]['goals_for'] += $match['home_score'];
                    $table[$h]['goals_against'] += $match['away_score'];
                    $table[$a]['goals_for'] += $match['away_score'];
                    $table[$a]['goals_against'] += $match['home_score'];

                    // Diferencia de Goles (DG = GF - GC)
                    $table[$h]['goal_difference'] = $table[$h]['goals_for'] - $table[$h]['goals_against'];
                    $table[$a]['goal_difference'] = $table[$a]['goals_for'] - $table[$a]['goals_against'];

                    // REGLA OFICIAL DE PUNTUACIÓN:
                    // - Victoria: 3 puntos (PG + 1, Pts + 3)
                    // - Empate: 1 punto para cada equipo (PE + 1, Pts + 1)
                    // - Derrota: 0 puntos (PP + 1, Pts + 0)
                    if ($match['home_score'] > $match['away_score']) {
                        $table[$h]['won'] += 1;
                        $table[$a]['lost'] += 1;
                    } elseif ($match['home_score'] < $match['away_score']) {
                        $table[$a]['won'] += 1;
                        $table[$h]['lost'] += 1;
                    } else {
                        $table[$h]['drawn'] += 1;
                        $table[$a]['drawn'] += 1;
                    }

                    // Cálculo matemático exacto de puntos: (PG * 3) + (PE * 1)
                    $table[$h]['points'] = ($table[$h]['won'] * 3) + ($table[$h]['drawn'] * 1);
                    $table[$a]['points'] = ($table[$a]['won'] * 3) + ($table[$a]['drawn'] * 1);
                }
            }
        }
    }

    // 5. Calcular proyecciones de puntos finales
    $totalTournamentMatchdays = 38;
    foreach ($table as $tid => &$row) {
        // Promedio de puntos obtenidos por partido
        $avg = $row['played'] > 0 ? ($row['points'] / $row['played']) : 1.0;
        $gamesLeft = $totalTournamentMatchdays - $row['played'];
        $row['projected_points'] = round($row['points'] + ($gamesLeft * $avg), 1);
    }
    unset($row);

    // 6. Ordenar tabla de posiciones:
    // Criterio 1: Puntos (Pts)
    // Criterio 2: Diferencia de goles (DG)
    // Criterio 3: Goles a favor (GF)
    uasort($table, function ($a, $b) {
        if ($a['points'] !== $b['points']) return $b['points'] <=> $a['points'];
        if ($a['goal_difference'] !== $b['goal_difference']) return $b['goal_difference'] <=> $a['goal_difference'];
        return $b['goals_for'] <=> $a['goals_for'];
    });

    return array_values($table);
}

// =========================================================================
// ROUTING DE ENDPOINTS
// =========================================================================

// Encabezados JSON para la API
if (strpos($uri, '/api/') === 0) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    // [GET] /api/tournaments
    if ($uri === '/api/tournaments') {
        echo json_encode($DATA['tournaments']);
        exit;
    }

    // [GET] /api/tournaments/{id}/matchdays
    if (preg_match('#^/api/tournaments/(\d+)/matchdays$#', $uri, $matches)) {
        $tournamentId = (int)$matches[1];
        $result = array_values(array_filter($DATA['matchdays'], function ($m) use ($tournamentId) {
            return $m['tournament_id'] === $tournamentId;
        }));
        echo json_encode($result);
        exit;
    }

    // [GET] /api/matchdays/{id}/standings
    if (preg_match('#^/api/matchdays/(\d+)/standings$#', $uri, $matches)) {
        $matchdayId = (int)$matches[1];
        $standings = calculateStandingsForMatchday($matchdayId, $DATA);
        echo json_encode($standings);
        exit;
    }

    // [GET] /api/matchdays/{id}/matches
    if (preg_match('#^/api/matchdays/(\d+)/matches$#', $uri, $matches)) {
        $matchdayId = (int)$matches[1];
        $matchesList = [];
        foreach ($DATA['matches'] as $match) {
            if ($match['matchday_id'] === $matchdayId) {
                $item = $match;
                $item['home_team'] = $DATA['teams'][$match['home_team_id']] ?? ['name' => 'Equipo ' . $match['home_team_id']];
                $item['away_team'] = $DATA['teams'][$match['away_team_id']] ?? ['name' => 'Equipo ' . $match['away_team_id']];
                $matchesList[] = $item;
            }
        }
        echo json_encode($matchesList);
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Endpoint no encontrado']);
    exit;
}

// 3. Servir la vista principal HTML
$viewFile = __DIR__ . '/../resources/views/welcome.blade.php';
if (file_exists($viewFile)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($viewFile);
    exit;
}

http_response_code(404);
echo "404 Not Found";
