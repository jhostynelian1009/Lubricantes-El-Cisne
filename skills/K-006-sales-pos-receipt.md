# K-006 — Ventas, POS y comprobante interno

## Objetivo

Implementar RF-SAL-001..007 sin afirmar facturación electrónica.

## Dependencias

- K-005 terminada.
- ADR-003 vigente y P-007 resuelta al menos para datos visuales mínimos.

## Trabajo requerido

1. Crear ventas y detalles con estados `draft`, `confirmed`, `cancelled`.
2. Implementar POS con búsqueda, carrito, cliente opcional y totales provisionales.
3. Recalcular precios/totales en servidor y consolidar productos repetidos.
4. Implementar `SequenceService` seguro y `SaleService::confirm` con bloqueo ordenado.
5. Descontar stock y crear un movimiento por detalle en la misma transacción.
6. Proteger doble envío en interfaz y backend.
7. Implementar historial/detalle y comprobante interno imprimible con aviso obligatorio.

## Pruebas mínimas

- T-SAL-001, T-SAL-002, T-SAL-003 y T-SAL-004.
- Borrador no cambia stock.
- Venta con 50 líneas confirma completa o revierte todo.
- Manipulación de cantidad/precio/total se rechaza o recalcula.
- Numeración única en concurrencia.
- Comprobante solo accesible a usuario autorizado y no contiene controles al imprimir.

## Exclusiones

- No SRI, impuestos, descuentos, cierre de caja ni integración directa con impresora.

## Terminado

Una venta válida se confirma una sola vez, descuenta existencias de forma íntegra y genera un comprobante inequívocamente interno.

