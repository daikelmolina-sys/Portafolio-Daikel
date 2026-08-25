# 🍙 Onigiri POS — Sistema de Gestión y Pedidos

> **E-commerce + Punto de Venta** para emprendimiento de comida rápida asiática.  
> Stack: Laravel 12 · React · MySQL 8 · Redis · Docker

---

## ⚙️ Arquitectura del Entorno Docker

```
┌─────────────────────────────────────────────────────┐
│                    Docker Network                    │
│                  (onigiri_network)                   │
│                                                      │
│  ┌───────────┐    ┌───────────┐    ┌─────────────┐  │
│  │   Nginx   │───▶│ PHP-FPM   │───▶│  MySQL 8    │  │
│  │  :8080    │    │  app:9000 │    │   db:3306   │  │
│  └───────────┘    └─────┬─────┘    └─────────────┘  │
│                         │                            │
│                   ┌─────▼─────┐    ┌─────────────┐  │
│                   │   Queue   │───▶│    Redis     │  │
│                   │  Worker   │    │  redis:6379  │  │
│                   └───────────┘    └─────────────┘  │
└─────────────────────────────────────────────────────┘
```

| Servicio | Imagen | Puerto Host | Puerto Container |
|----------|--------|-------------|-----------------|
| `web` | nginx:1.25-alpine | **8080** | 80 |
| `app` | php:8.2-fpm (custom) | — | 9000 |
| `db` | mysql:8.0 | **3307** | 3306 |
| `redis` | redis:7.2-alpine | **6380** | 6379 |
| `queue` | php:8.2-fpm (custom) | — | — |

---

## 🚀 Levantando el Entorno (Paso a Paso)

### Prerrequisitos
- **Docker Desktop** ≥ 4.x instalado y corriendo
- **Git** instalado

### 1. Clonar e ingresar al proyecto
```bash
git clone <url-del-repo> onigiri-pos
cd onigiri-pos
```

### 2. Copiar variables de entorno
```bash
cp .env.example .env
```
> Los valores por defecto funcionan directamente con Docker. No necesitas cambiar nada para desarrollo.

### 3. Construir las imágenes
```bash
docker compose build --no-cache
```
> ⏳ La primera vez puede tardar 3-5 minutos (descarga de capas de PHP, instalación de extensiones).

### 4. Levantar todos los servicios
```bash
docker compose up -d
```

### 5. Instalar dependencias PHP dentro del contenedor
```bash
docker compose exec app composer install
```

### 6. Generar Application Key de Laravel
```bash
docker compose exec app php artisan key:generate
```

### 7. Ejecutar migraciones y seeders
```bash
docker compose exec app php artisan migrate --seed
```

### 8. (Opcional) Instalar dependencias Node y compilar assets
```bash
docker compose exec app npm install
docker compose exec app npm run build
```

### ✅ Verificar que todo está corriendo
```bash
docker compose ps
```

Deberías ver `app`, `web`, `db`, `redis` y `queue` en estado **running**.

### 🌐 Acceder a la aplicación
- **Frontend / API:** http://localhost:8080
- **Base de datos:** `localhost:3307` (usuario: `onigiri_user`, pass: `onigiri_secret`)
- **Redis:** `localhost:6380`

---

## 🛑 Comandos Útiles

```bash
# Detener servicios (sin eliminar datos)
docker compose stop

# Eliminar contenedores y redes (¡los volúmenes de BD se preservan!)
docker compose down

# Eliminar TODO incluyendo volúmenes (⚠️ borra la BD)
docker compose down -v

# Ver logs en tiempo real
docker compose logs -f

# Ver logs de un servicio específico
docker compose logs -f app
docker compose logs -f queue

# Ejecutar comandos Artisan
docker compose exec app php artisan <comando>

# Acceder al shell del contenedor PHP
docker compose exec app bash

# Limpiar cachés de Laravel
docker compose exec app php artisan optimize:clear
```

---

## 📁 Estructura Docker

```
docker/
├── php/
│   ├── Dockerfile      # PHP 8.2 FPM + extensiones + Composer
│   └── php.ini         # Configuración PHP personalizada
├── nginx/
│   └── default.conf    # Proxy inverso → PHP-FPM
└── mysql/
    └── init.sql        # Script de inicialización de la BD
```

---

## 🗺️ Roadmap de Desarrollo

- [x] **Paso 1** — Entorno Docker (Nginx + PHP-FPM + MySQL + Redis)
- [x] **Paso 2** — Migraciones de base de datos (3FN) + Seeders
- [x] **Paso 3** — Modelos Eloquent, Repositories y Actions (inventario transaccional)
- [x] **Paso 4** — API RESTful (Catálogo, Carrito, Pedidos, Facturas)
- [x] **Paso 5** — Frontend React + Sistema de diseño CSS japonés bespoke
- [x] **Paso 6** — Optimistic UI (carrito reactivo con estado global)
- [x] **Paso 7** — Módulo POS Kiosk Mode + Job asíncrono de PDF
