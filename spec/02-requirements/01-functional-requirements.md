# Requisitos funcionales

Cada requisito es verificable. Las palabras **debe**, **no debe** y **solo** son obligatorias.

## Autenticación y perfil

| ID | Requisito | Criterio de aceptación principal |
|---|---|---|
| RF-AUT-001 | El sistema debe autenticar usuarios activos mediante correo y contraseña. | Credenciales válidas abren el panel; inválidas muestran un mensaje genérico. |
| RF-AUT-002 | El sistema debe cerrar la sesión invalidando la sesión actual. | Una solicitud posterior a una ruta privada redirige al inicio de sesión. |
| RF-AUT-003 | El sistema no debe ofrecer registro público. | No existe ruta ni formulario público de registro. |
| RF-AUT-004 | El usuario debe poder actualizar su nombre y cambiar su propia contraseña confirmando la contraseña vigente. | La nueva credencial funciona y las validaciones fallidas no cambian datos. |
| RF-AUT-005 | Un usuario inactivo no debe iniciar sesión y una sesión ya abierta debe dejar de autorizarlo. | El middleware rechaza al usuario desactivado. |

## Usuarios, roles y permisos

| ID | Requisito | Criterio de aceptación principal |
|---|---|---|
| RF-USR-001 | El administrador debe crear, consultar y actualizar usuarios. | El usuario aparece en el listado con rol y estado. |
| RF-USR-002 | El administrador debe activar o desactivar empleados sin eliminar su historial. | Las relaciones históricas permanecen y cambia el acceso. |
| RF-USR-003 | El sistema debe manejar los roles `admin` y `employee`. | Cada usuario posee exactamente un rol activo. |
| RF-USR-004 | El administrador debe asignar o retirar permisos individuales a empleados. | La acción se habilita o rechaza en la siguiente solicitud. |
| RF-USR-005 | Solo el administrador puede administrar usuarios y permisos. | Un empleado recibe HTTP 403 aun si llama directamente a la ruta. |
| RF-USR-006 | El sistema debe impedir desactivar al último administrador activo y la autodesactivación. | La operación se rechaza con explicación y no modifica la base. |

## Catálogos

| ID | Requisito | Criterio de aceptación principal |
|---|---|---|
| RF-CAT-001 | Los usuarios autorizados deben gestionar categorías. | Se puede crear, editar, consultar y desactivar una categoría. |
| RF-CAT-002 | Los usuarios autorizados deben gestionar proveedores. | Se conservan nombre, identificación/contacto opcionales y estado. |
| RF-CAT-003 | Los usuarios autorizados deben gestionar clientes. | Se conservan nombre, identificación/contacto opcionales y estado. |
| RF-CAT-004 | Los usuarios autorizados deben gestionar productos. | El producto conserva SKU, código de barras opcional, categoría, unidad, costo, precio, mínimo y estado. |
| RF-CAT-005 | El sistema debe buscar y filtrar productos por nombre, SKU, código de barras, categoría y estado. | La búsqueda combina filtros y pagina resultados. |
| RF-CAT-006 | Los registros de catálogo utilizados en transacciones no deben eliminarse físicamente. | La interfaz ofrece desactivación y conserva el historial. |

## Inventario

| ID | Requisito | Criterio de aceptación principal |
|---|---|---|
| RF-INV-001 | Todo stock inicial debe registrarse como una entrada o ajuste inicial, no al crear el producto. | Crear producto deja stock en cero; el cambio posterior genera movimiento. |
| RF-INV-002 | Un usuario autorizado debe registrar una entrada con uno o más productos, proveedor y referencia opcionales. | Confirmar crea encabezado, detalles, movimientos y actualiza stock atómicamente. |
| RF-INV-003 | Un usuario autorizado debe registrar ajustes de incremento o disminución con motivo obligatorio. | El ajuste genera movimiento trazable y nunca deja stock negativo. |
| RF-INV-004 | El sistema debe mostrar stock actual y estado normal/bajo/agotado por producto. | La clasificación coincide con stock y mínimo configurado. |
| RF-INV-005 | El sistema debe mostrar el historial de movimientos filtrable por producto, tipo, fecha, usuario y referencia. | Cada fila identifica valores anterior, variación, posterior y origen. |
| RF-INV-006 | El sistema debe alertar sobre productos activos con stock igual o inferior al mínimo. | El panel y el listado muestran la alerta sin bloquear operaciones. |
| RF-INV-007 | Un producto inactivo no debe agregarse a nuevas entradas, ajustes o ventas. | La validación de servidor rechaza la operación. |
| RF-INV-008 | Las operaciones que afectan existencias deben usar una transacción de base de datos. | Un fallo en cualquier detalle revierte encabezado, detalles, movimientos y stock. |

