# DOCKER-README.md — Infraestructura Aguteo Babys

Decisión: **Sail en local, Docker Compose en producción** (Vultr Santiago).
Next.js NO se dockeriza (local: npm run dev / producción: Vercel).

## Estructura en el repo aguteo-api

```
aguteo-api/
├── docker/
│   ├── Dockerfile          # multi-stage producción
│   ├── entrypoint.sh
│   └── nginx.conf
├── docker-compose.yml      # lo genera Sail (desarrollo)
└── docker-compose.prod.yml # producción
```

## Desarrollo local (tu PC con Docker Desktop)

```bash
composer create-project laravel/laravel aguteo-api && cd aguteo-api
php artisan sail:install   # seleccionar: pgsql, redis, mailpit
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```
- App: http://localhost · Mailpit (emails de prueba): http://localhost:8025
- Alias recomendado: `alias sail='./vendor/bin/sail'`
- En Windows: usar WSL2 (Docker Desktop lo pide igual). El repo debe vivir
  DENTRO del filesystem de WSL (~/projects), no en C:\ — la diferencia de
  rendimiento es 10x.

## Producción (Vultr) — setup inicial una sola vez

```bash
# 1. En el servidor Ubuntu 24.04
curl -fsSL https://get.docker.com | sh
usermod -aG docker $USER   # re-login después

# 2. Firewall mínimo
ufw allow OpenSSH && ufw allow 80 && ufw allow 443 && ufw enable

# 3. Código y configuración
git clone <repo> /srv/aguteo-api && cd /srv/aguteo-api
cp .env.example .env       # editar: APP_ENV=production, APP_DEBUG=false,
                           # DB_*, credenciales Webpay PRODUCTIVAS, etc.

# 4. Primer certificado SSL (antes, apuntar DNS api.aguteobabys.cl a la IP):
#    en docker/nginx.conf comentar el bloque "return 301" temporalmente
docker compose -f docker-compose.prod.yml up -d nginx
docker compose -f docker-compose.prod.yml run --rm certbot certonly \
  --webroot -w /var/www/certbot -d api.aguteobabys.cl --email tu@email.cl --agree-tos
#    descomentar el bloque 301 y levantar todo:

# 5. Levantar todo
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force
```

## Deploy de cambios (rutina)

```bash
cd /srv/aguteo-api
git pull
docker compose -f docker-compose.prod.yml up -d --build
# migraciones corren solas (MIGRATE_ON_BOOT=true en compose)
```

## Backups (NO opcional — es la tienda)

Cron diario en el host (no en contenedor), ejemplo 3 AM:
```bash
#!/bin/bash
# /srv/backups/backup.sh
STAMP=$(date +%F)
docker compose -f /srv/aguteo-api/docker-compose.prod.yml exec -T postgres \
  pg_dump -U $DB_USERNAME $DB_DATABASE | gzip > /srv/backups/db-$STAMP.sql.gz
# storage (imágenes de productos)
docker run --rm -v aguteo-api_storage:/data -v /srv/backups:/backup alpine \
  tar czf /backup/storage-$STAMP.tar.gz -C /data .
# retener 14 días local
find /srv/backups -name "*.gz" -mtime +14 -delete
# TODO: subir a Vultr Object Storage o similar (backup fuera del servidor)
```
Regla: un backup que vive solo en el mismo servidor no es backup.

## Comandos útiles

```bash
docker compose -f docker-compose.prod.yml logs -f app      # logs Laravel
docker compose -f docker-compose.prod.yml logs -f queue    # ver jobs/emails
docker compose -f docker-compose.prod.yml exec app php artisan tinker
docker compose -f docker-compose.prod.yml exec postgres psql -U $DB_USERNAME $DB_DATABASE
docker compose -f docker-compose.prod.yml restart queue    # tras cambiar jobs
```

## Reglas duras

1. `pgdata` y `storage` son volúmenes nombrados: JAMÁS `docker compose down -v` en producción.
2. `.env` de producción no entra a git ni a la imagen.
3. La DB no expone puertos a internet. Acceso por túnel SSH.
4. Tras cambiar código de jobs, reiniciar el contenedor queue (no recoge cambios solo).
