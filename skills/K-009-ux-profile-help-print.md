# K-009 — Experiencia de usuario, ayuda e impresión

## Objetivo

Completar RF-AYU-001..002 y RNF-USA/RNF-COM visibles para una operación cotidiana clara.

## Dependencias

- K-008 terminada.
- Leer `spec/07-frontend.md`.

## Trabajo requerido

1. Unificar layout, navegación por permisos, títulos, breadcrumbs y mensajes.
2. Revisar todos los formularios: etiquetas, ayudas, errores próximos y conservación segura.
3. Implementar estados vacíos/sin resultados/carga/error.
4. Validar responsive en 360, 768, 1024 y 1440 px.
5. Mejorar teclado, foco, contraste y estados que no dependan solo del color.
6. Completar perfil y ayuda operativa para entradas, ajustes, ventas y anulación.
7. Ajustar CSS de impresión del comprobante en Chrome y Edge.
8. Estandarizar fecha, hora, moneda y cantidades para `es-EC`.

## Validación

- Recorrido manual por rol y permiso.
- Checklist de teclado y responsive.
- Errores vinculados a campos.
- Doble clic no repite operaciones.
- Vista previa de impresión sin navegación/botones y con aviso de documento interno.
- No aparecen controles para acciones inválidas por estado.

## Terminado

Los flujos críticos son claros en móvil/escritorio y la ayuda permite a un usuario autorizado completar una operación sin conocer la implementación.

