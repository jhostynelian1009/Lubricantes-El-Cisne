<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\User;
use App\ValueObjects\Quantity;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryAdjustmentService
{
    public function __construct(
        private StockService $stockService
    ) {}

    public function increase(Product $product, string $quantity, string $reason, User $actor): void
    {
        $this->processAdjustment($product, $quantity, $reason, 'adjustment_in', $actor);
    }

    public function decrease(Product $product, string $quantity, string $reason, User $actor): void
    {
        $this->processAdjustment($product, "-{$quantity}", $reason, 'adjustment_out', $actor);
    }

    private function processAdjustment(Product $product, string $quantityDelta, string $reason, string $type, User $actor): void
    {
        $cleanReason = DataNormalizer::string($reason);
        if (blank($cleanReason)) {
            throw new InvalidArgumentException('El motivo del ajuste es obligatorio.');
        }

        if (mb_strlen($cleanReason) > 500) {
            throw new InvalidArgumentException('El motivo no puede exceder los 500 caracteres.');
        }

        DB::transaction(function () use ($product, $quantityDelta, $cleanReason, $type, $actor) {
            $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();

            if (!$lockedProduct->active) {
                throw new InvalidArgumentException('No se puede ajustar el stock de un producto inactivo.');
            }

            $delta = new Quantity($quantityDelta);
            if ($delta->isZero()) {
                throw new InvalidArgumentException('La cantidad del ajuste no puede ser cero.');
            }

            $adjustment = InventoryAdjustment::create([
                'product_id' => $lockedProduct->id,
                'type' => $type,
                'quantity' => $delta->toDecimalString(),
                'reason' => $cleanReason,
                'created_by' => $actor->id,
            ]);

            $this->stockService->applyMovement(
                product: $lockedProduct,
                type: $type,
                quantityDelta: $delta->toDecimalString(),
                user: $actor,
                reason: $cleanReason,
                unitCost: null, // Ajustes no modifican last_cost según requerimiento
                reference: $adjustment
            );
        });
    }
}
