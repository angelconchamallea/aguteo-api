# FILAMENT-CONVENCIONES.md — Reglas del Admin (Filament 3)

El admin lo usa Angel (técnico) hoy, pero debe poder usarlo una persona no
técnica mañana. Optimizar para claridad, no para densidad.

## Reglas generales

1. **Todo en español**: labels, columnas, botones, notificaciones, validaciones.
   `->label('Precio')`, no "Price". Modelos con `$modelLabel` y `$pluralModelLabel`
   ('Producto'/'Productos').
2. **Navegación agrupada** con `navigationGroup`:
   - Catálogo: Productos, Marcas, Secciones, Categorías, Etapas
   - Ventas: Pedidos, Cupones
   - Contenido: Mini-guías
   - Configuración: Zonas de envío, Tarifas
3. **Badges de estado con color consistente** en todas las tablas:
   - Pedidos: pending=gray "Pendiente", paid=success "Pagado",
     preparing=info "Preparando", shipped=warning "Enviado",
     delivered=success "Entregado", cancelled/failed=danger.
   - Productos: active=success "Activo", draft=gray "Borrador",
     out_of_stock=danger "Agotado".
4. **Precios**: inputs con prefijo $, sin decimales; columnas con
   `->money('CLP', divideBy: 1)` o formato $12.990.
5. **Slugs autogenerados** del nombre (afterStateUpdated en name → slug),
   editables solo con confirmación.
6. **Soft deletes visibles**: filtro TrashedFilter en Products y Customers.
7. **Toda acción destructiva con requiresConfirmation()** y texto claro de
   consecuencias.

## Reglas por resource

### ProductResource (el más usado — máximo cuidado)
- Form en tabs: "General" (nombre, sku, marca, sección, categoría, descripciones),
  "Precios y stock" (price, compare_at_price, cost_price con helper
  "Solo visible para ti — nunca se muestra en la tienda", stock condicional a
  has_variants), "Clasificación" (etapas multiselect, tags, featured),
  "Imágenes" (repeater/relation con reorder drag & drop).
- has_variants como Toggle: al activarlo, ocultar stock del producto y mostrar
  aviso "El stock ahora se maneja por talla en la pestaña Variantes".
- Relation managers: VariantsRelationManager (talla, color, sku autogenerado
  sufijo, stock, price_override), ImagesRelationManager.
- Tabla: thumbnail, nombre, sección (badge con color_token), marca, precio,
  stock TOTAL (suma de variantes si has_variants, con color danger si bajo
  low_stock_threshold), estado, featured (toggle inline).
- Filtros: sección, marca, etapa, estado, "Stock bajo" (filtro custom).
- Acción masiva: cambiar estado, asignar etapa.

### OrderResource
- SOLO lectura de los datos de la venta (items, montos, datos Webpay): una orden
  pagada es un documento contable, no se edita. Editables: status (con
  transiciones válidas solamente — usar select con opciones según estado actual),
  notes, tracking.
- Infolist de detalle: datos cliente, dirección (del snapshot JSON), items con
  precios snapshot, desglose de totales, datos Webpay (authorization_code,
  card_last4), timeline de fechas (paid_at, shipped_at...).
- Al pasar a "shipped": modal pidiendo tracking opcional → dispara email
  OrderShipped.
- Badge de navegación: count de pedidos en estado "paid" (los que hay que preparar).
- Filtros: estado, rango de fechas, búsqueda por order_number/email.

### CouponResource
- Code en mayúsculas forzadas. value con sufijo dinámico (% o $) según type.
- Columna times_used/usage_limit. Acción rápida "Desactivar".
- Validación: expires_at > starts_at.

### GuideResource (mini-guías)
- body con MarkdownEditor. Select de etapa. Multiselect de productos recomendados
  (searchable, con preview). Estado draft/published con published_at automático.

### Páginas custom
- ImportarProductos (ver prompt A3): upload + preview + resultado del job.
- Dashboard: widgets de (1) ventas de los últimos 30 días (chart), (2) pedidos
  por preparar (tabla compacta), (3) productos con stock bajo, (4) cupones
  activos. Nada más: un dashboard útil cabe en una pantalla.

## Lo que NO se hace en Filament

- No exponer automatic_discounts en v1 (esquema inactivo).
- No CRUD de regions/communes (son seeds; cambios por migración).
- No editar order_items de órdenes pagadas, bajo ninguna circunstancia.
- No instalar plugins de terceros sin discutirlo (cada plugin es deuda de
  mantenimiento en upgrades de Filament).
