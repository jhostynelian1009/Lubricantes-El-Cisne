# Habilidades de implementación

Cada archivo K es una unidad de trabajo ejecutable por un agente. Debe aplicarse en orden; una fase pendiente no autoriza anticipar código de fases posteriores.

| Fase | Resultado |
|---|---|
| K-001 | Proyecto Laravel y entorno reproducible |
| K-002 | Autenticación, usuarios, roles y permisos |
| K-003 | Categorías, proveedores y clientes |
| K-004 | Productos y núcleo de inventario |
| K-005 | Entradas, ajustes y movimientos |
| K-006 | Ventas, descuento de stock y comprobante |
| K-007 | Anulaciones y reversos |
| K-008 | Panel, reportes, kardex y CSV |
| K-009 | Perfil, ayuda, responsive, accesibilidad e impresión |
| K-010 | Auditoría, seguridad y operación |
| K-011 | Concurrencia, rendimiento y endurecimiento final |
| K-012 | Entrega, despliegue y evidencia de evaluación |

## Protocolo común

1. Leer `AGENTS.md`, la especificación enlazada y el estado real del repositorio.
2. Ejecutar preflight: rama, cambios existentes, versiones y pruebas de línea base.
3. Implementar solo el alcance de la fase.
4. Añadir o ajustar pruebas automatizadas.
5. Ejecutar la puerta de calidad aplicable.
6. Actualizar documentación/trazabilidad únicamente si la implementación aprobada lo requiere.
7. Entregar un reporte con cambios, pruebas, riesgos y decisiones pendientes.

No cerrar una fase con pruebas rojas, migraciones irreproducibles, cambios ajenos sobrescritos o asuntos bloqueantes ocultos.

