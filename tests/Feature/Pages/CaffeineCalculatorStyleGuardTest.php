<?php

declare(strict_types=1);

const CAFFEINE_CALCULATOR_PAGE = __DIR__.'/../../../resources/js/pages/caffeine-calculator.tsx';

const ACARA_HEX_TOKENS = [
    '#10b981', '#064e3b', '#111827', '#d1fae5', '#ecfdf5',
    '#059669', '#dc2626', '#d97706', '#0ea5e9',
    '#f9fafb', '#f3f4f6', '#e5e7eb', '#9ca3af', '#4b5563',
    '#ffffff', '#047857', '#b91c1c', '#991b1b', '#374151', '#065f46',
    '#0f172a', '#1e293b', '#94a3b8', '#334155', '#34d399',
];

const ACARA_RADIUS_CLASS_SUFFIXES = [
    'md', 'lg', 'xl', '2xl', '3xl', 'full',
];

const ACARA_RADIUS_DIRECTIONS = [
    '', 't', 'b', 'l', 'r', 'tl', 'tr', 'bl', 'br', 's', 'e', 'ss', 'se', 'es', 'ee',
];

it('uses no hex colors outside the Acara token palette in the caffeine calculator page', function (): void {
    $contents = file_get_contents(CAFFEINE_CALCULATOR_PAGE);

    expect($contents)->not->toBeFalse();

    preg_match_all('/#[0-9a-fA-F]{3,8}\b/', $contents, $matches);

    $offTokens = collect($matches[0])
        ->map(fn (string $hex): string => mb_strtolower($hex))
        ->reject(fn (string $hex): bool => in_array($hex, ACARA_HEX_TOKENS, true))
        ->values()
        ->all();

    expect($offTokens)->toBe([]);
});

it('does not override font-family in the caffeine calculator page', function (): void {
    $contents = file_get_contents(CAFFEINE_CALCULATOR_PAGE);

    expect($contents)->not->toBeFalse();

    preg_match_all('/font-family/i', $contents, $matches);

    expect($matches[0])->toBe([]);
});

it('only uses border-radius classes that map to 6, 8, 12, 16, 24, or 9999 px in the caffeine calculator page', function (): void {
    $contents = file_get_contents(CAFFEINE_CALCULATOR_PAGE);

    expect($contents)->not->toBeFalse();

    $allowed = [];
    foreach (ACARA_RADIUS_DIRECTIONS as $direction) {
        $prefix = $direction === '' ? 'rounded' : "rounded-{$direction}";
        foreach (ACARA_RADIUS_CLASS_SUFFIXES as $suffix) {
            $allowed[] = "{$prefix}-{$suffix}";
        }
    }

    preg_match_all('/\brounded(?:-[a-z0-9]+)*(?:-\[[^\]]+\])?\b/i', $contents, $matches);

    $offRadii = collect($matches[0])
        ->reject(fn (string $class): bool => in_array($class, $allowed, true))
        ->values()
        ->all();

    expect($offRadii)->toBe([]);
});

it('does not use arbitrary border-radius or inline radius styles in the caffeine calculator page', function (): void {
    $contents = file_get_contents(CAFFEINE_CALCULATOR_PAGE);

    expect($contents)->not->toBeFalse();

    preg_match_all('/rounded-\[[^\]]+\]|border-radius\s*:/i', $contents, $matches);

    expect($matches[0])->toBe([]);
});
