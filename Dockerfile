# 1. Utilisation de l'image PHP avec Apache
FROM php:8.2-apache

# 2. Installation des dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql zip mbstring exif pcntl bcmath gd

# 3. Nettoyage du cache apt pour réduire la taille de l'image
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 4. Configuration d'Apache pour pointer vers le dossier /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Activation du module rewrite d'Apache
RUN a2enmod rewrite

# 6. Définition du dossier de travail et copie des fichiers
WORKDIR /var/www/html
COPY . /var/www/html

# 7. Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 8. Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 9. Fixation des permissions initiales
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Création d'un script de démarrage (Entrypoint)
RUN echo '#!/bin/bash\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache\n\
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache\n\
\n\
php artisan migrate --force\n\
\n\
# On lance le seed UNIQUEMENT si tu as sécurisé ton seeder avec firstOrCreate/insertOrIgnore\n\
php artisan db:seed --class=DatabaseSeeder --force\n\
\n\
# On crée le lien storage s il n existe pas déjà pour éviter les erreurs\n\
php artisan storage:link || true\n\
\n\
php artisan config:cache\n\
php artisan route:cache\n\
\n\
exec apache2-foreground' > /usr/local/bin/start-app.sh

# 11. Rendre le script exécutable
RUN chmod +x /usr/local/bin/start-app.sh

# 12. Exposer le port 80
EXPOSE 80

# 13. Commande finale
CMD ["/usr/local/bin/start-app.sh"]
