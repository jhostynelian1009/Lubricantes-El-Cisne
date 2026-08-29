# Sistema de gestión de inventario — Lubricantes «El Cisne»

Aplicación web para la gestión de productos, existencias, entradas de stock, ventas y reportes trazables de Lubricantes «El Cisne», ubicada en San Lorenzo, Esmeraldas.

## Propósito

Desarrollar una solución web responsive que centralice productos, existencias, entradas de stock, ventas y reportes de Lubricantes «El Cisne». El sistema busca reducir las diferencias entre el inventario físico y el registrado, evitar omisiones o duplicidades y mejorar la trazabilidad de cada operación.

## Línea base tecnológica

- **Framework**: Laravel 12 y PHP 8.2 (arquitectura MVC con capa de servicios).
- **Base de datos**: MySQL 8.0+ o MariaDB 10.4+.
- **Frontend**: Blade, Bootstrap 5, JavaScript y Vite.
- **Localización**: Idioma principal en español (`es`), moneda en USD (`USD`) y zona horaria `America/Guayaquil`.

## Estructura del repositorio

```text
AGENTS.md               Reglas obligatorias para agentes de desarrollo
README.md               Guía de instalación, configuración y operación
spec/                   Fuente de verdad del producto, arquitectura y requisitos
skills/                 Fases ejecutables K-001 a K-012
```

## Requisitos del sistema

- **PHP**: >= 8.2 (extensiones requeridas: PDO, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath).
- **Composer**: >= 2.0.
- **Node.js**: >= 18.0 (Node 22 recomendado) y **npm**.
- **Base de datos**: MySQL 8.0+ o MariaDB 10.4+.

## Instalación y configuración

### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio>
cd Lubricantes-El-Cisne
```

### 2. Instalar dependencias PHP y JavaScript
```bash
composer install
npm install
```

### 3. Configuración del entorno `.env`
Copie el archivo de ejemplo para crear su entorno local y genere la clave de aplicación:
```bash
cp .env.example .env
php artisan key:generate
```

Verifique y ajuste en `.env` las variables de configuración principales:
```env
APP_NAME="Lubricantes El Cisne"
APP_ENV=local
APP_TIMEZONE=America/Guayaquil
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_EC
APP_CURRENCY=USD

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=lubricantes
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Creación de bases de datos (Desarrollo y Testing)
Cree en su motor MySQL/MariaDB dos bases de datos independientes:

```sql
CREATE DATABASE IF NOT EXISTS lubricantes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS lubricantes_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

> **Nota:** Desarrollo y Testing utilizan bases de datos separadas (`lubricantes` y `lubricantes_testing`). El entorno de pruebas está aislado en `phpunit.xml` para no alterar la base de desarrollo.

### 5. Migraciones
Para ejecutar las migraciones base en la base de desarrollo:
```bash
php artisan migrate
```

### 6. Compilación del Frontend (Vite)
Para desarrollo con recarga automática:
```bash
npm run dev
```
Para compilar los assets de producción:
```bash
npm run build
```

### 7. Ejecución del Servidor Local
```bash
php artisan serve
```
Acceda en su navegador a `http://127.0.0.1:8000`.

### 8. Ejecución de Pruebas Automatizadas
Para ejecutar la suite de pruebas automatizadas:
```bash
php artisan test
```

### 9. Comandos de limpieza de caché
En caso de realizar cambios de configuración, rutas o plantillas:
```bash
php artisan optimize:clear
```

## Advertencia de seguridad sobre datos reales

⚠️ **ADVERTENCIA IMPORTANTE**: Está estrictamente prohibido utilizar datos reales de clientes, credenciales personales, respaldos de producción o claves confidenciales en archivos `.env`, seeders, pruebas automatizadas o documentación del proyecto. Utilice exclusivamente datos ficticios de prueba.

## Alcance del MVP

El MVP incluye:
- Autenticación por sesión y autorización mediante roles y permisos.
- Catálogos de productos, categorías, proveedores y clientes.
- Registro de entradas, ajustes de inventario y Kardex.
- Registro de ventas y emisión de comprobante interno imprimible.
- Panel de control y reportes exportables a CSV.

**Exclusiones del MVP**: Facturación electrónica (SRI), contabilidad, cuentas por pagar, múltiples sucursales/bodegas, comercio electrónico y aplicación móvil.
