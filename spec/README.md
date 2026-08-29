# Índice de especificaciones

Este directorio es la fuente de verdad del sistema.

| Área | Documento | Contenido |
|---|---|---|
| Contexto | `00-project-context.md` | Problema, objetivo, interesados y supuestos |
| Alcance | `01-scope-and-glossary.md` | Incluido, excluido, vocabulario y pendientes |
| Requisitos | `02-requirements/01-functional-requirements.md` | Requisitos funcionales verificables |
| Requisitos | `02-requirements/02-non-functional-requirements.md` | Calidad, seguridad y rendimiento |
| Reglas | `02-requirements/03-business-rules.md` | Reglas invariantes del negocio |
| Casos de uso | `03-use-cases.md` | Actores, permisos y flujos críticos |
| Arquitectura | `04-architecture.md` | Componentes, responsabilidades y límites |
| Datos | `05-database/01-entity-relationship.md` | Modelo conceptual y relaciones |
| Datos | `05-database/02-data-dictionary.md` | Tablas, campos, índices y restricciones |
| Backend | `06-backend.md` | Servicios, transacciones y contratos internos |
| Frontend | `07-frontend.md` | Navegación, pantallas y estados de interfaz |
| Seguridad | `08-security.md` | Controles y amenazas principales |
| Pruebas | `09-testing.md` | Estrategia y casos mínimos |
| Operación | `10-deployment.md` | Entornos, configuración y respaldos |
| Decisiones | `11-decisions/` | ADR aceptados y pendientes |
| Trazabilidad | `12-traceability.md` | Requisito → fase → prueba |
| Evaluación | `13-research-evaluation.md` | Plan pretest–postest para el artículo |

## Orden de precedencia

1. Decisiones ADR aceptadas.
2. Reglas de negocio.
3. Requisitos funcionales y no funcionales.
4. Arquitectura y diseño de datos.
5. Habilidades de implementación.

Las habilidades no pueden cambiar requisitos por sí mismas. Cuando exista una ambigüedad, debe registrarse como pregunta pendiente en el ADR correspondiente.

