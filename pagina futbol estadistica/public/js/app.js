/**
 * app.js — Controlador del Frontend del Dashboard de Estadísticas de Fútbol
 * 
 * Gestiona:
 * 1. El estado de la aplicación (torneo y jornada seleccionados).
 * 2. Las llamadas AJAX/Fetch a la API de Laravel (/api/tournaments, /api/matchdays, /api/standings, /api/matches).
 * 3. La renderización dinámica de la tabla de posiciones, lista de partidos y panel de proyecciones.
 */

document.addEventListener('DOMContentLoaded', () => {
    // =========================================================================
    // ESTADO GLOBAL DE LA APLICACIÓN
    // =========================================================================
    const AppState = {
        tournaments: [],          // Lista de torneos cargados
        matchdays: [],            // Jornadas del torneo activo
        selectedTournamentId: null, // ID del torneo seleccionado por el usuario
        selectedMatchdayId: null,   // ID de la jornada seleccionada
    };

    // =========================================================================
    // ELEMENTOS DEL DOM
    // =========================================================================
    const elements = {
        tournamentSelect: document.getElementById('tournament-select'),
        matchdaySelect: document.getElementById('matchday-select'),
        standingsTable: document.querySelector('#standings-table tbody'),
        matchesGrid: document.getElementById('matches-grid'),
        projectionList: document.getElementById('projection-list'),
        
        standingsCard: document.querySelector('.standings-card'),
        matchesCard: document.querySelector('.matches-card'),
        projectionCard: document.querySelector('.projection-card')
    };

    // Inicializar la aplicación al cargar el DOM
    init();

    /**
     * Función principal de inicio: Carga los torneos y suscribe los event listeners.
     */
    async function init() {
        await loadTournaments();
        
        // Listener al cambiar el selector de Torneo
        elements.tournamentSelect.addEventListener('change', async (e) => {
            AppState.selectedTournamentId = e.target.value;
            AppState.selectedMatchdayId = null;
            elements.matchdaySelect.innerHTML = '<option value="">Selecciona Jornada...</option>';
            
            clearData();
            
            if (AppState.selectedTournamentId) {
                await loadMatchdays(AppState.selectedTournamentId);
            }
        });

        // Listener al cambiar el selector de Jornada
        elements.matchdaySelect.addEventListener('change', async (e) => {
            AppState.selectedMatchdayId = e.target.value;
            if (AppState.selectedMatchdayId) {
                await loadMatchdayData(AppState.selectedMatchdayId);
            } else {
                clearData();
            }
        });
    }

    // =========================================================================
    // LLAMADAS A LA API Y CARGA DE DATOS
    // =========================================================================

    /**
     * Obtiene todos los torneos desde /api/tournaments y puebla el dropdown.
     */
    async function loadTournaments() {
        try {
            const res = await fetch('/api/tournaments');
            const data = await res.json();
            AppState.tournaments = data;
            
            elements.tournamentSelect.innerHTML = '<option value="">Seleccionar Torneo...</option>' + 
                data.map(t => `<option value="${t.id}">${t.name} (${t.season})</option>`).join('');
                
        } catch (error) {
            console.error('Error cargando torneos:', error);
            elements.tournamentSelect.innerHTML = '<option value="">Error cargando torneos</option>';
        }
    }

    /**
     * Obtiene las jornadas de un torneo desde /api/tournaments/{id}/matchdays y puebla el dropdown.
     */
    async function loadMatchdays(tournamentId) {
        try {
            elements.matchdaySelect.innerHTML = '<option value="">Cargando jornadas...</option>';
            const res = await fetch(`/api/tournaments/${tournamentId}/matchdays`);
            const data = await res.json();
            AppState.matchdays = data;
            
            elements.matchdaySelect.innerHTML = '<option value="">Seleccionar Jornada...</option>' + 
                data.map(m => {
                    const label = String(m.number).toLowerCase().startsWith('jornada') ? m.number : `Jornada ${m.number}`;
                    return `<option value="${m.id}">${label}</option>`;
                }).join('');
                
        } catch (error) {
            console.error('Error cargando jornadas:', error);
            elements.matchdaySelect.innerHTML = '<option value="">Error cargando jornadas</option>';
        }
    }

    /**
     * Obtiene en paralelo la tabla de posiciones (con proyecciones) y los partidos de la jornada seleccionada.
     */
    async function loadMatchdayData(matchdayId) {
        elements.standingsCard.classList.add('loading');
        elements.matchesCard.classList.add('loading');
        elements.projectionCard.classList.add('loading');

        try {
            const [standingsRes, matchesRes] = await Promise.all([
                fetch(`/api/matchdays/${matchdayId}/standings`),
                fetch(`/api/matchdays/${matchdayId}/matches`)
            ]);

            const standings = await standingsRes.json();
            const matches = await matchesRes.json();

            renderStandings(standings);
            renderProjections(standings);
            renderMatches(matches);

        } catch (error) {
            console.error('Error cargando datos de la jornada:', error);
        } finally {
            elements.standingsCard.classList.remove('loading');
            elements.matchesCard.classList.remove('loading');
            elements.projectionCard.classList.remove('loading');
        }
    }

    // =========================================================================
    // FUNCIONES DE RENDERIZADO VISUAL
    // =========================================================================

    /**
     * Renderiza las filas HTML de la tabla de posiciones con clases de colores según la posición.
     */
    function renderStandings(standings) {
        if (!standings.length) {
            elements.standingsTable.innerHTML = '<tr><td colspan="10">No hay datos disponibles.</td></tr>';
            return;
        }

        elements.standingsTable.innerHTML = standings.map((row, index) => {
            const pos = index + 1;
            let posClass = '';
            if (pos === 1) posClass = 'pos-1';
            else if (pos <= 4) posClass = 'pos-top';
            else if (pos >= standings.length - 2) posClass = 'pos-bottom';

            return `
                <tr class="fade-in" style="animation-delay: ${index * 0.05}s">
                    <td class="${posClass}">${pos}</td>
                    <td>${row.team.name}</td>
                    <td>${row.played}</td>
                    <td>${row.won}</td>
                    <td>${row.drawn}</td>
                    <td>${row.lost}</td>
                    <td>${row.goals_for}</td>
                    <td>${row.goals_against}</td>
                    <td>${row.goal_difference > 0 ? '+' + row.goal_difference : row.goal_difference}</td>
                    <td style="font-weight: 700;">${row.points}</td>
                </tr>
            `;
        }).join('');
    }

    /**
     * Renderiza la grilla de tarjetas de partidos con marcadores y estado.
     */
    function renderMatches(matches) {
        if (!matches.length) {
            elements.matchesGrid.innerHTML = '<p class="text-muted">No hay partidos para esta jornada.</p>';
            return;
        }

        elements.matchesGrid.innerHTML = matches.map((match, index) => {
            return `
                <div class="match-item fade-in" style="animation-delay: ${index * 0.05}s">
                    <div class="team team-home">
                        <span class="team-name">${match.home_team.name}</span>
                    </div>
                    <div class="score-box">
                        <span class="score">${match.home_score} - ${match.away_score}</span>
                        <span class="match-status status-${match.status}">${match.status.replace('_', ' ')}</span>
                    </div>
                    <div class="team team-away">
                        <span class="team-name">${match.away_team.name}</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    /**
     * Renderiza la lista de posiciones ordenadas por puntos proyectados al final del torneo.
     */
    function renderProjections(standings) {
        // Ordenar descendentemente por puntos proyectados
        const projections = [...standings].sort((a, b) => b.projected_points - a.projected_points);

        if (!projections.length) {
            elements.projectionList.innerHTML = '<p class="text-muted">No hay proyecciones disponibles.</p>';
            return;
        }

        elements.projectionList.innerHTML = projections.map((proj, index) => {
            return `
                <div class="projection-item fade-in" style="animation-delay: ${index * 0.05}s">
                    <span class="proj-team">${index + 1}. ${proj.team.name}</span>
                    <span class="proj-points">${proj.projected_points} pts</span>
                </div>
            `;
        }).join('');
    }

    /**
     * Limpia los contenedores visuales antes de renderizar nueva información.
     */
    function clearData() {
        elements.standingsTable.innerHTML = '';
        elements.matchesGrid.innerHTML = '';
        elements.projectionList.innerHTML = '';
    }
});

