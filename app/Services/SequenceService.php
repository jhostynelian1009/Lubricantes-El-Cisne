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

            $sequence = Sequence::firstOrCreate(
                ['type' => $fullType],
                ['current_value' => 0]
            );

            // Bloquear la fila para evitar condiciones de carrera
            $lockedSequence = Sequence::where('id', $sequence->id)
                ->lockForUpdate()
                ->first();

            $lockedSequence->current_value += 1;
            $lockedSequence->save();

            $paddedValue = str_pad($lockedSequence->current_value, 6, '0', STR_PAD_LEFT);

            return "{$prefix}-{$year}-{$paddedValue}";
        });
    }
}
