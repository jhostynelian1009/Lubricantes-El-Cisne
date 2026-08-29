# K-002 — Autenticación, usuarios y permisos

## Objetivo

Implementar RF-AUT-001..005 y RF-USR-001..006 con autorización real en servidor.

## Dependencias

- K-001 terminada.
- Leer reglas RB-AUT y `spec/08-security.md`.

## Trabajo requerido

1. Implementar autenticación por sesión, login limitado y logout seguro; no registro público.
2. Extender usuarios con rol `admin|employee`, estado activo y último acceso.
3. Crear catálogo idempotente de permisos y pivote de permisos individuales.
4. Implementar middleware de usuario activo, Gates/Policies y bypass controlado del administrador.
5. Crear CRUD lógico de usuarios; activar/desactivar, restablecer credencial y asignar permisos permitidos.
6. Impedir autodesactivación, desactivar al último admin y asignar permisos reservados a empleados.
7. Implementar perfil básico y cambio de contraseña verificando la actual.
8. Crear comando seguro para el primer administrador.

## Pruebas mínimas

- T-AUT-001, T-AUT-002, T-PER-001 y T-PER-002.
- Anónimo redirigido; empleado recibe 403 por ruta directa.
- Seeder de permisos es idempotente.
- Registro público inexistente.
- Sesión se regenera al entrar y se invalida al salir.

## Terminado

Administrador y empleado pueden autenticarse; el administrador gestiona cuentas/permisos; toda protección se verifica en backend; quedan pruebas de cada permiso reservado.

