FROM php:8.2-cli

# Installa dipendenze di sistema (ICU per intl, git/unzip per Composer)
RUN apt-get update && apt-get install -y libicu-dev git unzip && rm -rf /var/lib/apt/lists/*

# Estensioni PHP necessarie
RUN docker-php-ext-install pdo pdo_mysql intl

# Installa Composer e le dipendenze PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock* /var/www/html/
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

# Copia tutto il progetto
COPY . /var/www/html/
WORKDIR /var/www/html

# Railway assegna la porta via $PORT, default 8080
ENV PORT=8080
EXPOSE 8080

# Usa il server PHP built-in con il router già esistente
CMD php src/seed-moto.php && php -S 0.0.0.0:${PORT} -t public public/router.php