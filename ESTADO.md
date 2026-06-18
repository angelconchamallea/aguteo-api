# ESTADO.md — Aguteo Babys

> Documento vivo. Refleja DÓNDE está el proyecto hoy: qué se construyó, qué sigue,
> y las decisiones tomadas. Se actualiza al cerrar cada hito (ver PROMPTS.md 9.1).
> NO es especificación (eso vive en docs/); es la foto del avance.
> Última actualización: 2026-06-17 (W3)

---

## Fuente de verdad — jerarquía de documentos

Cuando dos documentos se contradigan, gana el de mayor rango:

1. **MODELO-DATOS.md / API-SPEC.md / FLUJO-WEBPAY.md** — especificación técnica dura.
   Si el código contradice estos, el código está mal.
2. **CLAUDE.md (por repo)** — reglas y convenciones operativas.
3. **DESIGN-SYSTEM.md / FILAMENT-CONVENCIONES.md** — estándares de UI/admin.
4. **ESTADO.md (este)** — estado y decisiones; no define cómo se implementa, sí qué se decidió.

Si crees que un documento de rango superior está equivocado, NO lo contradigas en
silencio: dilo y espera que un humano lo corrija primero.

---

## Anti-patrones críticos — leer antes de codear

Lista de choque consolidada. El detalle vive en cada CLAUDE.md (secciones 3 y 6).

**NUNCA en este proyecto:**

- Serializar `cost_price` o `low_stock_threshold` en cualquier respuesta pública de la API.
- Marcar una orden como `paid` sin `commit()` exitoso de Webpay con monto verificado.
- Confiar en la redirección del navegador como confirmación de pago.
- Calcular precios, descuentos o totales en el frontend: solo se muestra lo que la API responde.
- Descontar stock fuera de una `DB::transaction()` con `lockForUpdate()`.
- Recalcular `order_items` desde el producto actual: son snapshots inmutables.
- Usar colores hex de DISEÑO (botones, fondos, textos) fuera de `tailwind.config`, o
  usar `rose` fuera de CTAs de compra. EXCEPCIÓN: el color de categoría/etapa viene de
  la API como hex (es dato, no diseño) y se aplica con `style`. Ver DESIGN-SYSTEM.md regla 1-3.
- Usar `localStorage` / `sessionStorage` para carrito o checkout (Zustand + cookie).
- Implementar features EXCLUIDAS de v1 (ver más abajo) aunque parezcan fáciles.
- Que el backend genere UI de tienda, o que el frontend toque la DB o Transbank directo.
- Commitear credenciales productivas de Webpay o el `.env` de producción.

---

## Estado actual

**Fase: A1–A4 y W1–W3 completos ✅ — siguiente: A5 (Órdenes + Webpay) → W4 (Carrito + Checkout).**

**Backend (aguteo-api):**

A1 verificado con `sail artisan migrate:fresh --seed` exitoso (24 migraciones,
7 seeders, 339 comunas, 33 categorías, 12 productos de muestra).

A2 verificado con Filament 3 instalado, panel admin en `/admin`, todos los
Resources creados y navegación agrupada.

A3 verificado con página "Importar productos" operativa: sube `.xlsx`, valida
cabeceras, muestra preview de 5 filas por hoja, importa en lotes de 50 vía job
en cola (phpoffice/phpspreadsheet), reporta creados/actualizados/errores por fila.

A4 verificado con 10 endpoints bajo `/api/v1/`: categorías (árbol + detalle con
breadcrumb), etapas, marcas, productos (filtros: categoría rama descendiente, etapa,
marca, precio, búsqueda; sorts; paginación), detalle de producto (variantes con precio
resuelto, imágenes, tags, guías relacionadas), guías, tarifas de envío y validación
de cupones. CORS configurado vía `FRONTEND_URL`. Tests PHPUnit: 20 casos.

**Frontend (aguteo-web):**

W1 verificado con proyecto Next.js 15 creado, Tailwind con todos los tokens del
design system, tipos TypeScript espejo de API-SPEC.md, cliente API con ISR, store
Zustand (sin persistencia), componentes UI base (Button, Badge, ProductCard,
StageCard, SectionPill, CategoryCard, PackCard, PriceTag, Skeleton).

