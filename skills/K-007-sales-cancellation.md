# K-007 — Anulación y reversión de ventas

## Objetivo

Implementar RF-SAL-008..009 y preservar evidencia completa de la operación original.

## Dependencias

- K-006 terminada.
- Permiso `sales.cancel`.

## Trabajo requerido

1. Implementar `SaleCancellationService` independiente de la confirmación.
2. Exigir venta confirmada, motivo y permiso.
3. Bloquear venta y productos; reponer cantidades con movimientos `sale_reversal`.
4. Guardar actor, fecha y motivo; conservar número, líneas, totales y comprobante original.
5. Mostrar estado/anulación en historial, detalle, comprobante y reportes futuros.
6. Rechazar edición, segunda anulación o reconfirmación.

## Pruebas mínimas

- T-SAL-005 y T-SAL-006.
- Empleado sin permiso recibe 403.
- Fallo en un reverso revierte toda la anulación.
- Reversos enlazan la venta y coinciden con cada detalle.
- Solicitudes simultáneas generan un solo conjunto de reversos.

## Exclusiones

- No implementar devolución parcial ni cambio de producto. Si se requiere, abrir ADR y épica separada.

## Terminado

La anulación total es atómica, idempotente y trazable; ninguna venta confirmada se borra o edita.

