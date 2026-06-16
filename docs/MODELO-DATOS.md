# MODELO-DATOS.md — Esquema de Base de Datos (fuente de verdad)

PostgreSQL 16 · Laravel migrations · nombres en inglés · soft deletes donde se indica.
Debe coincidir 1:1 con la plantilla Excel de carga (`plantilla-productos-aguteo-babys.xlsx`).

## Catálogo

### brands
| campo | tipo | notas |
|---|---|---|
| id | bigint pk | |
| name | string | Ej: "Amma", "Aguteo Babys" |
| slug | string unique | |
| logo_path | string nullable | |
| description | text nullable | |
| is_active | boolean default true | |

### categories (árbol jerárquico — reemplaza sections + categories anteriores)
| campo | tipo | notas |
|---|---|---|
| id | bigint pk | |
| parent_id | fk categories nullable | null = nodo raíz (ex-"section") |
| name | string | |
| slug | string unique | |
| description | text nullable | |
| image_path | string nullable | |
| color_token | string nullable | Hex (ej "#7DD9D4"). Solo en nodos raíz; la API lo entrega resuelto a los hijos. Decisión 2026-06-15: hex en vez de token nombrado. |
| icon | string nullable | Para menú de navegación y home |
| sort_order | int default 0 | |
| depth | int default 0 | 0=raíz, 1=subcategoría, 2=sub-sub (reservado) |
| path | string | Materialized path: "1/8" → queries rápidos sin CTE recursivos |
| is_active | boolean default true | |

**Patrón Materialized Path:** para obtener todos los descendientes de id=1:
`WHERE path LIKE '1/%' OR id = 1`. Para breadcrumb: explotar `path` por "/".

**Seed completo (depth 0 = raíz, depth 1 = subcategoría):**
```
1.  Ropa Bebé e Infantil          color_token: blush      depth: 0
    1.1  Conjuntos                                         depth: 1  path: 1/8
    1.2  Ropa interior                                     depth: 1  path: 1/9
    1.3  Pijamas                                           depth: 1  path: 1/10
2.  Alimentación                  color_token: butter     depth: 0
    2.1  Compotas                                          depth: 1  path: 2/11
    2.2  Jugos                                             depth: 1  path: 2/12
    2.3  Accesorios de Alimentación                        depth: 1  path: 2/13
3.  Cuidado e Higiene             color_token: aqua       depth: 0
    3.1  Pañales                                           depth: 1  path: 3/14
    3.2  Toallitas Húmedas                                 depth: 1  path: 3/15
    3.3  Cremas y Pomadas                                  depth: 1  path: 3/16
4.  Descanso y Baño               color_token: lavender   depth: 0
    4.1  Tutos y Pañales de Género                         depth: 1  path: 4/17
    4.2  Mantas y Frazadas                                 depth: 1  path: 4/18
    4.3  Toallas de Baño                                   depth: 1  path: 4/19
    4.4  Batas de Baño                                     depth: 1  path: 4/20
    4.5  Accesorios de Baño                                depth: 1  path: 4/21
5.  Juguetes                      color_token: tangerine  depth: 0
    5.1  Mordedores                                        depth: 1  path: 5/22
    5.2  Juguetes Sensoriales                              depth: 1  path: 5/23
    5.3  Juguetes Didácticos                               depth: 1  path: 5/24
    5.4  Peluches y Sonajeros                              depth: 1  path: 5/25
6.  Mamá                          color_token: sky        depth: 0
    6.1  Ropa Maternal                                     depth: 1  path: 6/26
    6.2  Ropa Interior Maternal                            depth: 1  path: 6/27
    6.3  Lactancia                                         depth: 1  path: 6/28
    6.4  Bolsos Maternales                                 depth: 1  path: 6/29
7.  Packs y Regalos               color_token: coral      depth: 0
    7.1  Pack Recién Nacido                                depth: 1  path: 7/30
    7.2  Pack Alimentación                                 depth: 1  path: 7/31
    7.3  Pack Juguetes                                     depth: 1  path: 7/32
    7.4  Pack Mamá                                         depth: 1  path: 7/33
```

**Packs en v1:** se tratan como productos simples (sin variantes de talla, descripción
detalla qué incluye). Lógica de bundle real (descuento de stock por componente) → v1.1.
**Categoría "Mamá":** age_stages queda vacío; estos productos no aparecen en el
filtro por etapa del bebé, solo navegando por su categoría.

### age_stages
id, name ("0-3 meses"), slug ("0-3m"), min_months int, max_months int,
color_token string (hex, ej "#7DD9D4"), tagline string ("Recién llegado"),
sort_order. Seed: 0-3m, 3-6m, 6-12m, 12-24m. Tabla propia (no enum) para asociar guías.

