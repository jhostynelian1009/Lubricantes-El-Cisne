# Alcance y glosario

## Incluido en el MVP

- Inicio de sesión, cierre de sesión y perfil.
- Administración de usuarios activos/inactivos.
- Roles administrador y empleado.
- Permisos individuales otorgados por el administrador.
- Gestión de categorías, proveedores, clientes y productos.
- Registro de entradas de stock con proveedor y referencia opcionales.
- Ajustes manuales autorizados con motivo obligatorio.
- Consulta del stock actual y del historial de movimientos.
- Alertas visuales de stock mínimo.
- Punto de venta con detalle de productos y validación de disponibilidad.
- Anulación controlada de ventas con reversión de stock.
- Comprobante interno imprimible.
- Panel con indicadores y reportes operativos.
- Registro de auditoría para acciones críticas.
- Respaldo operativo de base de datos fuera del flujo web.

## Fuera del MVP

- Facturación electrónica, firma digital o integración con el SRI.
- Contabilidad, libro diario, cuentas por cobrar o cuentas por pagar.
- Gestión formal de órdenes de compra y recepción parcial.
- Devoluciones de clientes o a proveedores como flujos independientes.
- Control por lotes, números de serie o fechas de caducidad.
- Varias empresas, sucursales, bodegas o transferencias entre bodegas.
- Comercio electrónico, pagos en línea, aplicación móvil o modo sin conexión.
- Integración directa con hardware fiscal, lector de código o controlador de impresora.
- Pronóstico con inteligencia artificial.

Lo excluido no debe implementarse de forma improvisada. Su incorporación requiere un ADR, requisitos, modelo de datos y pruebas nuevas.

## Glosario

| Término | Definición operativa |
|---|---|
| Stock actual | Cantidad disponible registrada para un producto después del último movimiento confirmado. |
| Stock mínimo | Umbral configurado que activa una alerta; no bloquea por sí mismo una venta. |
| Entrada | Incremento de stock documentado; puede vincularse a un proveedor y una referencia. |
| Ajuste | Corrección extraordinaria de stock con autorización y motivo. |
| Movimiento | Registro inmutable de variación de stock, con valores anterior, cambio y posterior. |
| Venta borrador | Venta aún editable que no afecta el stock. |
| Venta confirmada | Venta finalizada que descuenta stock dentro de una transacción. |
| Venta anulada | Venta confirmada invalidada; conserva sus datos y genera movimientos inversos. |
| Comprobante interno | Documento operativo de la empresa; no equivale a factura electrónica autorizada. |
| Permiso | Capacidad específica asignada por el administrador a un empleado. |
| Trazabilidad | Posibilidad de identificar qué ocurrió, cuándo, quién lo hizo y qué registro originó el cambio. |

## Decisiones pendientes de negocio

| Código | Pregunta | Impacto |
|---|---|---|
| P-001 | ¿Se necesita facturación electrónica oficial o solo comprobante interno? | Integración SRI, firma, impuestos, numeración y cumplimiento. |
| P-002 | ¿Qué métodos de pago deben registrarse? | POS, reportes y cierre de caja. |
| P-003 | ¿Se permiten descuentos y quién los autoriza? | Totales, permisos y auditoría. |
| P-004 | ¿Se venden cantidades fraccionarias? | Unidad, precisión y validaciones. |
| P-005 | ¿Deben gestionarse devoluciones? | Reversos parciales, stock y documentos. |
| P-006 | ¿Existe más de una bodega o sucursal? | Modelo de existencias y permisos. |
| P-007 | ¿Qué datos exactos debe contener el comprobante? | Campos empresariales, diseño e impresión. |
| P-008 | ¿Cuánto tiempo deben conservarse operaciones y respaldos? | Política de retención y capacidad. |

