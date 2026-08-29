<?php

namespace App\Services;

use Illuminate\Support\Str;

class DataNormalizer
{
    /**
     * Normalize a generic text string: trim edges and collapse multiple spaces.
     * Returns null if empty string.
     */
    public static function string(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        return preg_replace('/\s+/', ' ', $trimmed);
    }

    /**
     * Normalize email string: trim, collapse spaces, and convert to lowercase.
     * Returns null if empty string.
     */
    public static function email(?string $value): ?string
    {
        $normalized = self::string($value);
        return $normalized !== null ? Str::lower($normalized) : null;
    }

    /**
     * Normalize phone or identification string: trim whitespace, preserve leading zeros.
     * Returns null if empty string.
     */
    public static function code(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        return preg_replace('/\s+/', ' ', $trimmed);
    }
}
