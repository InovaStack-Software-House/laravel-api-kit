<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ProblemResponse;
use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnforceTransportSecurity
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Responsable|Response
    {
        if ((bool) config('security.force_https', false) && ! $request->isSecure()) {
            return new ProblemResponse(
                type: 'https://example.com/problems/https-required',
                title: 'HTTPS Required',
                status: Response::HTTP_BAD_REQUEST,
                detail: __('api.errors.https_required'),
            );
        }

        $response = $next($request);

        if ((bool) config('security.hsts.enabled', true) && $request->isSecure()) {
            $configuredMaxAge = config('security.hsts.max_age', 31536000);
            $maxAge = max(is_int($configuredMaxAge) ? $configuredMaxAge : 31536000, 0);
            $directives = ["max-age={$maxAge}"];

            if ((bool) config('security.hsts.include_subdomains', true)) {
                $directives[] = 'includeSubDomains';
            }

            if ((bool) config('security.hsts.preload', false)) {
                $directives[] = 'preload';
            }

            $response->headers->set('Strict-Transport-Security', implode('; ', $directives));
        }

        return $response;
    }
}
