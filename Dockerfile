FROM php:8.2-apache

# Fix MPM conflict: rimuovi tutti gli MPM abilitati, poi abilita solo mpm_prefork + rewrite
RUN rm -f /etc/apache2/mods-enabled/mpm_*.conf /etc/apache2/mods-enabled/mpm_*.load && \
    a2enmod mpm_prefork rewrite

# Estensioni PHP necessarie
RUN docker-php-ext-install pdo pdo_mysql

# Copia tutto il progetto
COPY . /var/www/html/

# Imposta la document root su /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# Permetti .htaccess nella public root
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Installa git e unzip
RUN apt-get update && apt-get install -y git unzip && rm -rf /var/lib/apt/lists/*

# Installa Composer e le dipendenze PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

EXPOSE 80