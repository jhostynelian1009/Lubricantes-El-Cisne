# K-012 — Despliegue, entrega y evaluación académica

## Objetivo

Entregar una versión reproducible, desplegable y acompañada de evidencia operativa/académica real.

## Dependencias

- K-011 terminada.
- Infraestructura y responsable de producción definidos.
- P-007 y P-008 resueltas; P-001 resuelta si cambió el alcance del documento.

## Trabajo requerido

1. Completar README de instalación, actualización, comandos, roles y solución de problemas.
2. Preparar `.env.example`, configuración de producción y checklist de secretos sin exponer valores.
3. Realizar respaldo, despliegue controlado y smoke tests conforme a `spec/10-deployment.md`.
4. Crear primer administrador y catálogos técnicos; no cargar datos ficticios en producción.
5. Capacitar con la ayuda operativa y registrar aceptación/incidencias.
6. Ejecutar ensayo de restauración y documentar resultado.
7. Preparar instrumentos pretest/postest y hoja de datos reproducible conforme a `spec/13-research-evaluation.md`.
8. Incorporar resultados solo después de medirlos; conservar datos crudos anonimizados y cálculos.
9. Entregar inventario de archivos, versiones, pruebas, URL/entorno si aplica, respaldo, riesgos y mantenimiento.

## Criterios de aceptación

- Producción usa HTTPS, debug desactivado y base respaldada.
- Migraciones/cachés/build ejecutados desde el código entregado.
- Admin puede completar entrada, ajuste, venta, impresión, anulación y reporte según permisos.
- Empleado no accede a administración/auditoría.
- Existe procedimiento de soporte y recuperación.
- Ningún valor académico es supuesto o fabricado.

## Terminado

El sistema queda operativo o listo para el entorno acordado, con entrega técnica verificable y un paquete de evaluación que separa claramente datos reales, cálculos, conclusiones y limitaciones.
