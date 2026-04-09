FROM php:8.2-apache

# Estensioni PHP necessarie
RUN docker-php-ext-install pdo pdo_mysql

# Abilita mod_rewrite di Apache
RUN a2enmod rewrite

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

# Installa Composer e le dipendenze PHP
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

EXPOSE 80
