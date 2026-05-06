<?php

if (!function_exists('format_rp')) {
    /**
     * Format a numeric value as Indonesian Rupiah, e.g. 1500000 → "Rp 1.500.000".
     */
    function format_rp(int|float|string $n): string
    {
        $value = is_string($n) ? (float) preg_replace('/[^0-9.-]/', '', $n) : (float) $n;
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}

if (!function_exists('format_rp_short')) {
    /**
     * Compact rupiah for dashboard cards: 12_400_000 → "Rp 12.4M", 5_000 → "Rp 5K".
     */
    function format_rp_short(int|float $val): string
    {
        if ($val >= 1_000_000) return 'Rp ' . number_format($val / 1_000_000, 1, ',', '.') . 'M';
        if ($val >= 1_000)     return 'Rp ' . number_format($val / 1_000, 0, ',', '.') . 'K';
        return 'Rp ' . number_format($val, 0, ',', '.');
    }
}

if (!function_exists('format_idr_input')) {
    /**
     * Format a digits-only string with Indonesian thousand separators
     * for display in number inputs (e.g. "15000000" → "15.000.000").
     */
    function format_idr_input(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw) ?? '';
        if ($digits === '') return '';
        return number_format((int) $digits, 0, ',', '.');
    }
}
