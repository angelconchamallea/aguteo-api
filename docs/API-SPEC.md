# API-SPEC.md — Contrato API (Laravel ⇄ Next.js)

Base URL: `https://api.aguteobabys.cl/api/v1`
Formato: JSON, snake_case, fechas ISO 8601, precios CLP enteros.
Este documento es el contrato. Cualquier cambio de endpoint o shape se refleja AQUÍ
y en los tipos TypeScript de aguteo-web en el mismo cambio.

## Convenciones de respuesta

Éxito (colección):
```json
{ "data": [...], "meta": { "current_page": 1, "last_page": 5, "per_page": 24, "total": 112 } }
```
Éxito (recurso): `{ "data": {...} }`
Error: `{ "message": "Descripción legible en español", "errors": { "campo": ["detalle"] } }`
Códigos: 200 OK, 201 creado, 404 no existe, 422 validación, 409 conflicto (stock), 500 error.

## Endpoints públicos (catálogo)

### GET /categories
Devuelve el árbol completo para menú de navegación. Solo nodos activos.
```json
{ "data": [
  {
    "id": 1, "name": "Ropa Bebé e Infantil", "slug": "ropa-bebe",
    "color_token": "#F8C8D4", "icon": "shirt", "depth": 0,
    "children": [
      { "id": 8,  "name": "Conjuntos",    "slug": "conjuntos",    "depth": 1, "children": [] },
      { "id": 9,  "name": "Ropa interior","slug": "ropa-interior","depth": 1, "children": [] },
      { "id": 10, "name": "Pijamas",      "slug": "pijamas",      "depth": 1, "children": [] }
    ]
  }
]}
```
Los nodos raíz (depth=0) incluyen `color_token` e `icon`. Los hijos heredan
el `color_token` del ancestro raíz — no se repite en cada hijo para no inflar la respuesta.
`color_token` es un valor hex (ej "#F8C8D4"); el front lo aplica directo con `style`.

### GET /categories/{slug}
Devuelve el nodo + sus hijos directos + ruta de ancestros (breadcrumb).
```json
{ "data": {
  "id": 8, "name": "Conjuntos", "slug": "conjuntos", "depth": 1,
  "color_token": "#F8C8D4",
  "breadcrumb": [
    { "name": "Ropa Bebé e Infantil", "slug": "ropa-bebe" },
    { "name": "Conjuntos", "slug": "conjuntos" }
  ],
  "children": []
}}

### GET /age-stages
```json
{ "data": [ { "id": 1, "name": "0-3 meses", "slug": "0-3m", "tagline": "Recién llegado",
  "color_token": "#7DD9D4", "min_months": 0, "max_months": 3 } ] }
