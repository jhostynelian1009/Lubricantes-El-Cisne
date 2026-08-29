<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\ValueObjects\Quantity;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SaleService
{
    public function __construct(
        protected SequenceService $sequenceService,
        protected StockService $stockService
    ) {}

    public function createDraft(?Customer $customer, User $actor): Sale
    {
        if ($customer && !$customer->active) {
            throw new InvalidArgumentException('El cliente seleccionado está inactivo.');
        }

        return Sale::create([
            'number' => null,
            'customer_id' => $customer?->id,
            'status' => SaleStatus::DRAFT,
            'subtotal' => '0.00',
            'total' => '0.00',
            'created_by' => $actor->id,
        ]);
    }

    public function replaceLines(Sale $draft, array $rawLines, User $actor, ?Customer $customer = null): Sale
    {
        if (!$draft->isDraft()) {
            throw new ConflictHttpException('Solo se pueden modificar ventas en estado borrador.');
        }

        if ($customer !== null) {
            if ($customer && !$customer->active) {
                throw new InvalidArgumentException('El cliente seleccionado está inactivo.');
            }
            $draft->customer_id = $customer?->id;
        }

        if (empty($rawLines)) {
            DB::transaction(function () use ($draft) {
                $draft->details()->delete();
                $draft->subtotal = '0.00';
                $draft->total = '0.00';
                $draft->save();
            });
            return $draft->fresh(['details', 'customer']);
        }

        $consolidated = [];
        foreach ($rawLines as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $rawQty = (string) ($line['quantity'] ?? '0');

            if ($productId <= 0) {
                throw new InvalidArgumentException('Se requiere un producto válido.');
            }

            if (is_numeric($rawQty) && (str_contains(strtolower((string) $rawQty), 'e'))) {
                throw new InvalidArgumentException('Notación científica no permitida en cantidades.');
            }

            $qtyVo = new Quantity($rawQty);
            if ($qtyVo->isZero() || $qtyVo->isNegative()) {
                throw new InvalidArgumentException('La cantidad debe ser mayor que cero.');
            }

            if (!isset($consolidated[$productId])) {
                $consolidated[$productId] = $qtyVo;
            } else {
                $consolidated[$productId] = $consolidated[$productId]->add($qtyVo);
            }
        }

        if (count($consolidated) > 50) {
            throw new InvalidArgumentException('Una venta no puede tener más de 50 productos distintos.');
        }

        $productIds = array_keys($consolidated);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        if (count($products) !== count($productIds)) {
            throw new InvalidArgumentException('Uno o más productos seleccionados no existen.');
        }

        foreach ($products as $p) {
            if (!$p->active) {
                throw new InvalidArgumentException("El producto '{$p->name}' está inactivo y no puede venderse.");
            }
        }

        return DB::transaction(function () use ($draft, $consolidated, $products) {
            $draft->details()->delete();

            $subtotalCents = 0;

            foreach ($consolidated as $productId => $qtyVo) {
                /** @var Product $p */
                $p = $products->get($productId);
                $unitPriceFloat = (float) $p->sale_price;
                $quantityFloat = (float) $qtyVo->toDecimalString();

                $lineTotalFloat = round($quantityFloat * $unitPriceFloat, 2);
                $lineTotalStr = number_format($lineTotalFloat, 2, '.', '');

                $subtotalCents += (int) round($lineTotalFloat * 100);

                SaleDetail::create([
                    'sale_id' => $draft->id,
                    'product_id' => $p->id,
                    'product_sku' => $p->sku,
                    'product_name' => $p->name,
                    'unit' => $p->unit,
                    'quantity' => $qtyVo->toDecimalString(),
                    'unit_price' => number_format($unitPriceFloat, 2, '.', ''),
                    'line_total' => $lineTotalStr,
                ]);
            }

            $subtotalStr = number_format($subtotalCents / 100, 2, '.', '');

            $draft->subtotal = $subtotalStr;
            $draft->total = $subtotalStr;
            $draft->save();

            return $draft->fresh(['details', 'customer']);
        });
    }

    public function confirm(Sale $draft, User $actor): Sale
    {
        return DB::transaction(function () use ($draft, $actor) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::where('id', $draft->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$lockedSale->isDraft()) {
                throw new ConflictHttpException('La venta ya fue confirmada o no está en estado borrador.');
            }

            $details = $lockedSale->details()->get();

            if ($details->isEmpty()) {
                throw new InvalidArgumentException('No se puede confirmar una venta sin líneas de detalle.');
            }

            if ($lockedSale->customer_id) {
                $customer = Customer::find($lockedSale->customer_id);
                if (!$customer || !$customer->active) {
                    throw new InvalidArgumentException('El cliente asociado a la venta está inactivo.');
                }
            }

            $productIds = $details->pluck('product_id')->unique()->sort()->values()->all();
            $products = Product::whereIn('id', $productIds)
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($details as $detail) {
                /** @var Product|null $product */
                $product = $products->get($detail->product_id);

                if (!$product || !$product->active) {
                    throw new InvalidArgumentException("El producto '{$detail->product_name}' está inactivo o no existe.");
                }

                if (number_format((float) $detail->unit_price, 2, '.', '') !== number_format((float) $product->sale_price, 2, '.', '')) {
                    throw new ConflictHttpException("El precio del producto '{$product->name}' ha cambiado. Por favor revise el borrador.");
                }

                $reqQty = new Quantity($detail->quantity);
                $currStock = new Quantity($product->current_stock);

                if ($currStock->getThousandths() < $reqQty->getThousandths()) {
                    throw new InvalidArgumentException("Stock insuficiente para el producto '{$product->name}'.");
                }
            }

            $number = $this->sequenceService->getNext('sale', 'V');

            $subtotalCents = 0;
            foreach ($details as $detail) {
                $qtyFloat = (float) $detail->quantity;
                $priceFloat = (float) $detail->unit_price;
                $lineTotalFloat = round($qtyFloat * $priceFloat, 2);

                $detail->line_total = number_format($lineTotalFloat, 2, '.', '');
                $detail->save();

                $subtotalCents += (int) round($lineTotalFloat * 100);
            }

            $subtotalStr = number_format($subtotalCents / 100, 2, '.', '');

            foreach ($details as $detail) {
                /** @var Product $product */
                $product = $products->get($detail->product_id);
                $delta = '-' . (string) $detail->quantity;

                $this->stockService->applyMovement(
                    product: $product,
                    type: 'sale',
                    quantityDelta: $delta,
                    user: $actor,
                    reason: "Venta {$number}",
                    unitCost: null,
                    reference: $lockedSale
                );
            }

            $lockedSale->number = $number;
            $lockedSale->subtotal = $subtotalStr;
            $lockedSale->total = $subtotalStr;
            $lockedSale->status = SaleStatus::CONFIRMED;
            $lockedSale->confirmed_at = now();
            $lockedSale->confirmed_by = $actor->id;
            $lockedSale->save();

            return $lockedSale->fresh(['details', 'customer', 'creator', 'confirmer']);
        });
    }
}
