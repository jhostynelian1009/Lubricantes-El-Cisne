# K-003 — Categorías, proveedores y clientes

## Objetivo

Implementar catálogos base reutilizables y trazables conforme a RF-CAT-001..003 y RF-CAT-006.

## Dependencias

- K-002 terminada.
- Permisos `categories.manage`, `suppliers.manage` y `customers.manage` disponibles.

## Trabajo requerido

1. Crear migraciones, modelos, factories y Policies de categorías, proveedores y clientes.
2. Implementar listados paginados con búsqueda y estado.
3. Implementar alta, edición, detalle y activación/desactivación.
4. Normalizar espacios/correo y validar longitudes; tratar identificaciones y teléfonos como texto.
5. Evitar borrado físico cuando exista historial; como base, ofrecer desactivación en la UI.
6. Registrar cambios relevantes mediante una interfaz de auditoría preparada, aunque el visor completo llegue en K-010.

## Pruebas mínimas

- CRUD exitoso y validaciones.
- Empleado con/sin cada permiso.
- Filtros conservados al paginar.
- Registros inactivos visibles en histórico y ausentes de selectores de operaciones nuevas.
- Datos con tildes y caracteres españoles.

## Exclusiones

- No validar obligaciones tributarias ni consultar servicios externos.
- No implementar compras, ventas o stock.

## Terminado

Los tres catálogos son usables, responsive, autorizados en servidor y no destruyen referencias históricas.

