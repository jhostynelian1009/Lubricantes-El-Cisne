# K-008 — Panel, reportes y exportación

## Objetivo

Implementar RF-REP-001..005 con consultas correctas, filtrables y escalables.

## Dependencias

- K-007 terminada.
- Datos y estados de inventario/ventas estables.

## Trabajo requerido

1. Construir panel con período explícito, alertas e indicadores enlazables.
2. Implementar reporte de inventario, kardex y ventas.
3. Separar confirmadas y anuladas; no contar anuladas como ingreso.
4. Implementar CSV en streaming/chunks con filtros idénticos a la pantalla.
5. Añadir índices faltantes demostrados por consultas/planes.
6. Aplicar permisos `reports.view` y `reports.export` por separado.
7. Registrar exportaciones sin guardar contenido completo.

## Pruebas mínimas

- T-REP-001.
- Totales con datos límite y anulaciones.
- Saldo anterior y final del kardex.
- Equivalencia entre filtros de pantalla y CSV.
- Paginación sin N+1.
- Usuario con vista pero sin exportación recibe 403 al CSV.

## Terminado

El administrador obtiene información operativa reproducible y los empleados solo reportes autorizados; consultas grandes no cargan todos los registros en memoria.

