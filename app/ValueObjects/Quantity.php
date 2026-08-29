<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class Quantity
{
    private int $thousandths;

    public function __construct(string|int|float $value)
    {
        $stringValue = trim((string) $value);

        $this->validateFormat($stringValue);

        $this->thousandths = $this->parseThousandths($stringValue);
    }

    public static function from(string|int|float $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self('0.000');
    }

    private function validateFormat(string $value): void
    {
        if (preg_match('/[eE]/', $value)) {
            throw new InvalidArgumentException("La notación científica no está permitida: {$value}");
        }

        if (!preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            throw new InvalidArgumentException("Formato de cantidad inválido: {$value}");
        }

        if (str_contains($value, '.')) {
            $decimals = explode('.', $value)[1];
            if (strlen($decimals) > 3) {
                throw new InvalidArgumentException("Las cantidades no pueden tener más de 3 decimales: {$value}");
            }
        }
    }

    private function parseThousandths(string $value): int
    {
        $isNegative = str_starts_with($value, '-');
        $cleanValue = ltrim($value, '-');

        $parts = explode('.', $cleanValue);
        $integerPart = (int) $parts[0];
        $decimalPart = isset($parts[1]) ? str_pad($parts[1], 3, '0', STR_PAD_RIGHT) : '000';

        $totalThousandths = ($integerPart * 1000) + (int) $decimalPart;

        return $isNegative ? -$totalThousandths : $totalThousandths;
    }

    public function getThousandths(): int
    {
        return $this->thousandths;
    }

    public function add(self|string|int|float $other): self
    {
        $otherQty = $other instanceof self ? $other : new self($other);
        $newThousandths = $this->thousandths + $otherQty->getThousandths();

        if ($newThousandths < 0) {
            throw new InvalidArgumentException('El saldo resultante no puede ser negativo.');
        }

        return self::fromThousandths($newThousandths);
    }

    public function subtract(self|string|int|float $other): self
    {
        $otherQty = $other instanceof self ? $other : new self($other);
        $newThousandths = $this->thousandths - $otherQty->getThousandths();

        if ($newThousandths < 0) {
            throw new InvalidArgumentException('El saldo resultante no puede ser negativo.');
        }

        return self::fromThousandths($newThousandths);
    }

    public static function fromThousandths(int $thousandths): self
    {
        if ($thousandths < 0) {
            throw new InvalidArgumentException('Las milésimas de cantidad no pueden ser negativas.');
        }

        $integer = intdiv($thousandths, 1000);
        $remainder = abs($thousandths % 1000);
        $decimalStr = str_pad((string) $remainder, 3, '0', STR_PAD_LEFT);

        $instance = new self("{$integer}.{$decimalStr}");
        $instance->thousandths = $thousandths;

        return $instance;
    }

    public function toDecimalString(): string
    {
        $isNegative = $this->thousandths < 0;
        $absThousandths = abs($this->thousandths);

        $integer = intdiv($absThousandths, 1000);
        $remainder = $absThousandths % 1000;

        $formatted = sprintf('%d.%03d', $integer, $remainder);

        return $isNegative ? "-{$formatted}" : $formatted;
    }

    public function isZero(): bool
    {
        return $this->thousandths === 0;
    }

    public function isNegative(): bool
    {
        return $this->thousandths < 0;
    }

    public function __toString(): string
    {
        return $this->toDecimalString();
    }
}
