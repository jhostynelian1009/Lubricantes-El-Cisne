# Especificación de interfaz

## Principios

- Priorizar velocidad operativa, claridad y prevención de errores.
- Mantener navegación y términos consistentes con el negocio.
- Mostrar solo acciones permitidas, sin depender de ello para la seguridad.
- Diferenciar borrador, confirmado y anulado con texto, icono y color.
- Conservar filtros en paginación y exportación.

## Estructura de navegación

1. Inicio.
2. Inventario: productos, existencias, entradas, ajustes y movimientos.
3. Ventas: nueva venta e historial.
4. Catálogos: categorías, proveedores y clientes.
5. Reportes: inventario, kardex y ventas.
6. Administración: usuarios, permisos y auditoría; solo administrador.
7. Ayuda y perfil.

Los elementos sin permiso se ocultan. Una URL directa continúa protegida en servidor.

## Pantallas

### Inicio

- Indicadores: productos activos, stock bajo, agotados, valor referencial y ventas confirmadas del período.
- Accesos rápidos condicionados por permiso.
- Lista breve de alertas con enlace al inventario filtrado.
- El período visible debe estar rotulado; no mostrar cifras sin rango temporal.

### Listados

- Búsqueda principal, filtros, estado, paginación y contador de resultados.
- Tabla adaptable: en pantallas pequeñas, priorizar datos esenciales y acciones en menú.
- Estados vacíos que expliquen cómo crear el primer registro si el usuario tiene permiso.
- Estado de cero resultados distinto al estado sin datos.

### Producto

- Secciones: identificación, clasificación, precio/costo, stock mínimo y estado.
- El stock actual es solo lectura; se modifica desde entradas o ajustes.
- En detalle: resumen y últimos movimientos.

### Entrada de stock

- Encabezado con fecha, proveedor, referencia y notas.
- Buscador de productos y tabla editable de cantidad/costo.
- Totales informativos.
- Guardar borrador y confirmar como acciones separadas.
- Modal de confirmación: después de confirmar no se edita; el stock aumentará.

### Ajuste

- Producto, saldo actual, dirección, cantidad, saldo resultante y motivo.
- Advertencia visible para disminuciones.
- Rechazo inmediato y en servidor cuando el saldo sería negativo.

### Punto de venta

- Búsqueda por nombre, SKU o código de barras escrito/pegado.
- Carrito con stock disponible, cantidad, precio y subtotal.
- Resumen fijo en escritorio y accesible en móvil.
- Confirmación explícita. Durante el envío se bloquea doble clic.
- Un error de concurrencia devuelve el borrador con productos que deben revisarse.

### Detalle y comprobante

- Detalle: número, estado, cliente, vendedor, fechas, líneas y total.
- Si se anuló: motivo, actor y fecha visibles.
- Comprobante: diseño monocromático legible, sin menú, botones u otros elementos en impresión.
- Texto obligatorio: `Comprobante interno — no constituye factura electrónica autorizada`.

### Reportes

- Filtros antes de ejecutar consultas costosas.
- Resumen de filtros aplicado arriba del resultado y en la exportación.
- Totalizaciones con explicación de anuladas.
- CSV disponible solo con permiso.

## Componentes comunes

- `flash-message`: éxito, advertencia y error.
- `validation-errors`: resumen accesible y errores por campo.
- `status-badge`: texto + icono + color.
- `confirm-dialog`: nombre de acción y consecuencia.
- `empty-state`, `filter-bar`, `pagination-summary` y `money`/`quantity` formatters.

## Formatos

- Dinero visible: `$ 1.234,56` según `es-EC`, almacenado sin símbolos.
- Cantidad: hasta tres decimales, ocultando ceros innecesarios.
- Fecha: `dd/mm/aaaa`; fecha y hora `dd/mm/aaaa HH:mm` en hora de Ecuador.
- Identificaciones, teléfonos y códigos se tratan como texto, no como números aritméticos.

## Impresión

`@media print` debe ocultar navegación, filtros, botones, mensajes y URL auxiliar; definir márgenes y evitar cortes dentro de filas cuando el navegador lo permita. La aceptación se realiza con vista previa de impresión en Chrome y Edge, no con comunicación directa a una impresora específica.

