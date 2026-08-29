# K-001 — Inicializar proyecto y entorno

## Objetivo

Crear una base Laravel reproducible y segura para desarrollar el sistema, sin implementar todavía módulos de negocio.

## Dependencias

- Repositorio creado por el usuario.
- PHP 8.2, Composer, Node/npm y MySQL/MariaDB disponibles.
- Leer ADR-001, `spec/10-deployment.md` y `AGENTS.md`.

## Trabajo requerido

1. Verificar si el repositorio está vacío o ya contiene Laravel; no reinicializar ni sobrescribir archivos válidos.
2. Crear/fijar Laravel 12 y versiones compatibles en archivos lock.
3. Configurar zona `America/Guayaquil`, localización española y base de pruebas independiente.
4. Preparar `.env.example` sin secretos y variables de sesión, logs y DB.
5. Crear layout Blade/Bootstrap/Vite con navegación autenticada vacía, componentes de flash y validación.
6. Configurar formato, pruebas y scripts de calidad.
7. Añadir README operativo con instalación, base local/testing, comandos y solución de errores comunes.
8. Crear smoke test de página pública/login según el estado inicial.

## Restricciones

- No crear tablas de negocio.
- No copiar `.env`, bases o datos del sistema anterior.
- No usar SQLite para declarar compatibilidad de reglas que dependerán de MySQL/MariaDB.

## Validación

- Instalación desde clon limpio documentada.
- Migraciones base ejecutan en base vacía.
- Suite inicial y build frontend pasan.
- `.env` y secretos no aparecen en Git.

## Terminado

El proyecto arranca, conecta a bases separadas de desarrollo/pruebas, muestra layout responsive y posee una puerta de calidad reproducible. Registrar versiones exactas encontradas.

