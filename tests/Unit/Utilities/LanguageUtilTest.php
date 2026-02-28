<?php

declare(strict_types=1);

use App\Utilities\LanguageUtil;

it('returns all languages from config', function (): void {
    $languages = LanguageUtil::all();

    expect($languages)->toBeArray()
        ->toHaveKeys(['en', 'fr', 'mn'])
        ->and($languages['en'])->toBe('English')
        ->and($languages['fr'])->toBe('Français')
        ->and($languages['mn'])->toBe('Монгол');
});

it('returns language keys', function (): void {
    $keys = LanguageUtil::keys();

    expect($keys)->toBeArray()
        ->toContain('en', 'fr', 'mn');
});

it('gets language by code', function (): void {
    expect(LanguageUtil::get('en'))->toBe('English')
        ->and(LanguageUtil::get('fr'))->toBe('Français')
        ->and(LanguageUtil::get('mn'))->toBe('Монгол')
        ->and(LanguageUtil::get('invalid'))->toBeNull();
});

it('checks if language exists', function (): void {
    expect(LanguageUtil::has('en'))->toBeTrue()
        ->and(LanguageUtil::has('fr'))->toBeTrue()
        ->and(LanguageUtil::has('mn'))->toBeTrue()
        ->and(LanguageUtil::has('invalid'))->toBeFalse();
});

it('returns default language', function (): void {
    expect(LanguageUtil::default())->toBe('en');
});
