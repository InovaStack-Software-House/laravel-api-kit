<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\ProductionSecurityChecks;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ProductionSecurityChecks::assertForEnvironment((string) app()->environment());

        Model::shouldBeStrict();

        RateLimiter::for('auth-register', fn(Request $request): array => [
            Limit::perMinute(10)->by($request->ip()),
        ]);

        RateLimiter::for('auth-login', fn(Request $request): array => [
            Limit::perMinute(10)->by(sprintf('%s|%s', $request->ip(), $request->string('email', '')->toString())),
        ]);

        RateLimiter::for('auth-password', fn(Request $request): array => [
            Limit::perMinute(5)->by(sprintf('%s|%s', $request->ip(), $request->string('email', '')->toString())),
        ]);

        RateLimiter::for('auth-protected', fn(Request $request): array => [
            Limit::perMinute(60)->by($this->rateLimitKey($request)),
        ]);
    }

    private function rateLimitKey(Request $request): string
    {
        $userId = $request->user()?->getAuthIdentifier();
        $ip = $request->ip();

        if (is_string($userId)) {
            return $userId;
        }

        return is_string($ip) ? $ip : '';
    }
}
