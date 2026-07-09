<?php

namespace App\Helpers;

class RupiahHelper
{
    public static function format(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public static function formatShort(float $amount): string
    {
        $abs = abs($amount);

        if ($abs >= 1_000_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000_000, $abs % 1_000_000_000 === 0 ? 0 : 1, ',', '.') . ' Miliar';
        }

        if ($abs >= 1_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000, $abs % 1_000_000 === 0 ? 0 : 1, ',', '.') . ' Juta';
        }

        if ($abs >= 1_000) {
            return 'Rp ' . number_format($amount / 1_000, $abs % 1_000 === 0 ? 0 : 1, ',', '.') . ' Ribu';
        }

        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
