#!/bin/sh
# Entrypoint — prepara Laravel al arrancar el contenedor.
set -e

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

# Migraciones solo si MIGRATE_ON_BOOT=true (evita sorpresas en restarts)
if [ "$MIGRATE_ON_BOOT" = "true" ]; then
  php artisan migrate --force
fi

exec "$@"
