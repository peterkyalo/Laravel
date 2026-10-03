<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get or set a site configuration setting.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Setting::getAll();
        }

        return Setting::get($key, $default);
    }
}

if (! function_exists('hexToRgb')) {
    /**
     * Convert HEX color string to comma-separated RGB values.
     */
    function hexToRgb(?string $hex, string $default = '245, 158, 11'): string
    {
        if (! $hex) {
            return $default;
        }

        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
            return "$r, $g, $b";
        }

        if (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "$r, $g, $b";
        }

        return $default;
    }
}
