# Despliegue y operación

## Entornos

| Entorno | Finalidad | Datos |
|---|---|---|
| Local | Desarrollo | Ficticios, seeders permitidos |
| Testing | Pruebas automatizadas | Base independiente y desechable |
| Staging | Aceptación | Ficticios o anonimizados |
| Producción | Operación real | Acceso restringido y respaldado |

Nunca ejecutar seeders demo ni `migrate:fresh` en producción.

## Variables de entorno

Como mínimo: nombre/entorno/URL de aplicación, clave, debug, zona horaria, conexión DB, driver de sesión/cache/log y configuración de correo si luego se habilita. `.env.example` contiene claves sin secretos; `.env` no se versiona.

## Requisitos del servidor

- Versión de PHP compatible con Laravel 12 y extensiones requeridas.
- MySQL 8 o MariaDB 10.4 validado por la suite.
- Servidor web con raíz pública apuntando a `public/`.
- HTTPS válido.
- Node solo para construir assets; producción puede servir el build generado.
- Cron del framework cada minuto si se programan tareas.
- Directorios de almacenamiento con permisos mínimos necesarios.

La infraestructura definitiva —hosting compartido, VPS o red local— permanece pendiente. No se promete compatibilidad con un proveedor no evaluado.

## Procedimiento de despliegue

1. Verificar commit/tag y notas de cambio.
2. Activar mantenimiento si el cambio lo requiere.
3. Generar y verificar respaldo de base de datos.
4. Instalar dependencias bloqueadas sin paquetes de desarrollo.
5. Compilar assets desde el estado exacto desplegado.
6. Ejecutar migraciones no destructivas con `--force`.
7. Limpiar y reconstruir cachés de configuración, rutas y vistas.
8. Reiniciar workers si existen.
9. Ejecutar smoke tests: login, panel, producto, venta de prueba controlada o verificación no mutable.
10. Desactivar mantenimiento y vigilar logs.

## Reversión

- El código puede volver a la versión anterior solo si sus migraciones son compatibles.
- Una migración destructiva requiere plan específico, ventana y respaldo probado.
- No ejecutar `migrate:rollback` automáticamente sobre datos reales.
- Si la nueva versión ya escribió datos con un formato incompatible, restaurar o migrar hacia adelante según el plan aprobado.

## Respaldos

Línea base propuesta, pendiente de confirmar con P-008:

- Respaldo diario de base de datos.
- Retención diaria 7 días y semanal 4 semanas.
- Copia fuera del servidor principal y acceso restringido.
- Ensayo de restauración mensual o antes de una entrega académica importante.
- Registrar fecha, tamaño, resultado y responsable; nunca guardar credenciales dentro del respaldo.

El sistema web no ejecuta comandos de shell de respaldo desde una ruta. Se usa la infraestructura o una tarea operativa controlada.

## Monitoreo mínimo

- Disponibilidad HTTP y expiración HTTPS.
- Errores 5xx y excepciones.
- Espacio en disco y crecimiento de base de datos/logs.
- Éxito y antigüedad del último respaldo.
- Intentos de login limitados y cambios críticos de permisos.
- Comando periódico de consistencia de stock en modo lectura.

## Primer arranque

1. Configurar entorno y generar clave.
2. Ejecutar migraciones.
3. Cargar únicamente catálogos técnicos idempotentes, como permisos.
4. Crear el primer administrador mediante comando interactivo.
5. Configurar datos empresariales del comprobante cuando P-007 esté resuelto.
6. Registrar productos; cargar stock inicial mediante ajuste inicial trazable.
