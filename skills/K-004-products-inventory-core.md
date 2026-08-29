# K-004 — Productos y núcleo del inventario

## Objetivo

Crear productos, saldo actual y libro de movimientos sin habilitar todavía entradas o ventas completas.

## Dependencias

- K-003 terminada.
- Confirmar ADR-004 antes de avanzar si la empresa posee más de una bodega.
- Leer ADR-002 y diccionario de datos.

## Trabajo requerido

1. Crear productos con SKU único, código de barras opcional único, categoría, unidad, costo, precio, mínimo y estado.
2. Fijar stock inicial en cero; stock actual solo lectura en formularios.
3. Crear enums y tabla inmutable `inventory_movements`.
4. Implementar `StockService` con decimal, bloqueo de fila, saldo anterior/delta/posterior y origen obligatorio.
5. Implementar clasificación normal/bajo/agotado y listado filtrable.
6. Implementar ajuste inicial controlado para carga de apertura, reutilizando el servicio y permiso de ajuste.
7. Añadir detalle de producto con últimos movimientos.

## Pruebas mínimas

- T-CAT-001 y T-INV-001.
- SKU/código únicos, incluso ante solicitudes inválidas.
- Producto inactivo no entra en operación nueva.
- El servicio rechaza cero y saldo negativo.
- `current_stock` coincide con el movimiento creado.
- Ningún endpoint asigna stock directamente.

## Terminado

El catálogo de productos y el mecanismo único de cambio de stock funcionan con decimales, trazabilidad y autorización; no existe modificación arbitraria del saldo.