```

### GET /brands
`{ "data": [ { "id": 1, "name": "Amma", "slug": "amma", "logo_url": null } ] }`

### GET /products
Query params: `category` (slug — incluye toda la rama descendiente automáticamente),
`stage` (slug), `brand` (slug), `min_price`, `max_price`, `featured` (bool),
`search` (string), `sort` (newest|price_asc|price_desc|featured),
`page`, `per_page` (default 24, max 48).

Filtro `category`: si se pasa "ropa-bebe" (nodo raíz), devuelve productos de
Conjuntos + Ropa interior + Pijamas y cualquier subnivel futuro.
Si se pasa "conjuntos" (hoja), devuelve solo esa categoría.
El backend resuelve esto con `WHERE path LIKE '{id}/%' OR category_id = {id}`.

Item del listado:
```json
{
  "id": 12, "sku": "ROB-AGB-CONJ-001", "name": "Conjunto ositos manga larga",
  "slug": "conjunto-ositos-manga-larga", "short_description": "Algodón suave...",
  "price": 12990, "compare_at_price": null, "discount_percent": null,
  "has_variants": true, "in_stock": true,
  "rating": null, "reviews_count": 0,
  "brand": { "name": "Aguteo Babys", "slug": "aguteo-babys" },
  "category": {
    "id": 8, "name": "Conjuntos", "slug": "conjuntos",
    "color_token": "#F8C8D4",
    "root": { "name": "Ropa Bebé e Infantil", "slug": "ropa-bebe" }
  },
  "age_stages": [ { "slug": "0-3m", "color_token": "#7DD9D4" } ],
  "cover_image_url": "https://...", "featured": false
}
```

**Reviews (Opción B — preparado, apagado en v1):** `rating` y `reviews_count` son
parte del contrato desde v1, pero la API los devuelve `null` y `0` respectivamente
hasta que el sistema de reseñas se active (v2). El frontend (ProductCard) solo pinta
estrellas si `rating !== null`. Así el diseño está listo sin construir la lógica de
moderación todavía. NO crear la tabla `reviews` ni endpoints de reseñas en v1.

### GET /products/{slug}
Todo lo anterior más: `description`, `images: [{url, alt_text}]`,
`variants: [{ id, size, color, sku, stock, price }]` (price ya resuelto con override),
`category` (con breadcrumb completo hasta la raíz), `tags`, `related_guides: [{title, slug}]`.
NUNCA incluir: cost_price, low_stock_threshold.

### GET /guides?stage=6-12m  ·  GET /guides/{slug}
Guía: id, title, slug, excerpt, body (markdown), cover_image_url,
age_stage {…}, products: [items de listado].

### GET /shipping-rates?commune_id=123
`{ "data": [ { "id": 1, "name": "Despacho estándar RM", "price": 3500, "free_from_amount": 30000, "estimated_days": "2-4 días hábiles" } ] }`

### POST /coupons/validate
Body: `{ "code": "BIENVENIDA10", "subtotal": 25000, "items": [{"product_id":12,"quantity":2}] }`
200: `{ "data": { "code": "BIENVENIDA10", "type": "percentage", "value": 10, "discount_amount": 2500 } }`
422: `{ "message": "El cupón expiró el 01-06-2026" }`

## Endpoints de checkout

### POST /orders
Body:
```json
{
  "customer": { "name": "María Pérez", "email": "maria@mail.com", "phone": "+56912345678" },
  "shipping_address": { "region_id": 13, "commune_id": 101, "street": "Av. Siempre Viva",
    "number": "742", "apartment": "depto 3B", "notes": "dejar en conserjería" },
  "shipping_rate_id": 1,
  "coupon_code": "BIENVENIDA10",
  "items": [ { "product_id": 12, "product_variant_id": 45, "quantity": 2 } ]
}
```
El backend recalcula TODOS los precios desde DB (ignora cualquier precio del cliente),
valida stock, crea la orden `pending` e inicia Webpay.

201:
```json
{ "data": { "order_number": "AGB-000123",
  "webpay": { "url": "https://webpay3g.transbank.cl/...", "token": "e9d5..." } } }
```
El front hace POST por formulario a `webpay.url` con `token_ws=token` (redirección).
409 si stock insuficiente: `{ "message": "Body ositos talla 3-6m: quedan 1 unidades" }`

### GET /orders/{order_number}/status?email=maria@mail.com
Para la página de confirmación. Devuelve status, total, items (snapshots), paid_at.
Requiere email coincidente (evita enumeración de órdenes).

## Endpoints internos (no front)

- `GET|POST /webpay/return` — return_url de Transbank. Hace commit(). Ver FLUJO-WEBPAY.md.
- Rutas Filament (`/admin`) fuera de la API, protegidas por sesión.

## Caching (Next.js)

- /categories (árbol), /age-stages, /brands: revalidate 3600s.
- /products listado y detalle: ISR revalidate 300s + revalidación on-demand
  (webhook desde Filament al guardar producto, llamando al endpoint /api/revalidate de Next).
- Carrito y checkout: sin cache, siempre dinámico.
