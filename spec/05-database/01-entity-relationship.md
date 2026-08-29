# Modelo entidad–relación

```mermaid
erDiagram
    USERS ||--o{ USER_PERMISSIONS : receives
    PERMISSIONS ||--o{ USER_PERMISSIONS : grants
    USERS ||--o{ STOCK_ENTRIES : creates
    USERS ||--o{ SALES : creates
    USERS ||--o{ INVENTORY_MOVEMENTS : records
    USERS ||--o{ AUDIT_LOGS : acts

    CATEGORIES ||--o{ PRODUCTS : classifies
    SUPPLIERS ||--o{ STOCK_ENTRIES : supplies
    CUSTOMERS ||--o{ SALES : buys

    STOCK_ENTRIES ||--|{ STOCK_ENTRY_DETAILS : contains
    PRODUCTS ||--o{ STOCK_ENTRY_DETAILS : receives

    SALES ||--|{ SALE_DETAILS : contains
    PRODUCTS ||--o{ SALE_DETAILS : sold_as

    PRODUCTS ||--o{ INVENTORY_MOVEMENTS : changes
    SALES ||--o{ INVENTORY_MOVEMENTS : originates
    STOCK_ENTRIES ||--o{ INVENTORY_MOVEMENTS : originates

    USERS {
      bigint id PK
      string name
      string email UK
      string role
      boolean active
    }
    PRODUCTS {
      bigint id PK
      bigint category_id FK
      string sku UK
      string barcode UK
      string name
      string unit
      decimal current_stock
      decimal minimum_stock
      decimal last_cost
      decimal sale_price
      boolean active
    }
    STOCK_ENTRIES {
      bigint id PK
      bigint supplier_id FK
      bigint created_by FK
      string number UK
      string status
      date entry_date
    }
    SALES {
      bigint id PK
      bigint customer_id FK
      bigint created_by FK
      string number UK
      string status
      decimal total
    }
    INVENTORY_MOVEMENTS {
      bigint id PK
      bigint product_id FK
      bigint created_by FK
      string type
      decimal quantity_delta
      decimal quantity_before
      decimal quantity_after
      string reference_type
      bigint reference_id
    }
```

## Decisiones de modelado

- `products.current_stock` acelera operaciones y listados; `inventory_movements` es la bitácora que permite demostrar cómo se obtuvo el saldo.
- Ambos se actualizan en la misma transacción. Una verificación de consistencia debe detectar discrepancias.
- Detalles de entrada y venta conservan valores históricos; no dependen de que el nombre o precio actual del producto siga igual.
- Las referencias de `inventory_movements` son polimórficas (`reference_type`, `reference_id`) para enlazar entrada, venta o ajuste sin columnas nulas múltiples.
- `audit_logs` registra acciones de aplicación; no reemplaza los movimientos del inventario.
- Las claves primarias son técnicas. Números visibles de entradas y ventas poseen índices únicos independientes.

## Cardinalidades relevantes

1. Una categoría clasifica muchos productos; cada producto pertenece a una categoría.
2. Un proveedor puede originar muchas entradas; la entrada puede no tener proveedor cuando sea un ajuste inicial documentado.
3. Una entrada confirmada contiene uno o más detalles.
4. Una venta confirmada contiene uno o más detalles y puede asociarse a un cliente.
5. Cada detalle corresponde a un producto.
6. Todo producto puede acumular muchos movimientos, pero solo conserva un stock actual.
7. Un usuario puede generar operaciones y auditorías aunque después sea desactivado.

