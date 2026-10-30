
# ==========================================
# ETAPA 1: Compilar frontend con Node.js
# ==========================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


# ==========================================
# ETAPA 2: Laravel con PHP 8.3 y Apache
# ==========================================
FROM php:8.3-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        intl \
        zip \
        gd \
        mbstring \
        xml \
        bcmath \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# Configurar Apache para Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar proyecto Laravel
COPY . .

# Copiar frontend compilado
COPY --from=frontend /app/public/build ./public/build

# Instalar dependencias PHP
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# Preparar directorios de Laravel
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Crear enlace simbólico para imágenes públicas
RUN php artisan storage:link

EXPOSE 80

CMD ["apache2-foreground"]
