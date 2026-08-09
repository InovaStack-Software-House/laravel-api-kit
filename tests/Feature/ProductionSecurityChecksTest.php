<?php

declare(strict_types=1);

use App\Support\ProductionSecurityChecks;
use RuntimeException;

it('skips checks when the environment is not production', function (): void {
    expect(fn() => ProductionSecurityChecks::assertForEnvironment('local'))
        ->not->toThrow(RuntimeException::class);
});

it('fails in production when debug is enabled', function (): void {
    config(['app.debug' => true]);

    expect(fn() => ProductionSecurityChecks::assertForEnvironment('production'))
        ->toThrow(RuntimeException::class, 'APP_DEBUG must be false');
});

it('skips checks when APP_ENV is not explicitly set', function (): void {
    $original = getenv('APP_ENV');

    putenv('APP_ENV');

    try {
        expect(fn() => ProductionSecurityChecks::assertForEnvironment('production'))
            ->not->toThrow(RuntimeException::class);
    } finally {
        if (false !== $original) {
            putenv('APP_ENV=' . $original);
        }
    }
});
