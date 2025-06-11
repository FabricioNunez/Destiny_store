# Dockerfile

FROM php:8.2-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    git unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev sqlite3 libsqlite3-dev libpq-dev \
    && docker-php-ext-install pdo pdo_sqlite zip mbstring tokenizer xml


# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar archivos
COPY . /var/www/html

WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader

RUN cp .env.example .env

RUN php artisan key:generate

RUN php artisan migrate --force

RUN php artisan storage:link

EXPOSE 80

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
