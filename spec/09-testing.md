# Estrategia de pruebas

## Pirámide

- Unitarias: cálculos, estados, permisos efectivos y reglas puras.
- Integración: servicios transaccionales con MariaDB/MySQL de pruebas.
- Feature: rutas, autenticación, autorización, validaciones y respuestas.
- Navegador/manuales: flujo POS, responsive, accesibilidad básica e impresión.

Las reglas de concurrencia y tipos decimales no deben validarse solo con SQLite si producción usa MySQL/MariaDB.

## Casos bloqueantes

| ID de prueba | Requisitos/reglas | Escenario esperado |
|---|---|---|
| T-AUT-001 | RF-AUT-001, RNF-SEC-005 | Login válido entra; inválido no revela existencia |
| T-AUT-002 | RF-AUT-005 | Usuario inactivo no entra ni continúa operando |
| T-PER-001 | RF-USR-005, RNF-SEC-002 | Empleado sin permiso recibe 403 por llamada directa |
| T-PER-002 | RF-USR-006, RB-AUT-004 | No se desactiva último admin ni a sí mismo |
| T-CAT-001 | RF-CAT-006 | Catálogo con historial se desactiva, no se elimina |
| T-INV-001 | RF-INV-001, RB-INV-008 | Crear producto deja stock cero |
| T-INV-002 | RF-INV-002, RNF-REL-002 | Entrada de varias líneas se confirma completa o no cambia nada |
| T-INV-003 | RF-INV-003, RNF-REL-001 | Ajuste que dejaría stock negativo se rechaza |
| T-INV-004 | RB-INV-003 | Último saldo coincide con stock del producto |
| T-SAL-001 | RF-SAL-003, RF-SAL-004 | Venta sin stock no se confirma ni descuenta otras líneas |
| T-SAL-002 | RF-SAL-002, RB-SAL-006 | Total manipulado se ignora y recalcula |
| T-SAL-003 | RF-SAL-005 | Dos ventas concurrentes no repiten número |
| T-SAL-004 | RNF-REL-005 | Confirmación repetida no duplica salida |
| T-SAL-005 | RF-SAL-008 | Anular repone todas las cantidades una vez |
| T-SAL-006 | RF-SAL-009 | Segunda anulación no repone otra vez |
| T-REP-001 | RB-AUD-003 | Anuladas se separan y no se suman como ventas confirmadas |
| T-AUD-001 | RF-AUD-001, RNF-SEC-008 | Acción crítica audita sin secretos |
| T-IDOR-001 | RNF-SEC-002 | Cambiar ID no permite acceder/actuar sin permiso |

## Datos de prueba

- Factories para usuarios, permisos, categorías, productos, entradas, ventas y movimientos.
- Seeder demo idempotente solo para local; nombres ficticios identificables.
- Estados límite: stock 0; igual al mínimo; cantidad 0.001; precio alto válido; 50 líneas de venta.
- Volumen: 10 000 productos y 100 000 movimientos generados, no copiados de producción.

## Pruebas de concurrencia

Como mínimo:

1. Dos ventas intentan consumir el mismo saldo: solo se confirma la cantidad disponible.
2. Dos confirmaciones sobre el mismo borrador: una sola transición.
3. Dos anulaciones sobre la misma venta: un solo reverso.
4. Dos emisiones de número: valores únicos.

Si el runner común no puede ejecutar concurrencia real, documentar una prueba de integración separada contra el motor objetivo; no reemplazarla con una afirmación manual.

## Pruebas manuales de interfaz

- Anchos 360, 768, 1024 y 1440 px.
- Navegación completa solo con teclado en formularios críticos.
- Mensajes de error asociados a campos.
- Estados normal, bajo, agotado y anulado distinguibles sin depender solo del color.
- Vista previa del comprobante en Chrome y Edge; sin navegación ni botones impresos.
- CSV con caracteres españoles y valores decimales legibles.

## Puerta de entrega

1. Base de pruebas recreada desde migraciones.
2. Suite automatizada sin fallos.
3. Formateador/linter sin cambios pendientes.
4. `composer audit` sin vulnerabilidad explotable no resuelta.
5. `npm audit --omit=dev` revisado.
6. `npm run build` exitoso.
7. Prueba manual del flujo crítico de la fase.
8. Matriz de trazabilidad actualizada.