## Ventas y comprobantes

| ID | Requisito | Criterio de aceptación principal |
|---|---|---|
| RF-SAL-001 | Un usuario autorizado debe crear una venta en borrador con cliente opcional. | El borrador no modifica existencias. |
| RF-SAL-002 | El usuario debe agregar productos activos, cantidades y precios válidos a la venta. | Los totales se recalculan en servidor y no confían en valores manipulados del navegador. |
| RF-SAL-003 | El sistema debe validar todas las existencias al confirmar la venta. | Si falta stock en un producto, no se confirma ni se descuenta ningún detalle. |
| RF-SAL-004 | Confirmar la venta debe descontar stock, crear movimientos y fijar los valores del detalle dentro de una sola transacción. | La venta queda confirmada una sola vez y cada detalle tiene su movimiento. |
| RF-SAL-005 | El sistema debe asignar un número interno único y secuencial a cada venta confirmada. | No se repite bajo solicitudes concurrentes. |
| RF-SAL-006 | El sistema debe generar una vista de comprobante interno apta para impresión desde navegador. | Contiene número, fecha, detalle, totales y aviso de documento interno. |
| RF-SAL-007 | Los usuarios autorizados deben consultar ventas por número, fecha, cliente, usuario y estado. | Los filtros son combinables y paginados. |
| RF-SAL-008 | Solo un usuario autorizado debe anular una venta confirmada indicando un motivo. | Se conserva la venta, cambia a anulada y el stock se revierte atómicamente. |
| RF-SAL-009 | Una venta anulada no debe poder anularse ni confirmarse nuevamente. | Los intentos repetidos son rechazados sin nuevos movimientos. |

## Panel, reportes y auditoría

| ID | Requisito | Criterio de aceptación principal |
|---|---|---|
| RF-REP-001 | El panel debe mostrar productos activos, valor referencial del stock, productos bajos/agotados y ventas del período seleccionado. | Cada indicador enlaza con su detalle filtrado. |
| RF-REP-002 | El sistema debe emitir un reporte de inventario actual. | Incluye producto, categoría, unidad, costo, precio, stock y estado. |
| RF-REP-003 | El sistema debe emitir un kardex simplificado por producto y período. | Muestra saldo inicial, movimientos ordenados y saldo resultante. |
| RF-REP-004 | El sistema debe emitir un reporte de ventas por período, estado y usuario. | Totales y filas consideran anulaciones de forma explícita. |
| RF-REP-005 | Los reportes deben poder exportarse a CSV respetando los filtros activos. | El archivo abre con encabezados legibles y datos equivalentes a la consulta. |
| RF-AUD-001 | El sistema debe auditar inicio de sesión, cambios de usuarios/permisos, entradas, ajustes, confirmaciones y anulaciones. | El registro identifica actor, acción, fecha, entidad y metadatos no sensibles. |
| RF-AUD-002 | Solo el administrador debe consultar el registro de auditoría. | Empleados reciben HTTP 403. |
| RF-AUD-003 | Los registros de auditoría no deben modificarse o borrarse desde la interfaz. | No existen rutas web para editar o eliminar auditorías. |

## Ayuda operativa

| ID | Requisito | Criterio de aceptación principal |
|---|---|---|
| RF-AYU-001 | El sistema debe incluir una ayuda breve para entradas, ajustes, ventas y anulación. | Un usuario puede abrir instrucciones desde el menú autenticado. |
| RF-AYU-002 | Los formularios críticos deben explicar campos y consecuencias irreversibles antes de confirmar. | Entradas, ajustes, ventas y anulaciones muestran confirmación contextual. |

