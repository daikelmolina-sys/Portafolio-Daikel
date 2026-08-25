# ⚽ StatPro — Dashboard de Estadísticas y Proyecciones de Fútbol

Plataforma integral desarrollada en **Laravel 13** y arquitectura orientada a servicios para el seguimiento y proyección matemática de torneos de fútbol, jornadas, tablas de posiciones y partidos con **aislamiento temporal** de estadísticas.

---

## 🚀 Características Principales

1. **Aislamiento Temporal de Estadísticas:**
   - La tabla de posiciones se congela a la fecha de finalización de cada jornada.
   - Partidos jugados posteriormente no alteran de forma retrospectiva las fotografías (snapshots) de jornadas anteriores.

2. **Cálculo Matemático de Proyecciones:**
   - Proyección de puntos al final de temporada basada en el rendimiento ponderado de los últimos $N$ partidos (por defecto 5 fechas):
     $$\text{Puntos Proyectados} = \text{Pts Actuales} + (\text{Partidos Restantes} \times \text{Promedio Pts Últimos 5 Partidos})$$

3. **Sincronización Idempotente con APIs Deportivas:**
   - Comando Artisan `php artisan sync:matches` para consumir proveedores externos y calcular tablas automáticamente sin duplicar información.

4. **Interfaz Interactiva (SPA):**
   - Selector dinámico de torneos y jornadas en tiempo real con Vanilla JS, Glassmorphism y Dark Theme.

---

## 🛠️ Requisitos del Entorno

- **PHP >= 8.3** (Compatible con PHP 8.4)
- **Composer >= 2.x**
- **Node.js & NPM** (Opcional, para assets)
- **SQLite / MySQL / PostgreSQL** (por defecto configurado con SQLite)

---

## 📦 Instalación y Configuración

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd "pagina futbol estadistica"

# 2. Instalar dependencias de PHP
composer install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Ejecutar migraciones y sembrar datos de ejemplo
php artisan migrate:fresh --seed

# 5. Iniciar servidor local
php artisan serve
```

---

## 🧪 Ejecución de Pruebas Automatizadas

El proyecto cuenta con una suite completa de pruebas unitarias y de integración (PHPUnit):

```bash
php artisan test
```

### Cobertura de Pruebas:
- **`StandingsCalculatorServiceTest`**: Validación de aislamiento temporal, acumulación de puntos (3-1-0), diferencia de goles y fórmula de proyecciones.
- **`FootballApiTest`**: Validación de endpoints `/api/tournaments`, `/api/matchdays/{id}/standings`, `/api/matchdays/{id}/matches` y renderizado de la interfaz.
- **`SyncMatchesCommandTest`**: Validación de sincronización con mock de API y persistencia en base de datos.

---

## 🐳 Despliegue con Docker

El proyecto incluye soporte listo para producción o desarrollo mediante Docker:

```bash
docker-compose up -d --build
```

---

## 📂 Estructura del Proyecto

- `app/Services/StandingsCalculatorService.php`: Motor de cálculo de tablas y proyecciones.
- `app/Services/SportsApiService.php`: Cliente Guzzle para conexión con APIs deportivas.
- `app/Console/Commands/SyncMatchesCommand.php`: Comando CLI para sincronización de datos.
- `routes/web.php`: Rutas web y API REST.
- `public/js/app.js`: Lógica frontend reactiva.
- `public/css/style.css`: Estilos de la interfaz.
- `tests/`: Pruebas unitarias y de integración.
