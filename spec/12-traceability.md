# Matriz de trazabilidad

Esta matriz agrupa requisitos cuando comparten implementación y prueba. Cada prueba real debe conservar su identificador `T-*` y puede citar varios requisitos.

| Requisitos | Reglas/RNF relacionados | Fase | Evidencia mínima |
|---|---|---|---|
| RF-AUT-001..005 | RB-AUT-005..006; RNF-SEC-001,003,005,006 | K-002 | T-AUT-001, T-AUT-002 y pruebas de sesión/perfil |
| RF-USR-001..006 | RB-AUT-001..005; RNF-SEC-002 | K-002 | T-PER-001, T-PER-002 y CRUD autorizado |
| RF-CAT-001..003,006 | RB-CAT-006 | K-003 | CRUD, desactivación e histórico |
| RF-CAT-004..005 | RB-CAT-001..007 | K-004 | T-CAT-001, unicidad y filtros |
| RF-INV-001,004,006,007 | RB-INV-003,005,008 | K-004 | T-INV-001, clasificación de stock |
| RF-INV-002,008 | RB-INV-001..003,006,009 | K-005 | T-INV-002 y prueba de rollback |
| RF-INV-003 | RB-INV-002,004,005,007 | K-005 | T-INV-003, T-INV-004 |
| RF-INV-005 | RB-AUD-004 | K-005 | filtros e historial estable |
| RF-SAL-001..007 | RB-SAL-001..010; RNF-REL-002..005 | K-006 | T-SAL-001..004 y prueba de impresión |
| RF-SAL-008..009 | RB-SAL-011..013 | K-007 | T-SAL-005, T-SAL-006 |
| RF-REP-001..005 | RB-MON-005, RB-AUD-003..004 | K-008 | T-REP-001, filtros y equivalencia CSV |
| RF-AUD-001..003 | RB-AUD-001..002; RNF-SEC-008 | K-010 | T-AUD-001 y autorización |
| RF-AYU-001..002 | RNF-USA-002..005 | K-009 | revisión de ayuda y formularios |
| RNF-PER-001..005 | índices y consultas definidas | K-008, K-011 | ensayo de volumen documentado |
| RNF-USA-001..006 | especificación frontend | K-009, K-011 | matriz manual responsive/accesibilidad |
| RNF-COM-001..004 | despliegue e impresión | K-009, K-012 | smoke tests y vista previa |
| RNF-MAN-001..005 | AGENTS.md y arquitectura | Todas | suite, lint, build y reporte de fase |
| RNF-REL-006..007 | migraciones y operación | K-011, K-012 | base vacía, respaldo/restauración |

## Cobertura de reglas bloqueantes

Antes de entrega, K-011 debe producir una lista automatizada o reporte que confirme:

- no existe stock negativo;
- cada producto con movimientos coincide con su último saldo;
- cada venta confirmada tiene salida por cada línea;
- cada venta anulada tiene un único reverso completo;
- permisos no se eluden mediante rutas directas;
- documentos confirmados no poseen rutas de eliminación.

