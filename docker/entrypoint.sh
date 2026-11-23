#!/bin/sh
# Solo desarrollo: el código se monta desde tu carpeta. Si ahí no hay vendor/
# (por ejemplo, .gitignore lo excluye), se usa el de la imagen.
set -e

if [ ! -f /var/www/html/vendor/autoload.php ] && [ -d /opt/vendor ]; then
    echo "[finanzas] vendor/ no encontrado: copiando el de la imagen..."
    cp -r /opt/vendor /var/www/html/vendor
fi

exec docker-php-entrypoint "$@"