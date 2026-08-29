# Instrucciones para agentes de desarrollo

Estas reglas son obligatorias para cualquier agente que trabaje en este repositorio.

## Fuente de verdad

1. Leer `spec/README.md`, `spec/00-project-context.md` y la habilidad `skills/K-xxx-*.md` activa antes de modificar código.
2. Cumplir los identificadores `RF-*`, `RNF-*` y `RB-*` asociados a la fase.
3. Si una solicitud contradice la especificación, detenerse, explicar la contradicción y registrar una decisión en `spec/11-decisions/` antes de implementar.
4. No ampliar silenciosamente el alcance. Las funciones marcadas como pendientes o fuera del MVP requieren aprobación del usuario.

## Forma de trabajo

- Ejecutar una sola habilidad K por iteración, respetando sus dependencias.
- Inspeccionar primero el estado de Git y conservar cambios ajenos.
- No hacer commit, push, merge, rebase ni crear PR salvo orden expresa.
- Usar migraciones; nunca alterar manualmente una base de datos como sustituto del código versionado.
- Implementar reglas de negocio en servicios y autorización en Policies/Gates, no únicamente en controladores o vistas.
- Usar transacciones y bloqueo de filas para operaciones que cambien stock.
- No borrar movimientos, ventas ni auditorías confirmadas; corregir mediante anulación o reverso trazable.
- No permitir stock negativo.
- No usar datos reales de clientes, credenciales ni respaldos de producción en pruebas o seeders.

## Calidad mínima por fase

- Form Requests o validadores equivalentes para toda entrada.
- Pruebas automatizadas para permisos, reglas críticas y casos de error.
- `php artisan test`, análisis de estilo y compilación del frontend sin errores antes de cerrar la fase.
- Actualizar la matriz de trazabilidad si cambia un requisito.
- Reportar archivos modificados, pruebas ejecutadas, riesgos y asuntos pendientes.

## Restricciones permanentes

- No hay registro público de usuarios.
- El comprobante del MVP es interno; no debe presentarse como factura electrónica autorizada.
- La impresión se realiza desde una vista preparada para navegador; no se integra directamente con el controlador de una impresora.
- El sistema es para una sola empresa, sucursal y bodega en el MVP.
- Los cálculos monetarios usan decimales; nunca `float`.

