<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'current_stock',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'last_cost' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $term = trim($search);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%");
        });
    }

    public function scopeCategory(Builder $query, $categoryId): Builder
    {
        if (blank($categoryId)) {
            return $query;
        }

        return $query->where('category_id', $categoryId);
    }

    public function scopeStockStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return match ($status) {
            'out_of_stock' => $query->where('current_stock', '<=', 0),
            'low_stock' => $query->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'minimum_stock'),
            'normal' => $query->whereColumn('current_stock', '>', 'minimum_stock'),
            default => $query,
        };
    }

    public function getStockStatusAttribute(): string
    {
        $stock = (float) $this->current_stock;
        $min = (float) $this->minimum_stock;

        if ($stock <= 0) {
            return 'out_of_stock';
        }

        if ($stock <= $min) {
            return 'low_stock';
        }

        return 'normal';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Agotado',
            'low_stock' => 'Bajo stock',
            'normal' => 'Stock normal',
            default => 'Desconocido',
        };
    }

    public function getStockStatusBadgeClassAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'bg-danger',
            'low_stock' => 'bg-warning text-dark',
            'normal' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function hasMovements(): bool
    {
        return $this->inventoryMovements()->exists();
    }
}
