# =============================================================================
# Dockerfile — PHP 8.2 FPM para Laravel 12
# Multi-stage no aplicado (dev-first), optimizado para desarrollo local.
# =============================================================================

FROM php:8.2-fpm

# Argumentos de construcción
ARG UID=1000
ARG GID=1000

# ---------------------------------------------------------------------------
# Dependencias del sistema
# ---------------------------------------------------------------------------
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
    zip \
    unzip \
    nano \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------------------
# Extensiones PHP
# ---------------------------------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        xml \
        opcache

# Redis extension (via PECL)
RUN pecl install redis && docker-php-ext-enable redis

# ---------------------------------------------------------------------------
# Composer
# ---------------------------------------------------------------------------
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# Configurar usuario no-root (seguridad)
# ---------------------------------------------------------------------------
RUN groupadd -g ${GID} appgroup \
    && useradd -u ${UID} -g appgroup -m appuser \
    && chown -R appuser:appgroup /var/www

# ---------------------------------------------------------------------------
# Directorio de trabajo
# ---------------------------------------------------------------------------
WORKDIR /var/www/html

USER appuser

EXPOSE 9000

CMD ["php-fpm"]
