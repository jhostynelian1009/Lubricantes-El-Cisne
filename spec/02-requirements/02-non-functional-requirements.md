# Requisitos no funcionales

## Seguridad y privacidad

| ID | Requisito medible |
|---|---|
| RNF-SEC-001 | Todas las rutas funcionales, excepto inicio de sesión, deben exigir autenticación. |
| RNF-SEC-002 | Toda acción protegida debe validarse en servidor mediante Policy, Gate o middleware; ocultar un botón no cuenta como autorización. |
| RNF-SEC-003 | Las contraseñas deben almacenarse con el hasher configurado por Laravel y nunca registrarse en logs. |
| RNF-SEC-004 | Formularios web mutables deben tener protección CSRF y validación de servidor. |
| RNF-SEC-005 | El inicio de sesión debe limitar intentos y usar un mensaje que no confirme si el correo existe. |
| RNF-SEC-006 | Las cookies de sesión deben ser `HttpOnly`, `SameSite=Lax` o más estricto y `Secure` en producción HTTPS. |
| RNF-SEC-007 | Deben configurarse encabezados `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` y `Permissions-Policy`. |
| RNF-SEC-008 | Exportaciones, errores y logs no deben exponer contraseñas, tokens, secretos ni datos internos innecesarios. |
| RNF-SEC-009 | Seeders y pruebas no deben contener datos personales reales de la empresa. |

## Integridad y confiabilidad

| ID | Requisito medible |
|---|---|
| RNF-REL-001 | Ninguna operación confirmada puede producir stock negativo. |
| RNF-REL-002 | Entradas, ajustes, confirmaciones y anulaciones deben ser atómicas y seguras frente a concurrencia. |
| RNF-REL-003 | Totales monetarios deben calcularse con decimales y redondeo explícito a dos posiciones. |
| RNF-REL-004 | Las cantidades deben manejar hasta tres decimales y rechazarse si son cero o negativas. |
| RNF-REL-005 | Una solicitud repetida no debe confirmar ni anular dos veces la misma venta. |
| RNF-REL-006 | Toda migración debe poder ejecutarse desde una base vacía en desarrollo y pruebas. |
| RNF-REL-007 | El despliegue debe contar con respaldo previo y procedimiento documentado de restauración. |

## Rendimiento

| ID | Requisito medible |
|---|---|
| RNF-PER-001 | Listados deben estar paginados y no cargar colecciones completas en memoria. |
| RNF-PER-002 | Con 10 000 productos y 100 000 movimientos de prueba, el 95 % de búsquedas y listados debe responder en menos de 2 segundos en el entorno de aceptación acordado. |
| RNF-PER-003 | La confirmación de una venta de hasta 50 líneas debe completar en menos de 3 segundos en el entorno de aceptación acordado. |
| RNF-PER-004 | Consultas críticas deben evitar N+1 y usar índices documentados. |
| RNF-PER-005 | Exportaciones grandes deben procesarse por cursor o chunks; no deben agotar memoria por cargar todos los registros. |

Los tiempos son metas técnicas propuestas. El hardware y el conjunto de datos del ensayo deben registrarse al medirlos.

## Usabilidad y accesibilidad

| ID | Requisito medible |
|---|---|
| RNF-USA-001 | La interfaz debe funcionar desde 360 px de ancho y en escritorio sin desplazamiento horizontal global. |
| RNF-USA-002 | Toda validación debe mostrar el error junto al campo y conservar entradas seguras. |
| RNF-USA-003 | Acciones destructivas o irreversibles deben pedir confirmación y explicar la consecuencia. |
| RNF-USA-004 | Controles deben poder operarse con teclado y tener etiqueta accesible. |
| RNF-USA-005 | Estados no deben comunicarse exclusivamente mediante color. |
| RNF-USA-006 | La interfaz, comprobantes y exportaciones deben estar en español. |

## Compatibilidad y operación

| ID | Requisito medible |
|---|---|
| RNF-COM-001 | La aplicación debe funcionar en las dos últimas versiones estables de Chrome, Edge y Firefox disponibles durante la aceptación. |
| RNF-COM-002 | La impresión debe usar CSS de impresión y el diálogo estándar del navegador. |
| RNF-COM-003 | El sistema debe operar con zona horaria `America/Guayaquil`, formato local `es-EC` y moneda USD. |
| RNF-COM-004 | Secretos y credenciales deben provenir del entorno; `.env` no se versiona. |

## Mantenibilidad y pruebas

| ID | Requisito medible |
|---|---|
| RNF-MAN-001 | La lógica de stock y ventas debe residir en servicios de dominio, no duplicarse entre controladores. |
| RNF-MAN-002 | Las reglas críticas deben tener pruebas unitarias o de integración; permisos y rutas, pruebas feature. |
| RNF-MAN-003 | Antes de una entrega deben pasar pruebas, formateo, auditoría de dependencias y build de frontend. |
| RNF-MAN-004 | Los identificadores de requisitos deben mantenerse en nombres o documentación de pruebas para permitir trazabilidad. |
| RNF-MAN-005 | El código nuevo debe evitar dependencias no justificadas; toda dependencia estructural requiere ADR. |

