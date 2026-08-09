<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ProblemResponse;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class Sunset
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(
        Request $request,
        Closure $next,
        string $sunsetAt,
        ?string $successorUrl = null,
        ?string $enforceAfterSunset = null,
    ): Responsable|Response {
        $sunsetDate = CarbonImmutable::parse($sunsetAt)->utc();
        $isExpired = now()->greaterThanOrEqualTo($sunsetDate);
        $shouldEnforce = filter_var($enforceAfterSunset, FILTER_VALIDATE_BOOL);

        if ($isExpired && $shouldEnforce) {
            $response = new ProblemResponse(
                type: 'https://example.com/problems/endpoint-sunset',
                title: 'Endpoint Sunset',
                status: Response::HTTP_GONE,
                detail: __('api.sunset.endpoint_unavailable'),
            )->toResponse($request);

            $this->attachHeaders($response, $sunsetDate, $successorUrl);

            return $response;
        }

        $response = $next($request);

        $this->attachHeaders($response, $sunsetDate, $successorUrl);

        return $response;
    }

    private function attachHeaders(Response $response, CarbonImmutable $sunsetDate, ?string $successorUrl): void
    {
        $response->headers->set('Deprecation', '@' . $sunsetDate->timestamp);
        $response->headers->set('Sunset', $sunsetDate->format('D, d M Y H:i:s') . ' GMT');

        if ($successorUrl && filter_var($successorUrl, FILTER_VALIDATE_URL)) {
            $linkValue = sprintf('<%s>; rel="successor-version"', $successorUrl);
            $existingLink = $response->headers->get('Link');
            $response->headers->set('Link', $existingLink ? $existingLink . ', ' . $linkValue : $linkValue);
        }
    }
}