W2 verificado con home completa: Topbar, SiteHeader (logo + buscador + carrito
con badge), HeroSection, StageGrid, CategoryBar + CategoryGrid, FeaturedProducts,
OfferBanner, PacksSection, BrandStrip, MiniGuideBlock, FounderBlock,
GuaranteeStrip, SiteFooter (logo original + columnas Tienda/Ayuda/Síguenos +
sello Webpay, alineado verticalmente al centro). Favicon y logo SVG integrados.

W3 verificado con `/tienda` (filtros por categoría/etapa/marca/precio, sidebar
desktop + bottom sheet mobile, chips de filtro activos, sort, paginación con gaps,
estado vacío) y `/producto/[slug]` (galería con miniaturas, selector de variantes,
aviso de stock bajo, botón desktop + barra fija mobile, breadcrumb, guías
relacionadas, sección "Te puede servir"). ISR 300s en ambas rutas.

Subido a GitHub: https://github.com/angelconchamallea/aguteo-web

**Próximo hito backend: A5** (Órdenes + Webpay) → luego **W4** (Carrito + Checkout). Ver PROMPTS-CONSTRUCCION.md.

### Documentos listos (16 archivos)

| Documento | Repo destino | Estado |
|---|---|---|
| CLAUDE.md (api) + AGENTS.md | aguteo-api | ✅ |
| CLAUDE.md (web) + AGENTS.md | aguteo-web | ✅ |
| MODELO-DATOS.md | aguteo-api/docs | ✅ |
| API-SPEC.md | ambos /docs | ✅ |
| FLUJO-WEBPAY.md | aguteo-api/docs | ✅ |
| FILAMENT-CONVENCIONES.md | aguteo-api/docs | ✅ |
| DOCKER-README.md | aguteo-api/docs | ✅ |
| DESIGN-SYSTEM.md | aguteo-web/docs | ✅ |
| Dockerfile, entrypoint.sh, nginx.conf, docker-compose.prod.yml | aguteo-api | ✅ |
| PROMPTS.md + PROMPTS-CONSTRUCCION.md | recursos | ✅ |
| Plantilla Excel de productos | recursos | ✅ |
| ESTADO.md (este) | raíz / recursos | ✅ |

### Hitos de construcción

**Backend (aguteo-api):**
- [x] A1 — Fundación: Laravel + migraciones + modelos + seeders ✅ 2026-06-14
- [x] A2 — Filament admin (resources + relation managers) ✅ 2026-06-14
- [x] A3 — Importador Excel de productos ✅ 2026-06-16
- [x] A4 — API pública de catálogo ✅ 2026-06-16
- [ ] A5 — Órdenes + Webpay (zona crítica) ← SIGUIENTE
- [ ] A6 — Producción (revisar Docker)

**Frontend (aguteo-web):**
- [x] W1 — Fundación + design system + tipos TS ✅ 2026-06-16
- [x] W2 — Home ✅ 2026-06-17
- [x] W3 — Catálogo y detalle ✅ 2026-06-17
- [ ] W4 — Carrito y checkout (después de A5) ← SIGUIENTE (frontend)
- [ ] W5 — Mini-guías y pulido

### Tareas externas en paralelo (tuyas, no de los agentes)
- [ ] Verificar WSL2 (`wsl --list --verbose`) y montar estructura de carpetas
- [ ] Afiliación Webpay Plus en transbank.cl (trámite lento — iniciar ya)
- [ ] Comprar dominio (verificar aguteobabys.cl en nic.cl)
- [ ] Crear cuenta Vultr Santiago
- [ ] Llenar plantilla Excel con los 100+ productos
- [ ] Fotografías de productos con fondo consistente

---

## Decisiones tomadas (registro)

