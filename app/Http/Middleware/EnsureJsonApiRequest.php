<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ProblemResponse;
use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureJsonApiRequest
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Responsable|Response
    {
        $request->headers->set('Accept', 'application/json');

        if ($this->hasRequestPayload($request) && ! $request->isJson()) {
            return new ProblemResponse(
                type: 'https://example.com/problems/unsupported-media-type',
                title: 'Unsupported Media Type',
                status: Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
                detail: __('api.errors.unsupported_media_type'),
            );
        }

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }

    private function hasRequestPayload(Request $request): bool
    {
        if ( ! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        return '' !== $request->getContent() || $request->request->count() > 0;
    }
}
