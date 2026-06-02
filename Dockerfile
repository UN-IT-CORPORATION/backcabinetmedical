# 1. Utilisation de l'image PHP avec Apache
FROM php:8.2-apache

# 2. Installation des dépendances système nécessaires
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

# 4. Installation des extensions PHP pour Laravel et MySQL
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 5. Configuration d'Apache pour pointer vers le dossier /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Activation du module rewrite d'Apache (indispensable pour les routes Laravel)
RUN a2enmod rewrite

# 7. Copie des fichiers du projet dans le conteneur
COPY . /var/www/html
WORKDIR /var/www/html

# 8. Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 9. Installation des dépendances PHP (sans les outils de dev pour la prod)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 10. Fixation des permissions initiales
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Création d'un script de démarrage (Entrypoint)
RUN echo '#!/bin/bash\n\
# On sature les permissions des logs et du cache au démarrage\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache\n\
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache\n\
\n\
# On attend que la DB soit prête, puis on migre et on seed\n\
# CORRECTION : Utilisation de --class=Admin car votre fichier se nomme Admin.php\n\
php artisan migrate --force\n\
php artisan db:seed --class=DatabaseSeeder --force\n\
php artisan storage:link\n\
\n\
# On vide les caches pour éviter les erreurs de config\n\
php artisan config:cache\n\
php artisan route:cache\n\
\n\
# Lancement d'Apache en premier plan\n\
exec apache2-foreground' > /usr/local/bin/start-app.sh

# 12. Rendre le script exécutable
RUN chmod +x /usr/local/bin/start-app.sh

# 13. Exposer le port (Apache utilise le port 80 par défaut)
EXPOSE 80

# 14. Commande finale pour démarrer l'application
CMD ["/usr/local/bin/start-app.sh"]
