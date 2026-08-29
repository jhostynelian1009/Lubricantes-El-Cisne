<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\ValueObjects\Quantity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
     * Aplica un movimiento de inventario a un producto y actualiza su stock actual de forma atómica.
     */
    public function applyMovement(
        Product $product,
        string $type,
        string|int|float $quantityDelta,
        User $user,
        ?string $reason = null,
        string|float|int|null $unitCost = null,
        ?Model $reference = null
    ): InventoryMovement {
        return DB::transaction(function () use ($product, $type, $quantityDelta, $user, $reason, $unitCost, $reference) {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::where('id', $product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedProduct->active) {
                throw new InvalidArgumentException('No se pueden registrar movimientos en un producto inactivo.');
            }

            $deltaQty = new Quantity($quantityDelta);

            if ($deltaQty->isZero()) {
                throw new InvalidArgumentException('El movimiento de inventario no puede tener un delta de cero.');
            }

            $beforeQty = new Quantity($lockedProduct->current_stock ?? '0.000');

            if ($deltaQty->getThousandths() > 0) {
                $afterQty = $beforeQty->add($deltaQty);
            } else {
                $afterQty = $beforeQty->subtract(Quantity::fromThousandths(abs($deltaQty->getThousandths())));
            }

            if ($afterQty->isNegative()) {
                throw new InvalidArgumentException('La operación resulta en un stock negativo no permitido.');
            }

            $cost = $unitCost !== null ? number_format((float) $unitCost, 2, '.', '') : null;

            $movement = InventoryMovement::create([
                'product_id' => $lockedProduct->id,
                'type' => $type,
                'quantity_delta' => $deltaQty->toDecimalString(),
                'quantity_before' => $beforeQty->toDecimalString(),
                'quantity_after' => $afterQty->toDecimalString(),
                'unit_cost' => $cost,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->getKey(),
                'reason' => DataNormalizer::string($reason),
                'created_by' => $user->id,
            ]);

            $lockedProduct->current_stock = $afterQty->toDecimalString();
            if ($cost !== null && (float) $cost > 0) {
                $lockedProduct->last_cost = $cost;
            }
            $lockedProduct->save();

            $product->current_stock = $lockedProduct->current_stock;
            $product->last_cost = $lockedProduct->last_cost;

            return $movement;
        });
    }
}
