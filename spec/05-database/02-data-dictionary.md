# Diccionario de datos

Los tamaños son línea base y pueden ajustarse mediante ADR antes de crear migraciones. Dinero usa `decimal(14,2)` y cantidades `decimal(14,3)`.

## Identidad

### `users`

| Campo | Tipo | Reglas |
|---|---|---|
| `id` | bigint | PK |
| `name` | varchar(120) | requerido |
| `email` | varchar(190) | requerido, único, normalizado |
| `password` | varchar(255) | hash |
| `role` | varchar(20) | `admin` o `employee` |
| `active` | boolean | default true, indexado |
| `last_login_at` | timestamp nullable | informativo |
| timestamps | timestamp | creación/actualización |

### `permissions`

`id`, `key` varchar(80) único, `name` varchar(120), `assignable_to_employee` boolean y timestamps. El catálogo se crea mediante seeder idempotente.

### `user_permissions`

`user_id` FK, `permission_id` FK y timestamps. PK o UNIQUE compuesto (`user_id`, `permission_id`). Eliminación en cascada solo para el pivote, nunca para el historial de operaciones.

## Catálogos

### `categories`

`id`, `name` varchar(100), `description` nullable, `active` boolean y timestamps. UNIQUE recomendado sobre nombre normalizado entre registros activos.

### `suppliers`

`id`, `name` varchar(160), `identification` varchar(20) nullable, `phone` varchar(30) nullable, `email` varchar(190) nullable, `address` varchar(255) nullable, `active` y timestamps. No se presume validación tributaria mientras P-001 no se resuelva.

### `customers`

Mismos datos básicos que proveedores. Todos los campos excepto `name` pueden ser nulos en el MVP. La identificación es única cuando exista y su regla definitiva depende de P-001.

### `products`

| Campo | Tipo | Reglas |
|---|---|---|
| `id` | bigint | PK |
| `category_id` | bigint | FK requerida, restringir borrado |
| `sku` | varchar(60) | requerido, único |
| `barcode` | varchar(80) nullable | único cuando exista |
| `name` | varchar(180) | requerido, indexado |
| `description` | text nullable | opcional |
| `unit` | varchar(30) | requerido; catálogo inicial configurable |
| `current_stock` | decimal(14,3) | default 0, no negativo por servicio |
| `minimum_stock` | decimal(14,3) | default 0 |
| `last_cost` | decimal(14,2) | default 0 |
| `sale_price` | decimal(14,2) | requerido, mayor que 0 |
| `active` | boolean | default true, indexado |
| timestamps | timestamp | creación/actualización |

Índices: `(active, name)`, `category_id`, `sku`, `barcode`.

## Entradas

### `stock_entries`

`id`; `number` único nullable mientras sea borrador; `supplier_id` nullable; `entry_date`; `reference` nullable; `notes` nullable; `status` (`draft`, `confirmed`); `created_by`; `confirmed_by` nullable; `confirmed_at` nullable; timestamps. Una entrada confirmada no se edita ni elimina.

### `stock_entry_details`

`id`; `stock_entry_id`; `product_id`; `product_sku`; `product_name`; `unit`; `quantity` decimal(14,3); `unit_cost` decimal(14,2); `line_total` decimal(14,2); timestamps. UNIQUE (`stock_entry_id`, `product_id`).

## Ventas

### `sales`

| Campo | Tipo | Reglas |
|---|---|---|
| `id` | bigint | PK |
| `number` | varchar(30) nullable | único; asignado al confirmar |
| `customer_id` | bigint nullable | conservar referencia histórica |
| `status` | varchar(20) | `draft`, `confirmed`, `cancelled` |
| `subtotal` | decimal(14,2) | calculado en servidor |
| `total` | decimal(14,2) | igual a subtotal en MVP |
| `created_by` | bigint | FK usuario |
| `confirmed_at` | timestamp nullable | momento de salida de stock |
| `cancelled_by` | bigint nullable | FK usuario |
| `cancelled_at` | timestamp nullable | momento de reverso |
| `cancellation_reason` | varchar(500) nullable | requerido al anular |
| timestamps | timestamp | creación/actualización |

Índices: `(status, confirmed_at)`, `customer_id`, `created_by`, `number`.

### `sale_details`

`id`; `sale_id`; `product_id`; instantáneas `product_sku`, `product_name`, `unit`; `quantity` decimal(14,3); `unit_price` decimal(14,2); `line_total` decimal(14,2); timestamps. UNIQUE (`sale_id`, `product_id`).

## Inventario y secuencias

### `inventory_movements`

| Campo | Tipo | Reglas |
|---|---|---|
| `id` | bigint | PK monotónica |
| `product_id` | bigint | FK, indexado |
| `type` | varchar(30) | `entry`, `sale`, `sale_reversal`, `adjustment_in`, `adjustment_out`, `initial_adjustment` |
| `quantity_delta` | decimal(14,3) | firmado, distinto de 0 |
| `quantity_before` | decimal(14,3) | mayor o igual a 0 |
| `quantity_after` | decimal(14,3) | mayor o igual a 0 |
| `unit_cost` | decimal(14,2) nullable | costo aplicable |
| `reference_type` | varchar(100) | origen polimórfico |
| `reference_id` | bigint | origen polimórfico |
| `reason` | varchar(500) nullable | obligatorio para ajustes/reversos |
| `created_by` | bigint | usuario responsable |
| `created_at` | timestamp | inmutable |

Índices: `(product_id, created_at, id)`, `(reference_type, reference_id)`, `(type, created_at)`, `created_by`. No requiere `updated_at`.

### `document_sequences`

`key` varchar(50) PK, `year` smallint, `current_value` bigint, `updated_at`. UNIQUE (`key`, `year`) si se usa `id` como PK. Se bloquea al emitir el siguiente número.

## Auditoría

### `audit_logs`

`id`; `actor_id` nullable; `action` varchar(100); `auditable_type`; `auditable_id`; `ip_address` varchar(45) nullable; `user_agent` varchar(500) nullable; `metadata` JSON nullable con lista blanca; `created_at`. Sin `updated_at`. Índices por entidad, actor, acción y fecha.

## Política de claves foráneas

- Restringir borrado de catálogos con historial.
- Permitir `SET NULL` solo donde conservar el hecho tenga sentido y el usuario pueda eliminarse por obligación futura; en el MVP se prefiere desactivar usuarios.
- Cascada solo para detalles de documentos aún borradores y tablas pivote. La aplicación no elimina documentos confirmados.
