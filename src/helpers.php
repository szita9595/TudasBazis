<?php

declare(strict_types=1);

/**
 * Globális helper függvények a template-ekhez
 * Ez a fájl a ViewRenderer render() metódusából töltődik be
 */

if (!function_exists('e')) {
    /**
     * HTML escape - XSS védelem
     */
    function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('csrf_field')) {
    /**
     * CSRF hidden input mező generálása
     */
    function csrf_field(string $token): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('url')) {
    /**
     * URL generálása
     */
    function url(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Asset URL generálása
     */
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('old')) {
    /**
     * Előző form érték lekérdezése
     */
    function old(string $key, mixed $default = ''): mixed
    {
        global $adatok;
        return $adatok[$key] ?? $default;
    }
}
