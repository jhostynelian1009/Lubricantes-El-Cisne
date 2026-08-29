<?php

namespace App\Services;

use App\Enums\StockEntryStatus;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockEntryDetail;
use App\Models\User;
use App\ValueObjects\Quantity;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class StockEntryService
{
    public function __construct(
        private StockService $stockService,
        private SequenceService $sequenceService
    ) {}

    public function createDraft(array $data, User $actor): StockEntry
    {
        return DB::transaction(function () use ($data, $actor) {
            $entry = StockEntry::create([
                'supplier_id' => $data['supplier_id'] ?? null,
                'entry_date' => $data['entry_date'],
                'reference' => DataNormalizer::string($data['reference'] ?? null),
                'notes' => DataNormalizer::string($data['notes'] ?? null),
                'status' => StockEntryStatus::DRAFT,
                'created_by' => $actor->id,
            ]);

            $this->syncDetails($entry, $data['details']);

            return $entry;
        });
    }

    public function updateDraft(StockEntry $entry, array $data): void
    {
        if ($entry->status !== StockEntryStatus::DRAFT) {
            throw new ConflictHttpException('Solo se pueden editar entradas en estado borrador.');
        }

        DB::transaction(function () use ($entry, $data) {
            $entry->update([
                'supplier_id' => $data['supplier_id'] ?? null,
                'entry_date' => $data['entry_date'],
                'reference' => DataNormalizer::string($data['reference'] ?? null),
                'notes' => DataNormalizer::string($data['notes'] ?? null),
            ]);

            $this->syncDetails($entry, $data['details']);
        });
    }

    private function syncDetails(StockEntry $entry, array $details): void
    {
        $entry->details()->delete();

        $productIds = array_column($details, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($details as $detail) {
            $product = $products->get($detail['product_id']);
            
            if (!$product || !$product->active) {
                throw new InvalidArgumentException("El producto ID {$detail['product_id']} no existe o está inactivo.");
            }

            $quantity = new Quantity($detail['quantity']);
            if ($quantity->isNegative() || $quantity->isZero()) {
                throw new InvalidArgumentException("La cantidad debe ser mayor a cero.");
            }

            $unitCost = number_format((float)$detail['unit_cost'], 2, '.', '');
            if ((float)$unitCost < 0) {
                throw new InvalidArgumentException("El costo unitario no puede ser negativo.");
            }

            $lineTotal = number_format((float)$quantity->toDecimalString() * (float)$unitCost, 2, '.', '');

            StockEntryDetail::create([
                'stock_entry_id' => $entry->id,
                'product_id' => $product->id,
                'product_sku' => $product->sku,
                'product_name' => $product->name,
                'unit' => $product->unit,
                'quantity' => $quantity->toDecimalString(),
                'unit_cost' => $unitCost,
                'line_total' => $lineTotal,
            ]);
        }
    }

    public function confirm(StockEntry $entry, User $actor): StockEntry
    {
        return DB::transaction(function () use ($entry, $actor) {
            $lockedEntry = StockEntry::where('id', $entry->id)->lockForUpdate()->firstOrFail();

            if ($lockedEntry->status !== StockEntryStatus::DRAFT) {
                throw new ConflictHttpException('La entrada ya ha sido confirmada o no es un borrador.');
            }

            $details = $lockedEntry->details()->orderBy('product_id')->get();
            $productIds = $details->pluck('product_id')->toArray();
            
            // Sort to prevent deadlocks
            sort($productIds);

            $lockedProducts = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($details as $detail) {
                $product = $lockedProducts->get($detail->product_id);
                if (!$product || !$product->active) {
                    throw new InvalidArgumentException("El producto '{$detail->product_name}' está inactivo y no puede recibir stock.");
                }

                $this->stockService->applyMovement(
                    product: $product,
                    type: 'entry',
                    quantityDelta: $detail->quantity,
                    user: $actor,
                    reason: $lockedEntry->reference ? "Entrada #{$lockedEntry->id} Ref: {$lockedEntry->reference}" : "Entrada #{$lockedEntry->id}",
                    unitCost: $detail->unit_cost,
                    reference: $lockedEntry
                );
            }

            $number = $this->sequenceService->getNext('entry', 'E');

            $lockedEntry->update([
                'number' => $number,
                'status' => StockEntryStatus::CONFIRMED,
                'confirmed_by' => $actor->id,
                'confirmed_at' => now(),
            ]);

            return $lockedEntry;
        });
    }
}
