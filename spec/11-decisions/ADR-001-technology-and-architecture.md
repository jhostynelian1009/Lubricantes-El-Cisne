# ADR-001: Tecnología y arquitectura

- Estado: Aceptado.
- Fecha: 2026-08-29.

## Contexto

El proyecto anterior y el entorno de desarrollo del usuario utilizan PHP, Laravel, MySQL/MariaDB, Blade y Bootstrap. El producto es un sistema interno de tamaño pequeño/medio con transacciones fuertes entre ventas e inventario.

## Decisión

Construir un monolito modular en Laravel 12 y PHP 8.2, con MySQL 8 o MariaDB 10.4, Blade, Bootstrap, JavaScript y Vite. Usar MVC para HTTP y una capa de servicios para casos de uso transaccionales. Autenticación por sesión y autorización por roles, permisos, Policies/Gates.

## Consecuencias

- Despliegue y operación más simples que una arquitectura distribuida.
- Las transacciones de ventas e inventario permanecen dentro de una sola base.
- El frontend no es una SPA y no requiere API pública.
- Debe evitarse concentrar lógica en controladores/modelos por la aparente simplicidad del monolito.
- La versión exacta de Laravel/PHP debe fijarse en `composer.json` al crear el repositorio y probarse en el motor de base objetivo.

## Alternativas descartadas

- Microservicios: complejidad sin necesidad del alcance.
- SPA/API separadas: duplican autenticación, despliegue y contratos sin beneficio demostrado.
- Escritura directa de SQL desde controladores: reduce mantenibilidad y seguridad.

