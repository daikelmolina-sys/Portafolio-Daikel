<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Stats Dashboard — StatPro</title>
    
    <!-- Fuentes de Google: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="app-container">
        
        <!-- ================================================================= -->
        <!-- ENCABEZADO Y SELECTORES DE FILTRO (Torneo y Jornada)              -->
        <!-- ================================================================= -->
        <header class="glass-header">
            <div class="logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 2C12 2 15 6 15 12C15 18 12 22 12 22" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 2C12 2 9 6 9 12C9 18 12 22 12 22" stroke="currentColor" stroke-width="2"/>
                    <path d="M2 12H22" stroke="currentColor" stroke-width="2"/>
                </svg>
                <h1>StatPro</h1>
            </div>
            
            <div class="controls">
                <!-- Dropdown selector de Torneo (llenado dinámicamente con JS) -->
                <div class="select-wrapper">
                    <select id="tournament-select" aria-label="Seleccionar Torneo">
                        <option value="">Cargando torneos...</option>
                    </select>
                </div>
                
                <!-- Dropdown selector de Jornada (depende del torneo seleccionado) -->
                <div class="select-wrapper">
                    <select id="matchday-select" aria-label="Seleccionar Jornada">
                        <option value="">Selecciona un torneo primero</option>
                    </select>
                </div>
            </div>
        </header>

        <!-- ================================================================= -->
        <!-- CONTENIDO PRINCIPAL: Grilla con Tabla, Partidos y Proyecciones     -->
        <!-- ================================================================= -->
        <main class="dashboard-grid">
            
            <!-- 1. SECCIÓN: TABLA DE POSICIONES -->
            <section class="card standings-card">
                <h2>Tabla de Posiciones</h2>
                <div class="table-responsive">
                    <table id="standings-table">
                        <thead>
                            <tr>
                                <th>Pos</th>
                                <th>Equipo</th>
                                <th>PJ</th>
                                <th>PG</th>
                                <th>PE</th>
                                <th>PP</th>
                                <th>GF</th>
                                <th>GC</th>
                                <th>DG</th>
                                <th>Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Las filas se inyectan automáticamente desde app.js -->
                        </tbody>
                    </table>
                </div>
                <!-- Indicador de carga animado -->
                <div id="standings-loader" class="loader"></div>
            </section>

            <!-- 2. SECCIÓN: RESULTADOS Y PARTIDOS DE LA JORNADA -->
            <section class="card matches-card">
                <h2>Resultados de la Jornada</h2>
                <div id="matches-grid" class="matches-grid">
                    <!-- Las tarjetas de cada partido se inyectan desde app.js -->
                </div>
                <!-- Indicador de carga animado -->
                <div id="matches-loader" class="loader"></div>
            </section>

            <!-- 3. SECCIÓN: PROYECCIÓN FINAL DE PUNTOS -->
            <section class="card projection-card">
                <h2>Proyección de Puntos Finales</h2>
                <p class="subtitle">Basado en promedio de los últimos 5 partidos</p>
                <div id="projection-list" class="projection-list">
                    <!-- La lista proyectada se inyecta desde app.js -->
                </div>
            </section>
        </main>
    </div>
    
    <!-- Script principal de interactividad -->
    <script src="/js/app.js"></script>
</body>
</html>