| Fecha | Decisión | Razón |
|---|---|---|
| 2026-06 | Arquitectura híbrida: Laravel API + Next.js front | Backend en terreno conocido; aprender React donde más importa (cliente) |
| 2026-06 | Filament 3 para el admin | CRUD de 100+ productos en horas, no semanas; desacoplado del front |
| 2026-06 | Webpay Plus, no Mercado Pago | Confianza del comprador chileno; comisiones más bajas; SDK PHP oficial |
| 2026-06 | Vultr Santiago, no Clouding.io | Latencia: servidores junto a los clientes (Chile) vs Barcelona |
| 2026-06 | PostgreSQL 16, no MySQL | Mejor manejo de JSON (jsonb); dirección de la industria; costo cero de cambio |
| 2026-06 | Docker: Sail local + Compose producción | Paridad de ambientes; sin costo de Forge; aprendizaje |
| 2026-06 | Checkout como invitado en v1 | Convierte más; ahorra semanas de auth |
| 2026-06 | Categorías como árbol jerárquico (Materialized Path) | Modelo WooCommerce: 7 raíces + subcategorías, profundidad ilimitada a futuro |
| 2026-06 | Packs como producto simple en v1 | Bundle real (descuento por componente) se posterga a v1.1 |
| 2026-06-14 | Docker: postgres:17 + redis:7 (Debian) en vez de Alpine | Las imágenes Alpine fallan en esta máquina (exec format error por arquitectura); Debian funciona igual que la imagen Sail base |
| 2026-06-14 | Filament instalado con --ignore-platform-req=ext-intl | La imagen php83-composer de Sail no tiene ext-intl; en el container de runtime (PHP 8.5) sí está disponible |
| 2026-06-15 | color_token en Category y AgeStage cambia de token string a hex | El frontend aún no está construido; hex es más simple (style directo) que mapear tokens en el front. Sincronizado en DESIGN-SYSTEM (regla 1-3), API-SPEC, MODELO-DATOS y anti-patrones el 2026-06-16: hex permitido SOLO para color de categoría/etapa (es dato); resto de UI sigue en tokens; rose sigue exclusivo de CTAs |
| 2026-06-15 | product_variants.size cambiado de enum a VARCHAR(50) | El enum de PostgreSQL rechazaba tallas fuera de la lista predefinida (ej. XG, Único); el admin necesita libertad total de tallas |
| 2026-06-16 | phpoffice/phpspreadsheet en vez de maatwebsite/excel para A3 | maatwebsite/excel requiere phpspreadsheet <8.5; el proyecto corre PHP 8.5.7; phpspreadsheet 5.x soporta PHP 8.5 nativamente |
| 2026-06-16 | Entrypoint de compose.yaml como lista YAML en vez de scalar > | El scalar plegado `>` no preservaba las comillas al pasar al shell de Docker, haciendo que sh interpretara los paths como comandos |
| 2026-06-16 | CORS via config/cors.php + FRONTEND_URL env var | API pública bajo api/* necesita CORS para que Next.js (dominio distinto) pueda llamarla; /admin usa sesión y no lo necesita; origen configurable por entorno sin tocar código |
| 2026-06-16 | rating=null y reviews_count=0 en toda respuesta de producto (v1) | Sistema de reseñas excluido de v1; los campos se incluyen en el contrato desde ya para que el frontend (ProductCard) pueda diseñar la UI sin cambiar el contrato en v2 |
| 2026-06-16 | Next.js creado manualmente (sin create-next-app) | create-next-app rechazaba el directorio por los .md previos; la creación manual dio control total sobre las versiones y configuración |
| 2026-06-17 | Migración Next.js 14 → 15.5.19 | CVEs en 14.x (DoS, cache poisoning, request smuggling); 15.x los parchea. Breaking changes aplicados: params y searchParams en pages son ahora Promise y requieren await |

---

## Alcance por versión

**v1 (en construcción):** catálogo con filtros (categoría/rama, etapa, marca, precio),
variantes por talla, carrito invitado, cupones, Webpay, emails transaccionales,
admin Filament, importador Excel, mini-guías por etapa.

**EXCLUIDO de v1:** reviews, wishlist, login/registro obligatorio, descuentos
automáticos activos (esquema listo, lógica inactiva), bundle real de packs,
multi-moneda, multi-idioma, app móvil, boleta electrónica SII.

**v1.1 (próximo):** descuentos automáticos activos, bundle real de packs,
revalidación on-demand afinada.

**v2 (futuro):** reviews, cuentas de cliente con historial, wishlist,
contenido de Instagram integrado, boleta electrónica SII.

---

*Actualizar este documento al cerrar cada hito: marcar el check, mover "SIGUIENTE",
agregar decisiones nuevas a la tabla, y refrescar la fecha.*