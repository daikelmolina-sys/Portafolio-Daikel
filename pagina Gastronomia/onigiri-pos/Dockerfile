# =============================================================================
# Stage 1: Build de assets con Node.js & Vite
# =============================================================================
FROM node:20-alpine AS node_builder

WORKDIR /app

COPY package*.json ./
RUN npm ci || npm install

COPY . .
RUN npm run build

# =============================================================================
# Stage 2: PHP 8.2-FPM + Nginx (Producción para Render)
# =============================================================================
FROM php:8.2-fpm

# Instalar dependencias del sistema, Nginx y gettext-base (para envsubst)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libsqlite3-dev \
    zip \
    unzip \
    nginx \
    gettext-base \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        xml \
        opcache

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar código fuente
COPY . /var/www/html

# Copiar assets compilados de Vite desde el stage 1
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Copiar plantilla de Nginx
COPY docker/nginx/render-nginx.conf /etc/nginx/templates/render-nginx.conf.template

# Instalar dependencias PHP de producción
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar y dar permisos al script de entrada (eliminando posibles retornos de carro CRLF)
COPY docker/render-entrypoint.sh /usr/local/bin/render-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/render-entrypoint.sh && chmod +x /usr/local/bin/render-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/render-entrypoint.sh"]
