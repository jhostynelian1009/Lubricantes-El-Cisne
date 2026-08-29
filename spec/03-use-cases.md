# Actores, permisos y casos de uso

## Actores

### Administrador

Posee acceso completo, administra cuentas y permisos, configura catálogos, supervisa inventario, registra operaciones cuando sea necesario, consulta reportes y auditorías, y autoriza anulaciones o ajustes.

### Empleado

Accede solo a funciones habilitadas por el administrador. El rol por sí mismo no concede acciones críticas; los permisos efectivos se validan en servidor.

## Catálogo de permisos

| Permiso | Administrador | Empleado asignable | Uso |
|---|---:|---:|---|
| `users.manage` | Sí | No | Usuarios, estados y permisos |
| `categories.manage` | Sí | Sí | Categorías |
| `suppliers.manage` | Sí | Sí | Proveedores |
| `customers.manage` | Sí | Sí | Clientes |
| `products.manage` | Sí | Sí | Productos y precios |
| `inventory.view` | Sí | Sí | Stock e historial |
| `inventory.entries.create` | Sí | Sí | Entradas de stock |
| `inventory.adjust` | Sí | Sí | Ajustes con motivo |
| `sales.create` | Sí | Sí | Borradores y confirmación |
| `sales.cancel` | Sí | Sí | Anulación total |
| `reports.view` | Sí | Sí | Panel y reportes |
| `reports.export` | Sí | Sí | Exportación CSV |
| `audit.view` | Sí | No | Auditoría del sistema |

El administrador no puede conceder `users.manage` ni `audit.view` a empleados en el MVP.

## CU-01 Iniciar sesión

- Requisitos: RF-AUT-001, RF-AUT-003, RF-AUT-005.
- Precondición: usuario creado y activo.
- Flujo principal:
  1. El usuario ingresa correo y contraseña.
  2. El sistema limita intentos, valida las credenciales y el estado.
  3. Regenera la sesión y abre el panel permitido.
  4. Registra el evento sin almacenar credenciales.
- Alternativas: credenciales incorrectas, usuario inactivo o demasiados intentos muestran un mensaje seguro y no crean sesión.
- Poscondición: sesión autenticada y permisos disponibles para la siguiente solicitud.

## CU-02 Crear empleado y asignar permisos

- Requisitos: RF-USR-001 a RF-USR-006.
- Actor: administrador.
- Flujo principal:
  1. Ingresa nombre, correo, contraseña temporal y rol empleado.
  2. Selecciona permisos asignables.
  3. El sistema valida unicidad del correo y catálogo de permisos.
  4. Guarda cuenta y permisos en una transacción.
  5. Registra auditoría.
- Excepciones: un empleado llama a la ruta; un permiso reservado es manipulado; el correo ya existe. Todos se rechazan.

## CU-03 Registrar entrada de stock

- Requisitos: RF-INV-002, RF-INV-007, RF-INV-008.
- Permiso: `inventory.entries.create`.
- Precondición: productos activos; proveedor opcional según operación.
- Flujo principal:
  1. El usuario crea un borrador con fecha, proveedor y referencia opcional.
  2. Agrega uno o más productos, cantidad y costo unitario.
  3. El servidor revalida productos y cantidades.
  4. En una transacción, bloquea productos en orden estable, confirma la entrada, crea detalles y movimientos, actualiza stock y último costo.
  5. Registra auditoría y muestra el resumen.
- Excepciones: una línea inválida, producto inactivo o conflicto de concurrencia revierte toda la operación.

## CU-04 Registrar ajuste

- Requisitos: RF-INV-003.
- Permiso: `inventory.adjust`.
- Flujo principal:
  1. El usuario elige producto, incremento/disminución, cantidad y escribe el motivo.
  2. El sistema bloquea el producto y calcula el saldo posterior.
  3. Si el saldo sería negativo, rechaza la operación.
  4. Si es válido, crea movimiento, actualiza stock y audita en una transacción.
- Poscondición: existe una justificación trazable; el movimiento no se puede editar.

## CU-05 Confirmar venta

- Requisitos: RF-SAL-001 a RF-SAL-006.
- Permiso: `sales.create`.
- Flujo principal:
  1. El usuario crea una venta borrador y agrega productos.
  2. El sistema muestra totales provisionales sin cambiar stock.
  3. El usuario confirma conscientemente la operación.
  4. El servidor bloquea todos los productos en orden por ID, valida stock y recalcula valores.
  5. En la misma transacción asigna número, fija detalles, crea movimientos, descuenta stock y marca la venta confirmada.
  6. El usuario puede abrir el comprobante interno e imprimirlo.
- Excepciones: si un producto no tiene stock o cambió de estado/precio antes de confirmar, no se modifica ningún registro y se informa qué debe revisarse.

## CU-06 Anular venta

- Requisitos: RF-SAL-008, RF-SAL-009.
- Permiso: `sales.cancel`.
- Precondición: venta confirmada, no anulada.
- Flujo principal:
  1. El usuario abre la venta e ingresa un motivo obligatorio.
  2. El sistema confirma la consecuencia: reposición total de stock.
  3. En una transacción, bloquea venta y productos, crea movimientos inversos y marca la venta anulada.
  4. Conserva comprobante, detalles y número; registra actor, fecha y motivo.
- Alternativas: una segunda solicitud de anulación se rechaza sin duplicar stock.

## CU-07 Consultar kardex

- Requisitos: RF-INV-005, RF-REP-003.
- Permisos: `inventory.view` y, para el reporte, `reports.view`.
- Flujo: seleccionar producto y período; calcular saldo anterior; listar movimientos en orden estable; verificar que el saldo final coincide con el stock cuando el período llega a la fecha actual.

## CU-08 Exportar reporte

- Requisitos: RF-REP-002 a RF-REP-005.
- Permisos: `reports.view` y `reports.export`.
- Flujo: aplicar filtros, previsualizar resultados, exportar CSV con los mismos filtros y registrar auditoría de la exportación.

