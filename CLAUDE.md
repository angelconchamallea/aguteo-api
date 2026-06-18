# CLAUDE.md — aguteo-api (Backend Laravel)

> Contexto para agentes de IA. `AGENTS.md` espeja este archivo (Codex).
> Documentos obligatorios antes de codear: docs/MODELO-DATOS.md, docs/API-SPEC.md,
> docs/FLUJO-WEBPAY.md, docs/FILAMENT-CONVENCIONES.md, docs/DOCKER-README.md.
> Última actualización: 2026-06-09

## 0. ESTÁS AQUÍ: el backend

ESTE repo es `aguteo-api`. Tu trabajo: API REST, panel de administración,
pagos, emails, base de datos. NO escribes nada de la tienda visible al cliente.

## 1. Mapa del sistema completo

**Aguteo Babys** es un ecommerce chileno de productos para bebés de 0 a 24 meses
(7 categorías: Ropa Bebé, Alimentación, Cuidado e Higiene, Descanso y Baño,
Juguetes, Mamá, Packs y Regalos), fundado por papás de gemelos. Diferenciador: navegación
y mini-guías **por etapa del bebé** que derivan a productos. Mercado: Chile,
CLP, español. Hoy venden por Instagram; esta es su primera tienda web.

El sistema son DOS repos que se comunican SOLO por la API REST:

```
┌──────────────────────────┐   API REST (docs/API-SPEC.md)   ┌─────────────────────────┐
│ aguteo-web (OTRO repo)   │ ───────────────────────────────▶│ aguteo-api (ESTE repo)  │
│ Next.js 15 + TS + Tailwind│                                 │ Laravel 11 + PHP 8.3    │
│ Tienda pública            │                                 │ PostgreSQL 16 + Redis   │
│ Deploy: Vercel            │                                 │ Filament 3 (admin)      │
└──────────────────────────┘                                 │ Webpay Plus (Transbank) │
                                                              │ Deploy: Clouding.io     │
                                                              │ (Docker Compose)        │
                                                              └─────────────────────────┘
```

## 2. Responsabilidades de ESTE repo

- Esquema de base de datos (migrations = única fuente del esquema; espejo en docs/MODELO-DATOS.md).
- API REST según docs/API-SPEC.md — es un CONTRATO con el otro repo: shapes exactos,
  snake_case, sin campos extra ni faltantes. Si cambias el contrato, actualiza el
  doc en el mismo commit y avisa que aguteo-web necesita el cambio.
- Panel admin con Filament 3 según docs/FILAMENT-CONVENCIONES.md. NO construir admin a mano.
- Webpay Plus según docs/FLUJO-WEBPAY.md. Regla de oro: orden `paid` SOLO tras
  commit() exitoso con monto verificado, en transacción de DB.
- TODA la lógica de precios, descuentos, cupones, stock y envíos vive aquí.
  El frontend solo muestra lo que esta API responde.
- Categorías como árbol jerárquico (Materialized Path): tabla única `categories`
  con `parent_id` y `path`. Sin tabla `sections` separada. Ver docs/MODELO-DATOS.md.
- Emails transaccionales (colas Redis), importador Excel de productos, jobs programados.

## 3. Fronteras: lo que NO haces aquí

- NO generas HTML/React/UI de la tienda (eso es aguteo-web).
- NO devuelves textos de UI pensados para pantallas (el front tiene su microcopy);
  los `message` de error de la API sí van en español claro.
- NUNCA serializas `cost_price` ni `low_stock_threshold` en la API pública.
- NO implementas features del listado EXCLUIDO (sección 6).

## 4. Convenciones de código

- Código, tablas, variables, comentarios: inglés. Mensajes de validación/emails: español chileno cálido.
- Laravel idiomático: Eloquent, Form Requests, API Resources, Policies, Jobs en cola.
- Precios: CLP enteros (sin decimales) en TODO el sistema.
- Stock: descuento atómico (DB transaction + lockForUpdate) al confirmar pago.
- order_items son snapshots: jamás se recalculan desde products.
- Tests Pest mínimos: filtros de catálogo, validación de cupones, flujo Webpay completo (SDK mockeado).
- Commits: Conventional Commits.

## 5. Entornos

- Local: Laravel Sail (pgsql, redis, mailpit). Emails de prueba en http://localhost:8025.
- Producción: Docker Compose en Clouding.io (docs/DOCKER-README.md). No inventes otra infraestructura.
- Webpay: credenciales de INTEGRACIÓN en local (públicas de Transbank); productivas
  solo en el .env del servidor. Jamás en git.

## 6. Alcance v1 — EXCLUIDO (no implementar aunque parezca fácil)

Reviews, wishlist, login obligatorio, descuentos automáticos activos (el esquema
existe, la lógica NO se activa), multi-moneda, multi-idioma, suscripciones,
boleta electrónica SII (el campo rut ya se captura para el futuro).

## 7. Si los docs y tu criterio chocan

Los docs ganan. Si crees que un doc está mal, dilo explícitamente y espera
confirmación humana antes de desviarte. No "mejores" silenciosamente.
