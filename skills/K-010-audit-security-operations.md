# K-010 — Auditoría, seguridad y operación

## Objetivo

Implementar RF-AUD-001..003 y completar la línea base de seguridad/operación.

## Dependencias

- K-009 terminada.
- Leer `spec/08-security.md` y `spec/10-deployment.md`.

## Trabajo requerido

1. Completar `AuditService` con lista blanca de metadatos y registro after/before seguro cuando aplique.
2. Auditar sesiones, usuarios/permisos, catálogos críticos, entradas, ajustes, ventas, anulaciones y exportaciones.
3. Crear visor paginado solo para admin; sin edición/borrado web.
4. Implementar encabezados, cookies/configuración de producción, páginas de error y saneamiento de logs.
5. Revisar CSRF, rate limits, mass assignment, IDOR y exposición de excepciones.
6. Documentar y automatizar de forma operativa respaldo/restauración fuera de rutas web.
7. Añadir comando de consistencia en modo lectura.

## Pruebas mínimas

- T-AUD-001 y T-IDOR-001.
- Auditoría contiene actor/entidad y no password/token/cookie.
- Empleado no puede consultar auditoría.
- Encabezados presentes.
- `APP_DEBUG=false` no revela stack.
- Comando de consistencia detecta una discrepancia preparada y no la corrige.
- Ensayo documentado de restauración en entorno no productivo.

## Terminado

Las acciones críticas son investigables, la configuración de producción es segura y existe un procedimiento reproducible para proteger/restaurar datos.

