<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Carbon\Carbon;

final class IssueApiTokenAction
{
    /**
     * @return array{0: string, 1: Carbon|null}
     */
    public function handle(User $user, string $deviceName): array
    {
        $configuredExpiration = config('sanctum.expiration');
        $expirationMinutes = filter_var(
            $configuredExpiration,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        $expiresAt = false !== $expirationMinutes
            ? now()->addMinutes($expirationMinutes)
            : null;

        $token = $user->createToken(
            name: $deviceName,
            abilities: $this->defaultAbilities(),
            expiresAt: $expiresAt,
        );

        return [$token->plainTextToken, $expiresAt];
    }

    /**
     * @return list<string>
     */
    private function defaultAbilities(): array
    {
        $abilities = config('sanctum.abilities.default', []);

        if ( ! is_array($abilities)) {
            return [];
        }

        return array_values(array_filter(
            array_map(
                static fn(mixed $ability): string => is_string($ability) ? mb_trim($ability) : '',
                $abilities,
            ),
            static fn(string $ability): bool => '' !== $ability,
        ));
    }
}
