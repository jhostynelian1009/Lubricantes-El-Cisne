# K-011 — Concurrencia, rendimiento y preparación de entrega

## Objetivo

Verificar que el sistema completo cumple invariantes, rendimiento razonable y calidad técnica antes de desplegar.

## Dependencias

- K-010 terminada.
- Todas las decisiones provisionales que afecten el MVP revisadas.

## Trabajo requerido

1. Ejecutar auditoría de cobertura RF/RNF/RB y cerrar huecos, sin ampliar alcance.
2. Crear/ejecutar pruebas de concurrencia para ventas, confirmación, anulación y secuencias.
3. Generar dataset ficticio de volumen y medir RNF-PER con hardware/contexto documentado.
4. Revisar planes de consulta, N+1, índices, paginación y exportación.
5. Probar migración desde base vacía y actualización desde la versión anterior del proyecto nuevo.
6. Ejecutar comando de consistencia y verificar invariantes bloqueantes.
7. Ejecutar análisis de estilo, auditoría de dependencias, suite completa y build.
8. Preparar checklist de aceptación del usuario por roles y flujos.

## Evidencias

- Tiempos p95 con herramienta, datos, hardware y fecha.
- Resultado de pruebas de concurrencia contra MySQL/MariaDB.
- Conteo de pruebas/aserciones y comandos ejecutados.
- Matriz de trazabilidad actualizada.
- Riesgos residuales y decisiones pendientes visibles.

## Terminado

No existe invariante roto ni fallo conocido crítico/alto; la suite completa y la aceptación técnica pasan; toda excepción está documentada y aprobada, no escondida.

