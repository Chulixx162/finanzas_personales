# syntax=docker/dockerfile:1
# ============================================================
# Finanzas Personales - imagen PHP 8.3 + Apache
#
#   docker build --target development -t finanzas:dev .
#   docker build --target production  -t finanzas:prod .
#
# Lo normal es usar docker-compose.yml (desarrollo) o
# docker-compose.prod.yml (producción); ellos eligen el target.
# ============================================================

# ---------- Dependencias de Composer (vendor/) ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

# ---------- Base común ----------
FROM php:8.3-apache AS base

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers remoteip \
    && a2dissite 000-default \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache/finanzas.conf /etc/apache2/sites-available/finanzas.conf
RUN a2ensite finanzas

WORKDIR /var/www/html

# ---------- Desarrollo ----------
# El código se monta desde el host (ver docker-compose.yml): los cambios se ven al
# recargar. Errores visibles en pantalla (php.ini-development).
FROM base AS development
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY docker/php/zz-finanzas.ini "$PHP_INI_DIR/conf.d/zz-finanzas.ini"
# vendor/ de la imagen: se usa si el contenedor no trae uno propio.
COPY --from=vendor /app/vendor /opt/vendor
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint-finanzas
RUN chmod +x /usr/local/bin/docker-entrypoint-finanzas
ENTRYPOINT ["docker-entrypoint-finanzas"]
CMD ["apache2-foreground"]

# ---------- Producción ----------
# El código va DENTRO de la imagen; no se monta nada del host. Errores al log,
# nunca en pantalla (php.ini-production). Apache arranca como root solo para abrir
# el puerto 80; los procesos que ejecutan PHP corren como www-data.
FROM base AS production
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/zz-finanzas.ini "$PHP_INI_DIR/conf.d/zz-finanzas.ini"
COPY --chown=www-data:www-data . /var/www/html
COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/html/vendor
RUN find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} +
EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl -fsS -o /dev/null http://127.0.0.1/public/index.php || exit 1