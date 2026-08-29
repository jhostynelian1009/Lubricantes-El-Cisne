<?php

namespace App\Services;

use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * Obtiene el siguiente número de secuencia de forma transaccional y atómica.
     * Genera un formato tipo PREFIJO-YYYY-NNNNNN (ej. E-2026-000001).
     */
    public function getNext(string $type, string $prefix): string
    {
        return DB::transaction(function () use ($type, $prefix) {
            $year = date('Y');
            $fullType = "{$type}_{$year}";

            /** @var Sequence|null $sequence */
            $sequence = Sequence::where('type', $fullType)->lockForUpdate()->first();

            if (!$sequence) {
                try {
                    $sequence = Sequence::create([
                        'type' => $fullType,
                        'current_value' => 0,
                    ]);
                    $sequence = Sequence::where('id', $sequence->id)->lockForUpdate()->first();
                } catch (\Throwable $e) {
                    $sequence = Sequence::where('type', $fullType)->lockForUpdate()->firstOrFail();
                }
            }

            $sequence->current_value += 1;
            $sequence->save();

            $paddedValue = str_pad((string) $sequence->current_value, 6, '0', STR_PAD_LEFT);

            return "{$prefix}-{$year}-{$paddedValue}";
        });
    }
}
