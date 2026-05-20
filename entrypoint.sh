#!/bin/sh
set -e

if [ ! -d "vendor" ]; then
    echo "--- 📦 Carpeta vendor no encontrada. Instalando dependencias... ---"
    composer install --no-interaction --optimize-autoloader
fi

exec "$@"