### products (soft deletes)
| campo | tipo | notas |
|---|---|---|
| id | bigint pk | |
| sku | string unique | formato CAT-MARCA-NOMBRE-### · CAT = 3 letras del nodo raíz |
| name | string | |
| slug | string unique | |
| short_description | string nullable | listado |
| description | text | página de producto |
| brand_id | fk brands | |
| category_id | fk categories | Apunta al nodo más específico (hoja). El nodo raíz se obtiene por path. |
| price | unsigned int | CLP, sin decimales |
| compare_at_price | unsigned int nullable | precio tachado |
| cost_price | unsigned int nullable | PRIVADO: margen, nunca en API pública |
| has_variants | boolean | |
| stock | unsigned int nullable | solo si has_variants=false |
| low_stock_threshold | unsigned int default 5 | |
| status | enum: active, draft, out_of_stock | |
| featured | boolean default false | |
| weight_grams | unsigned int nullable | envíos |

Relaciones: belongsToMany age_stages (pivot product_age_stage),
belongsToMany tags, hasMany product_images, hasMany product_variants.

### product_variants
id, product_id fk, size varchar(50) nullable (libre: RN, 0-3m, ..., XG, Único — decisión
2026-06-15, era enum pero PostgreSQL rechazaba tallas fuera de lista),
color string nullable, sku string unique, stock unsigned int,
price_override unsigned int nullable. Regla: si product.has_variants, el stock vive
SOLO aquí; products.stock queda null.

### product_images
id, product_id fk, path, alt_text nullable, sort_order. Primera imagen = portada.

### tags / product_tag
id, name, slug unique.

### guides (mini-guías por etapa — el diferenciador)
id, age_stage_id fk, title, slug unique, excerpt string, body longtext (markdown),
cover_image nullable, status enum(draft,published), published_at nullable.
Relación belongsToMany products (productos recomendados de la guía).

## Promociones

### coupons
| campo | tipo | notas |
|---|---|---|
| id, code unique (uppercase) | | |
| type | enum: percentage, fixed_amount, free_shipping | |
| value | unsigned int | % o CLP según type |
| min_purchase_amount | unsigned int nullable | |
| usage_limit | unsigned int nullable | total |
| usage_limit_per_customer | unsigned int nullable | por email |
| times_used | unsigned int default 0 | |
| starts_at / expires_at | timestamps nullable | |
| is_active | boolean | |
Pivotes opcionales de restricción: coupon_category (por id de nodo, cualquier nivel),
coupon_brand (vacío = aplica a todo). Al filtrar por categoría con hijo/padre,
aplica a toda la rama descendiente (mismo patrón LIKE del path).

### automatic_discounts (ESQUEMA EN v1, LÓGICA INACTIVA hasta v1.1)
id, name, type enum(percentage,fixed_amount), value, conditions json
(category_ids, brand_ids, age_stage_ids, min_amount, buy_x_get_y),
starts_at, expires_at, is_active default false.
category_ids acepta nodos de cualquier nivel; la lógica aplica a toda la rama.

## Clientes y direcciones

### customers
id, name, email unique, phone, rut string nullable (boleta electrónica futura),
password nullable (checkout invitado crea customer sin password; puede registrarse después),
soft deletes.

### regions / communes (seed completo de Chile)
regions: id, name, code, sort_order. communes: id, region_id fk, name.

### addresses
id, customer_id fk, region_id fk, commune_id fk, street, number,
apartment nullable, notes nullable, is_default boolean.

## Órdenes

### orders
| campo | tipo | notas |
|---|---|---|
| id | bigint pk | |
| order_number | string unique | formato AGB-000001 |
| customer_id | fk | |
| status | enum | pending, paid, preparing, shipped, delivered, cancelled, failed |
| subtotal / discount_total / shipping_total / total | unsigned int | CLP |
| coupon_id | fk nullable | |
| coupon_code | string nullable | snapshot |
| payment_method | string default 'webpay' | |
| webpay_token | string nullable | |
| webpay_buy_order | string nullable | |
| webpay_authorization_code | string nullable | |
| webpay_card_last4 | string nullable | |
| shipping_address | json | SNAPSHOT, nunca fk a addresses |
| shipping_rate_name | string | snapshot |
| paid_at / shipped_at / delivered_at | timestamps nullable | |
| notes | text nullable | |

### order_items
id, order_id fk, product_id fk nullable (set null on delete),
product_variant_id fk nullable, product_name string (snapshot),
variant_label string nullable (snapshot "Talla 3-6m rosado"),
sku string (snapshot), unit_price unsigned int (snapshot), quantity, line_total.
REGLA: los items son snapshots completos. Cambios futuros de precio o nombre
del producto jamás alteran órdenes históricas.

## Envíos

### shipping_zones
id, name ("Región Metropolitana"), is_active. Pivot zone_commune o zone_region.

### shipping_rates
id, shipping_zone_id fk, name ("Despacho estándar RM"), price unsigned int,
free_from_amount unsigned int nullable (envío gratis desde X), estimated_days string.

## Reglas transversales

1. Precios CLP enteros (sin decimales) en TODA la base.
2. Stock se descuenta atómicamente (DB transaction + lockForUpdate) al confirmar pago,
   no al agregar al carrito.
3. cost_price jamás se expone en API pública ni en el front.
4. Slugs autogenerados de name, editables en Filament.
5. Toda migración nueva actualiza este documento en el mismo PR/commit.
