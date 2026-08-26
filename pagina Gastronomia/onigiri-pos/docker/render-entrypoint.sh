#!/bin/sh
set -e

# Configurar puerto dinámico (Render inyecta $PORT, por defecto 8080)
PORT=${PORT:-8080}
export PORT

echo "🍱 Iniciando Onigiri POS en el puerto ${PORT}..."

# Generar configuración de Nginx con el puerto correspondiente
mkdir -p /etc/nginx/conf.d /etc/nginx/sites-available /etc/nginx/sites-enabled
envsubst '${PORT}' < /etc/nginx/templates/render-nginx.conf.template > /etc/nginx/sites-available/default
cp /etc/nginx/sites-available/default /etc/nginx/conf.d/default.conf
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default 2>/dev/null || true

# Asegurar permisos en storage y bootstrap/cache
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generar APP_KEY si no existe
if [ -z "$APP_KEY" ]; then
    if [ ! -f ".env" ]; then
        cp .env.example .env 2>/dev/null || true
    fi
    php artisan key:generate --force || true
fi

# Crear archivo de base de datos SQLite si está configurado
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_HOST" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi

# Intentar migraciones de base de datos
echo "📦 Ejecutando migraciones de base de datos..."
php artisan migrate --force || echo "⚠️ Advertencia: No se pudieron ejecutar las migraciones inmediatamente. Continuando arranque..."

# Optimizar Laravel para producción
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizando caché de Laravel para producción..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Iniciar PHP-FPM en segundo plano
echo "🐘 Iniciando PHP-FPM..."
php-fpm -D

# Iniciar Nginx en primer plano
echo "🌐 Iniciando Nginx..."
exec nginx -g "daemon off;"
