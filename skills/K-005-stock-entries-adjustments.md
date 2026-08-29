# K-005 — Entradas, ajustes y kardex base

## Objetivo

Implementar RF-INV-002, RF-INV-003, RF-INV-005 y RF-INV-008.

## Dependencias

- K-004 terminada.
- Permisos de entradas, ajustes y consulta definidos.

## Trabajo requerido

1. Crear encabezado/detalles de entradas y sus estados.
2. Implementar borrador y confirmación atómica mediante `StockEntryService`.
3. Consolidar o rechazar productos repetidos; máximo inicial de 200 líneas.
4. Actualizar último costo al confirmar, sin retroactuar movimientos previos.
5. Implementar ajustes de incremento/disminución con motivo y vista previa del saldo.
6. Implementar historial filtrable y kardex base por producto/período.
7. Evitar edición o eliminación de entradas confirmadas y movimientos.

## Pruebas mínimas

- T-INV-002, T-INV-003 y T-INV-004.
- Fallo de una línea revierte la entrada completa.
- Doble confirmación no incrementa otra vez.
- Disminución concurrente no deja stock negativo.
- Kardex ordenado por fecha e ID, con saldo inicial correcto.

## Terminado

Entradas y ajustes modifican stock únicamente por movimientos atómicos; el historial permite reconstruir el saldo y toda acción crítica conserva actor/origen.

