# app code is bind-mounted at runtime, not baked in

FROM php:8.2-apache

# mysqli is the only DB driver used anywhere in the codebase
RUN docker-php-ext-install mysqli \
    && a2enmod headers

# short open tags + bigger upload limits
COPY php-uploads.ini /usr/local/etc/php/conf.d/zz-vitadb.ini

# blocks HTTP access to deploy/, .git/, config.php
COPY apache-vitadb.conf /etc/apache2/conf-available/vitadb-security.conf
RUN a2enconf vitadb-security

# upload path (see deploy/README.md) - volumes mount onto this
RUN mkdir -p \
      /customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/screenshots \
      /customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/icons \
      /customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/avatars \
    && chown -R www-data:www-data /customers

# default avatar placeholder, seeded into the volume
COPY unknown.png /customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/avatars/unknown.png
RUN chown www-data:www-data /customers/8/5/0/rinnegatamante.it/httpd.www/vitadb/avatars/unknown.png

# entrypoint creates the relative-path symlinks at container start
COPY docker-entrypoint.sh /usr/local/bin/vitadb-entrypoint.sh
RUN chmod +x /usr/local/bin/vitadb-entrypoint.sh
ENTRYPOINT ["vitadb-entrypoint.sh"]
CMD ["apache2-foreground"]

WORKDIR /var/www/html
