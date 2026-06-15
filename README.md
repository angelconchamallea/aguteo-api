# aguteo-api

Backend de **Aguteo Babys** — ecommerce chileno de productos para bebés de 0 a 24 meses.
API REST + panel de administración + pagos. Consumido por `aguteo-web` (Next.js).

## Stack

Laravel 11 · PHP 8.3 · PostgreSQL 16 · Redis · Filament 3 (admin) · Webpay Plus (Transbank).
Local: Laravel Sail (Docker). Producción: Docker Compose en Vultr Santiago.

## Empieza por acá (orden de lectura para humanos y agentes de IA)

1. **CLAUDE.md** — qué es este repo, qué te toca, qué NO te toca, convenciones.
2. **docs/MODELO-DATOS.md** — esquema de base de datos (fuente de verdad).
3. **docs/API-SPEC.md** — contrato de la API con el frontend.
4. **docs/FLUJO-WEBPAY.md** — flujo de pago (zona crítica).
5. **docs/FILAMENT-CONVENCIONES.md** — reglas del admin.
6. **docs/DOCKER-README.md** — entornos local y producción.

Estado del proyecto y próximo hito: ver **ESTADO.md**.
Prompts para trabajar con IA: ver **PROMPTS.md** y **PROMPTS-CONSTRUCCION.md**.

## Arranque local

```bash
composer create-project laravel/laravel .   # solo la primera vez
php artisan sail:install                     # seleccionar pgsql, redis, mailpit
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
```
App en http://localhost · Emails de prueba (Mailpit) en http://localhost:8025

## Reglas de oro (detalle en CLAUDE.md)

- Este repo NO genera UI de tienda. Eso es aguteo-web.
- La API es un contrato: respeta docs/API-SPEC.md al pie de la letra.
- Una orden es `paid` solo tras commit() de Webpay verificado.
- `cost_price` jamás sale en la API pública.