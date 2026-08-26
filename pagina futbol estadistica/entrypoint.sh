#!/bin/sh
set -e

# Configurar puerto dinámico (Render inyecta $PORT, en local usa 8000)
PORT=${PORT:-8000}
export PORT

echo "🚀 Iniciando Fútbol Estadística en el puerto ${PORT}..."

# Generar configuración de Nginx con el puerto correspondiente
mkdir -p /etc/nginx/conf.d /etc/nginx/sites-available /etc/nginx/sites-enabled
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/sites-available/default
cp /etc/nginx/sites-available/default /etc/nginx/conf.d/default.conf
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default 2>/dev/null || true

# Crear directorios de logs, cache y base de datos con permisos totales
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Asegurar que APP_KEY sea válida para Laravel (debe comenzar con base64:)
case "$APP_KEY" in
  base64:*)
    echo "🔑 APP_KEY válida detectada."
    ;;
  *)
    echo "🔑 Generando nueva APP_KEY base64 válida para Laravel..."
    if [ ! -f ".env" ]; then
        cp .env.example .env 2>/dev/null || true
    fi
    php artisan key:generate --force || true
    ;;
esac

# Crear archivo de base de datos SQLite si está configurado
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_HOST" ]; then
    touch /var/www/html/database/database.sqlite
    chmod 666 /var/www/html/database/database.sqlite
fi

# Intentar migraciones de base de datos y seeders
echo "📦 Ejecutando migraciones de base de datos..."
php artisan migrate --force || echo "⚠️ Advertencia: No se pudieron ejecutar las migraciones."

echo "🌱 Ejecutando seeders de datos..."
php artisan db:seed --force || echo "⚠️ Advertencia: No se pudieron ejecutar los seeders."

# Optimizar cache para producción si está en entorno de producción
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizando caché de Laravel..."
    php artisan config:clear || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Iniciar PHP-FPM en segundo plano
echo "🐘 Iniciando PHP-FPM..."
php-fpm -D

# Iniciar Nginx en primer plano
echo "🌐 Iniciando Nginx..."
exec nginx -g "daemon off;"
