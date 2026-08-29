# ADR-002: Stock materializado y movimientos inmutables

- Estado: Aceptado.
- Fecha: 2026-08-29.

## Contexto

El problema principal es la diferencia entre stock físico y registrado y la falta de trazabilidad. Calcular el stock sumando todos los movimientos en cada pantalla ofrece trazabilidad, pero puede penalizar listados; guardar solo un número actual es rápido, pero no explica su origen.

## Decisión

Guardar `products.current_stock` y, por cada variación, crear un `inventory_movement` inmutable con saldo anterior, delta, saldo posterior, origen y actor. Actualizar ambos dentro de una transacción con bloqueo de fila. Las correcciones se realizan mediante movimientos compensatorios.

## Consecuencias

- Listados rápidos y kardex trazable.
- Existe redundancia controlada que exige pruebas y un comando de consistencia.
- Ningún controlador, importador o seeder puede cambiar `current_stock` por fuera de `StockService`.
- La concurrencia debe probarse con el motor real.

## Alternativas descartadas

- Solo stock actual: no resuelve la trazabilidad.
- Solo suma de movimientos: complica consultas y reportes de gran volumen.
- Editar movimientos: destruye evidencia y dificulta auditoría.

