<?php

namespace App\Enums;

enum StockEntryStatus: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Borrador',
            self::CONFIRMED => 'Confirmada',
        };
    }
}
