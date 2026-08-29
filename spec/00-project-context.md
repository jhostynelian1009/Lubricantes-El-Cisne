# Contexto del proyecto

## Identificación

- Organización: Lubricantes «El Cisne».
- Ubicación: San Lorenzo, Esmeraldas, Ecuador.
- Producto: sistema web de gestión de inventario y ventas.
- Usuarios iniciales: administrador y empleados autorizados.
- Enfoque académico: desarrollo de una solución y evaluación pretest–postest de su efecto sobre el control del inventario.

## Problema

El inventario y las ventas se gestionan mediante registros manuales y hojas de cálculo. El proceso favorece omisiones, duplicidades y diferencias entre el stock físico y el registrado; dificulta detectar existencias críticas; demora la consulta y preparación de reportes; y ofrece trazabilidad insuficiente sobre quién realizó cada operación.

## Pregunta orientadora

¿Cómo contribuye una aplicación web de gestión de inventario a mejorar el control de productos y la trazabilidad de las operaciones en Lubricantes «El Cisne»?

## Objetivo general

Desarrollar una aplicación web para mejorar la gestión del inventario de Lubricantes «El Cisne» mediante el registro centralizado de productos, entradas, salidas por venta, existencias y reportes trazables.

## Objetivos específicos

1. Analizar el proceso actual de registro y control del inventario e identificar errores, tiempos y necesidades de información.
2. Diseñar la arquitectura, la base de datos, las reglas de negocio y la interfaz del sistema conforme a los requerimientos de la empresa.
3. Implementar el sistema web en Laravel con módulos de usuarios, catálogos, inventario, ventas y reportes.
4. Evaluar la solución comparando indicadores pretest y postest sin fabricar ni completar datos que no hayan sido medidos.

## Interesados

| Interesado | Necesidad principal | Participación |
|---|---|---|
| Propietario/administrador | Control, supervisión, permisos y reportes | Aprueba reglas y alcance |
| Empleado | Registrar y consultar operaciones permitidas | Valida facilidad de uso |
| Investigador/desarrollador | Construir y evaluar la solución | Mantiene especificación y evidencia |
| Tutor/docente | Coherencia metodológica y técnica | Revisa entregables académicos |

## Criterios de éxito del producto

- Todo cambio de stock queda respaldado por un movimiento inmutable y un usuario responsable.
- Las ventas confirmadas descuentan stock de forma atómica y nunca permiten existencias negativas.
- El administrador puede conocer stock actual, productos en nivel mínimo, movimientos y ventas por período.
- Los empleados solo ejecutan acciones para las cuales poseen permiso.
- El comprobante interno puede imprimirse desde un navegador común.
- Las mediciones académicas usan instrumentos y datos reales, diferenciando claramente resultado medido de meta propuesta.

## Supuestos que deben validarse con la empresa

- El MVP operará en una sola sucursal y una sola bodega.
- El sistema no emitirá facturas electrónicas autorizadas por el SRI.
- Las entradas de stock no constituyen un módulo contable de compras o cuentas por pagar.
- Las cantidades pueden requerir hasta tres decimales; cada producto declara su unidad.
- El cliente puede ser opcional en una venta interna.

