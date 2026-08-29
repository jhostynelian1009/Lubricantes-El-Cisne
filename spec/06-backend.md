# Especificación del backend

## Convenciones

- Controladores RESTful y delgados.
- Form Requests por operación mutable.
- Enums PHP para roles, estados y tipos de movimiento.
- Policies por recurso y permisos específicos para acciones sin recurso.
- Servicios de aplicación con métodos orientados a casos de uso.
- Modelos con `fillable` explícito, casts y relaciones tipadas.
- Consultas reutilizables como scopes acotados; sin crear una capa Repository por defecto.

## Servicios y contratos internos

### `StockService`

Único componente que escribe `products.current_stock`.

```php
change(
    Product $lockedProduct,
    StockMovementType $type,
    string $quantityDelta,
    Model $reference,
    User $actor,
    ?string $reason = null,
    ?string $unitCost = null,
): InventoryMovement
```

Precondiciones: producto bloqueado dentro de una transacción; delta distinto de cero; origen persistido. Poscondiciones: saldo no negativo, producto actualizado y movimiento creado.

### `StockEntryService`

- `createDraft(array $data, User $actor): StockEntry`
- `confirm(StockEntry $entry, User $actor): StockEntry`

`confirm` bloquea la entrada, carga detalles en orden por producto, bloquea productos en ese orden, revalida y aplica un `entry` por línea. Una entrada ya confirmada produce conflicto sin nuevos movimientos.

### `InventoryAdjustmentService`

- `increase(Product $product, string $quantity, string $reason, User $actor)`
- `decrease(Product $product, string $quantity, string $reason, User $actor)`

No expone una asignación directa de stock. Si se conoce el conteo físico deseado, el controlador calcula y muestra la diferencia, pero el servicio registra el delta y ambos valores.

### `SaleService`

- `createDraft(?Customer $customer, User $actor): Sale`
- `replaceLines(Sale $draft, array $lines, User $actor): Sale`
- `confirm(Sale $draft, User $actor): Sale`

`confirm` debe:

1. Bloquear venta.
2. Rechazar si no está en `draft`.
3. Bloquear productos por ID ascendente.
4. Validar activos, cantidades y existencias.
5. Recalcular líneas con precio vigente o con la política de precio previamente aceptada.
6. Asignar número con `SequenceService`.
7. Crear un movimiento `sale` por detalle.
8. Guardar totales y marcar `confirmed`.

La política inicial fija el precio vigente al confirmar; si la empresa necesita congelarlo al agregar al carrito, se requiere decisión explícita.

### `SaleCancellationService`

- `cancel(Sale $sale, string $reason, User $actor): Sale`

Bloquea venta y productos. Por cada detalle crea `sale_reversal` positivo. Luego fija estado, actor, fecha y motivo. No elimina ni modifica detalles.

### Consultas y reportes

- `DashboardService::summary(DateRange $range, User $actor)`
- `InventoryReportService::inventory(InventoryFilters $filters)`
- `InventoryReportService::kardex(Product $product, DateRange $range)`
- `SalesReportService::sales(SalesFilters $filters)`
- `CsvExportService::stream(iterable $rows, array $headers, string $filename)`

Los DTO de filtros normalizan fechas, estados y búsqueda. Los reportes respetan políticas y no aceptan nombres de columnas arbitrarios para ordenar.

## Rutas web previstas

| Área | Prefijo/nombre | Acciones |
|---|---|---|
| Autenticación | `login`, `logout`, `profile.*` | sesión y perfil |
| Usuarios | `admin.users.*` | CRUD lógico y permisos |
| Catálogos | `categories.*`, `suppliers.*`, `customers.*`, `products.*` | listar, crear, editar, activar/desactivar |
| Inventario | `inventory.index`, `stock-entries.*`, `inventory.adjustments.*`, `inventory.movements.*` | stock y operaciones |
| Ventas | `sales.*`, `sales.confirm`, `sales.cancel`, `sales.receipt` | POS y comprobante |
| Reportes | `reports.inventory`, `reports.kardex`, `reports.sales`, `reports.*.csv` | consulta/exportación |
| Auditoría | `admin.audit-logs.index` | solo lectura |
| Ayuda | `help.index` | documentación operativa |

No se fijan URIs como contrato público; los nombres de ruta sí deben usarse en vistas y pruebas.

## Validaciones destacadas

- Identificadores de registros deben existir y estar activos cuando la regla lo exija.
- Arrays de detalles: mínimo 1, máximo 50 por venta y 200 por entrada como línea base.
- Duplicados de producto se rechazan o consolidan antes de confirmar; nunca crean dos movimientos ambiguos.
- Fechas operativas no pueden ser futuras sin permiso/regla explícita.
- Motivos se recortan, requieren contenido visible y máximo 500 caracteres.
- Filtros de fechas requieren inicio menor o igual al fin y un rango máximo razonable para exportación interactiva.

## Eventos y tareas en cola

El MVP no necesita colas para modificar stock. Se pueden emitir eventos después del commit para acciones secundarias, pero ningún listener debe cambiar el resultado contable del inventario. Exportaciones muy grandes pueden pasar a cola en una fase futura mediante ADR.

## Comandos operativos

- Crear primer administrador de forma interactiva y segura.
- Verificar consistencia stock vs. último movimiento; solo lectura por defecto.
- Purgar borradores antiguos mediante política configurada, sin tocar confirmados.

El comando de consistencia nunca corrige automáticamente en producción. Debe reportar diferencias para investigación y ajuste autorizado.

