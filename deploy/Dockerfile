# VitaDB deploy image.
#
# The application code itself is NOT copied into this image - it's bind-mounted
# from the git checkout at runtime (see docker-compose.yml). This image only
# provides the PHP/Apache runtime, the one extension the app needs, and a
# couple of hardening tweaks. Rebuilding it is cheap and doesn't require
# touching any app files.

FROM php:8.2-apache

# mysqli is the only DB driver used anywhere in the codebase
RUN docker-php-ext-install mysqli \
    && a2enmod headers

# PHP settings VitaDB needs: short open tags (<? ... ?>), bigger upload limits
# for homebrew screenshots/icons than the PHP defaults allow.
COPY php-uploads.ini /usr/local/etc/php/conf.d/zz-vitadb.ini

# Blocks direct browser/HTTP access to deploy/, .git/, config.php, and any
# *.env file that might end up under the web root. PHP's own include()
# of config.php on the server side is completely unaffected by this - it
# only blocks requests coming in over HTTP.
COPY apache-vitadb.conf /etc/apache2/conf-available/vitadb-security.conf
RUN a2enconf vitadb-security

# screenshots.php, icon0.php, and avatar.php write uploads to a hardcoded
# absolute path inherited from the original author's shared hosting layout
# (see deploy/README.md for details). Rather than edit those files, we make
# sure this exact path exists and is owned by www-data here; docker-compose.yml
# then mounts named volumes onto it so the first `docker compose up` populates
# them correctly.
RUN mkdir -p \
      /customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/screenshots \
      /customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/icons \
      /customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/avatars \
    && chown -R www-data:www-data /customers

WORKDIR /var/www/html
