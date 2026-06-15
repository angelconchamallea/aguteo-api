# Aguteo Babys — aguteo-api (contexto para agentes)

Estás en el repo BACKEND (Laravel 11 + Filament + Webpay). La fuente de verdad
es `CLAUDE.md` en la raíz: léelo completo antes de escribir código, junto con
docs/MODELO-DATOS.md, docs/API-SPEC.md, docs/FLUJO-WEBPAY.md y
docs/FILAMENT-CONVENCIONES.md.

Reglas que NUNCA se rompen (detalle en CLAUDE.md):
1. Este repo NO genera UI de tienda. Eso vive en aguteo-web (otro repo).
2. API responde snake_case según API-SPEC.md; es contrato con el frontend.
3. Una orden es `paid` solo tras commit() de Webpay exitoso con monto verificado.
4. cost_price y low_stock_threshold jamás se serializan en la API pública.
5. Precios CLP enteros; toda lógica de precios/stock vive aquí.
6. No implementar features EXCLUIDAS de v1.
7. Los docs ganan a tu criterio: si crees que están mal, pregunta primero.