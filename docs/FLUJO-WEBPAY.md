# FLUJO-WEBPAY.md — Pago con Webpay Plus (Transbank)

SDK: `transbank/transbank-sdk` (Composer, PHP oficial).
Producto: Webpay Plus (transacción normal).
REGLA DE ORO: una orden pasa a `paid` ÚNICAMENTE tras `commit()` exitoso con
`response_code === 0` y monto verificado, dentro de una transacción de DB en el backend.
La redirección del navegador NUNCA confirma un pago.

## Ambientes

| | Integración | Producción |
|---|---|---|
| Credenciales | Públicas de Transbank (en docs oficiales) | Código de comercio propio (contrato) |
| Config | `WEBPAY_ENV=integration` | `WEBPAY_ENV=production` |
| Tarjeta prueba | VISA 4051 8856 0044 6623, CVV 123, cualquier fecha | — |
| Auth banco prueba | RUT 11.111.111-1, clave 123 | — |

Credenciales en `.env`, jamás en código ni commits.

## Flujo paso a paso

```
1. Cliente completa checkout en Next.js
2. POST /orders (Laravel):
   a. Valida datos + stock (lockForUpdate)
   b. Recalcula precios/descuentos/envío desde DB
   c. Crea orden status=pending + items snapshot
   d. Transaction::create(buy_order, session_id, amount, return_url)
   e. Guarda webpay_token y webpay_buy_order en la orden
   f. Responde { url, token } al front
3. Front redirige: POST form a url con token_ws=token
4. Cliente paga (o cancela) en Webpay
5. Transbank redirige al return_url del BACKEND:
   GET|POST /webpay/return
   ├─ Llega token_ws            → flujo normal, ir a paso 6
   ├─ Llega TBK_TOKEN           → cliente ABORTÓ: marcar failed, redirigir a /pago-fallido
   └─ Llega TBK_ORDEN_COMPRA sin token_ws → timeout (10 min): marcar failed
6. Backend hace Transaction::commit(token_ws):
   ├─ response_code === 0 Y amount === orden.total:
   │    DB::transaction:
   │      - orden: status=paid, paid_at=now, authorization_code, card_last4
   │      - descontar stock (variants o product según has_variants)
   │      - incrementar times_used del cupón si aplica
   │    Disparar jobs: email confirmación cliente + notificación admin
   │    Redirect 302 → https://aguteobabys.cl/compra-exitosa?orden=AGB-000123
   └─ cualquier otro caso:
        orden: status=failed
        Redirect 302 → https://aguteobabys.cl/pago-fallido?orden=AGB-000123
```

## Estados de orden

```
pending  → paid | failed | cancelled
paid     → preparing → shipped → delivered
paid     → cancelled (manual, con reversa en Transbank si corresponde)
```
Transiciones solo vía métodos del modelo (Order::markAsPaid(), etc.) que validan
el estado origen. Nunca updates directos de status.

## Casos borde obligatorios

1. **commit() duplicado**: Transbank puede reintentar el return. Si la orden ya está
   paid con ese token, responder redirect a éxito sin re-procesar (idempotencia).
2. **Stock cambió entre create y commit**: el stock se valida y reserva en POST /orders
   con lockForUpdate dentro de la transacción que crea la orden... PERO la reserva dura
   solo el request. Decisión v1: el descuento real ocurre al commit; si en ese instante
   no hay stock (carrera extrema), la orden queda paid igualmente y se gestiona manual
   (notificación admin urgente). Aceptable a este volumen; revisar en v2 con reservas TTL.
3. **Cliente cierra el navegador tras pagar**: el return llega igual desde Transbank
   (server-to-server vía redirección no completada NO existe en Webpay Plus —
   mitigación: job programado que consulta Transaction::status() de órdenes pending
   con más de 15 minutos y concilia).
4. **Montos**: comparar SIEMPRE amount del commit contra orden.total. Si difieren,
   loggear crítico, marcar failed, NO entregar.
5. **Doble clic en "Pagar"**: si la orden pending ya tiene webpay_token vigente (<10 min),
   reutilizar token en vez de crear nueva transacción.

## Emails transaccionales (colas, Redis)

- `OrderPaidCustomer`: confirmación con detalle, número de orden, datos de despacho.
- `OrderPaidAdmin`: aviso de nueva venta.
- `OrderShipped`: cuando admin marca shipped en Filament (incluir tracking si existe).
Tono: cercano y cálido, español chileno, coherente con la marca.

## Logging

Canal dedicado `webpay` (daily). Loggear: create, commit request/response completo
(sin datos de tarjeta — Transbank no los expone igualmente), aborts, timeouts,
descuadres de monto. Retención 90 días.
