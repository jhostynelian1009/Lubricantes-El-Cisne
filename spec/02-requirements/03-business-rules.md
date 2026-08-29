# Reglas de negocio

## Usuarios y autorización

- **RB-AUT-001:** solo existen los roles `admin` y `employee` en el MVP.
- **RB-AUT-002:** el administrador posee acceso total; los permisos individuales restringen a los empleados.
- **RB-AUT-003:** ningún empleado puede otorgarse permisos ni administrar usuarios.
- **RB-AUT-004:** siempre debe existir al menos un administrador activo.
- **RB-AUT-005:** desactivar un usuario bloquea su acceso, pero conserva sus operaciones históricas.
- **RB-AUT-006:** no existe registro público; las cuentas son creadas por el administrador.

## Catálogos y productos

- **RB-CAT-001:** `sku` es obligatorio, único y no cambia después de que el producto tenga movimientos, salvo corrección administrativa auditada.
- **RB-CAT-002:** el código de barras es opcional, pero debe ser único cuando exista.
- **RB-CAT-003:** nombre, SKU y unidad del producto se copian como instantánea en los detalles transaccionales necesarios para conservar el histórico.
- **RB-CAT-004:** costo, precio y stock no pueden ser negativos.
- **RB-CAT-005:** el stock mínimo puede ser cero y solo produce una alerta.
- **RB-CAT-006:** un registro referenciado por una transacción se desactiva; no se elimina físicamente.
- **RB-CAT-007:** un producto inactivo permanece visible en históricos, pero no puede entrar en nuevas operaciones.

## Inventario

- **RB-INV-001:** todo cambio de stock crea exactamente un movimiento por producto afectado.
- **RB-INV-002:** un movimiento confirmado es inmutable; una corrección genera un movimiento compensatorio.
- **RB-INV-003:** el stock actual del producto debe coincidir con el valor posterior de su último movimiento.
- **RB-INV-004:** las cantidades de movimiento usan hasta tres decimales y no pueden ser cero.
- **RB-INV-005:** el sistema nunca permite que el valor posterior sea menor que cero.
- **RB-INV-006:** una entrada incrementa existencias y registra usuario, fecha, origen y detalle.
- **RB-INV-007:** un ajuste requiere tipo incremento/disminución, motivo explícito y permiso específico.
- **RB-INV-008:** el stock inicial se registra como `initial_adjustment`; no se escribe directamente en el producto.
- **RB-INV-009:** la actualización de stock debe bloquear la fila del producto dentro de una transacción para evitar sobreventa concurrente.
- **RB-INV-010:** borrar un borrador que nunca afectó stock no genera reverso; una operación confirmada no se borra.

## Ventas

- **RB-SAL-001:** una venta tiene uno de estos estados: `draft`, `confirmed`, `cancelled`.
- **RB-SAL-002:** solo una venta `draft` puede modificar sus líneas.
- **RB-SAL-003:** una venta requiere al menos una línea para confirmarse.
- **RB-SAL-004:** cada combinación de producto dentro de una venta debe consolidarse en una sola línea.
- **RB-SAL-005:** cantidad y precio unitario deben ser mayores que cero.
- **RB-SAL-006:** el servidor recalcula subtotal y total; ignora totales enviados por el cliente.
- **RB-SAL-007:** confirmar una venta asigna número interno, fija precios, descuenta stock y registra movimientos en una transacción.
- **RB-SAL-008:** el número interno de venta es único y no se reutiliza después de una anulación.
- **RB-SAL-009:** confirmar o anular es idempotente: un segundo intento no cambia stock.
- **RB-SAL-010:** el cliente puede omitirse mientras P-001 y P-007 no exijan identificación obligatoria.
- **RB-SAL-011:** una venta confirmada no se edita. La corrección permitida en el MVP es anularla completamente con un motivo.
- **RB-SAL-012:** anular una venta crea movimientos inversos por todos sus detalles y conserva el documento original.
- **RB-SAL-013:** el comprobante debe indicar claramente que es un documento interno y no una factura electrónica autorizada.

## Dinero y cálculos

- **RB-MON-001:** importes se guardan en `decimal` con dos posiciones; no se usa punto flotante.
- **RB-MON-002:** cantidades se guardan en `decimal` con tres posiciones.
- **RB-MON-003:** cada total de línea es cantidad por precio unitario, redondeado a dos decimales con estrategia única documentada.
- **RB-MON-004:** el total de la venta es la suma de sus líneas. Descuentos e impuestos permanecen deshabilitados hasta resolver P-001 y P-003.
- **RB-MON-005:** el valor de inventario mostrado es referencial: stock actual por último costo registrado; no constituye contabilidad oficial.

## Auditoría y reportes

- **RB-AUD-001:** auditorías registran actor, acción, entidad, identificador, fecha, IP y metadatos permitidos.
- **RB-AUD-002:** no se guardan contraseñas, cookies, tokens ni contenido completo de solicitudes en auditoría.
- **RB-AUD-003:** el reporte de ventas separa confirmadas y anuladas; nunca suma anuladas como ingreso efectivo.
- **RB-AUD-004:** el kardex se ordena por fecha de creación e identificador para garantizar un orden estable.
- **RB-AUD-005:** la hora se guarda coherentemente y se presenta en `America/Guayaquil`.

## Invariantes que bloquean una entrega

Una fase no puede cerrarse si ocurre cualquiera de estos hechos:

1. Stock negativo.
2. Movimiento sin usuario u origen.
3. Diferencia entre stock actual y último saldo del producto.
4. Venta confirmada sin movimientos completos.
5. Empleado capaz de ejecutar una ruta sin permiso.
6. Operación confirmada eliminable desde la aplicación.
7. Comprobante presentado como factura electrónica sin que P-001 haya sido resuelto.
