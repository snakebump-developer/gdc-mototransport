FROM php:8.2-apache

# Create a configuration to disable conflicting MPMs
RUN echo "# Disable conflicting MPMs\n\
<IfModule mpm_event_module>\n\
  # mpm_event disabled\n\
</IfModule>\n\
<IfModule mpm_worker_module>\n\
  # mpm_worker disabled\n\
</IfModule>" > /etc/apache2/conf-available/disable-mpm.conf && \
a2enconf disable-mpm && \
a2dismod mpm_event mpm_worker || true && \
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