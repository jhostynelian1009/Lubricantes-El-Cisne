# Línea base de seguridad

## Activos a proteger

- Credenciales y sesiones.
- Identidad y permisos de usuarios.
- Stock, movimientos, ventas y auditorías.
- Datos de clientes y proveedores.
- Configuración, secretos y respaldos.

## Matriz de amenazas y controles

| Riesgo | Control obligatorio | Prueba |
|---|---|---|
| Acceso horizontal/vertical | Policies/Gates y permisos en servidor | Feature tests con admin, empleado permitido/no permitido y anónimo |
| Manipulación de precios/totales | Recalcular en servidor | Enviar valores alterados y comprobar total persistido |
| Sobreventa concurrente | Transacción y `lockForUpdate` | Prueba de integración/concurrencia controlada |
| Confirmación duplicada | Bloqueo de documento y transición idempotente | Dos intentos dejan un solo conjunto de movimientos |
| CSRF | Middleware CSRF en rutas web | Solicitud sin token rechazada |
| Fuerza bruta | Rate limit de login | Superar umbral produce limitación temporal |
| XSS | Escape por defecto y lista blanca para contenido | Valores maliciosos se muestran como texto |
| SQL injection | ORM/query builder y ordenamientos permitidos | Entradas no alteran la consulta |
| Sesión robada/fijada | Regenerar ID al autenticar; cookies seguras | Verificación de configuración y prueba de login |
| Exposición de errores | `APP_DEBUG=false`, páginas genéricas | Producción simulada no muestra stack trace |
| Fuga en logs/auditoría | Lista blanca de metadatos | Prueba asegura ausencia de password/token |
| Respaldo expuesto | Fuera del webroot, cifrado/control de acceso | Revisión operativa y ensayo de restauración |

## Autorización

1. Middleware `auth` y `active` en rutas privadas.
2. Policy o Gate por acción.
3. Consulta limitada al dominio permitido.
4. Validación de transición de estado en servicio.

Las cuatro capas cumplen funciones distintas. Ninguna se omite porque otra exista.

## Cuentas

- No hay registro público.
- Contraseña inicial no se envía ni registra en texto plano.
- La creación del primer administrador se hace por comando seguro.
- El administrador puede forzar un cambio de contraseña mediante mecanismo definido en implementación, sin conocer la anterior.
- No se bloquea al último administrador activo.
- La política exacta de longitud se alinea con validadores vigentes del framework; como base, mínimo 12 caracteres para administradores y 10 para empleados.

## Sesión y transporte

- Producción solo por HTTPS.
- `SESSION_SECURE_COOKIE=true`, `HttpOnly`, `SameSite=Lax` o `Strict`.
- Renovar sesión tras autenticación; invalidar y regenerar token CSRF al cerrar.
- Tiempo de inactividad configurable; valor inicial propuesto de 120 minutos, pendiente de validación operativa.

## Datos y privacidad

- Recoger solo datos de contacto necesarios.
- Evitar datos personales reales en documentación, capturas y repositorio.
- Controlar exportaciones por permiso y auditar su generación.
- Restringir acceso a respaldos al personal operativo autorizado.
- No guardar solicitudes completas en auditoría.

## Encabezados

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- Content Security Policy se implementará cuando se haya inventariado Vite, estilos y scripts; no declarar una CSP no probada.

## Gestión de dependencias

- Versiones bloqueadas mediante `composer.lock` y `package-lock.json`.
- Ejecutar `composer audit` y `npm audit --omit=dev` en cada entrega.
- Una vulnerabilidad alta o crítica explotable bloquea producción hasta corregir o documentar mitigación aprobada.

## Respuesta a incidentes

1. Conservar logs y aislar el acceso afectado.
2. Desactivar cuentas o rotar credenciales comprometidas.
3. Identificar operaciones y movimientos relacionados.
4. Restaurar solo después de verificar integridad.
5. Documentar causa, impacto, corrección y prevención.

