<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ProblemResponse;
use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class IdempotencyKey
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Responsable|Response
    {
        $idempotencyKey = mb_trim((string) $request->headers->get('Idempotency-Key', ''));

        if ('' === $idempotencyKey) {
            return $next($request);
        }

        if (1 !== preg_match('/^[A-Za-z0-9._:-]{8,128}$/', $idempotencyKey)) {
            return new ProblemResponse(
                type: 'https://example.com/problems/invalid-idempotency-key',
                title: 'Invalid Idempotency Key',
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
                detail: __('api.errors.idempotency_key_invalid'),
            );
        }

        $cacheKey = $this->cacheKey($request, $idempotencyKey);
        $requestHash = $this->requestHash($request);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            if (($cached['request_hash'] ?? null) !== $requestHash) {
                return new ProblemResponse(
                    type: 'https://example.com/problems/idempotency-conflict',
                    title: 'Idempotency Key Conflict',
                    status: Response::HTTP_CONFLICT,
                    detail: __('api.errors.idempotency_key_conflict'),
                );
            }

            $body = $cached['body'] ?? null;
            $status = $cached['status'] ?? null;

            $response = new Response(
                is_string($body) ? $body : '',
                is_int($status) ? $status : Response::HTTP_OK,
            );

            $contentType = $cached['content_type'] ?? null;
            if (is_string($contentType) && '' !== $contentType) {
                $response->headers->set('Content-Type', $contentType);
            }

            $response->headers->set('Idempotency-Key', $idempotencyKey);
            $response->headers->set('Idempotency-Replayed', 'true');

            return $response;
        }

        $response = $next($request);

        if ($this->shouldCacheResponse($response)) {
            $ttlMinutes = config('idempotency.ttl_minutes', 10);
            $ttl = max(is_int($ttlMinutes) ? $ttlMinutes : 10, 1);

            Cache::put($cacheKey, [
                'request_hash' => $requestHash,
                'status' => $response->getStatusCode(),
                'body' => $response->getContent() ?: '',
                'content_type' => $response->headers->get('Content-Type'),
            ], now()->addMinutes($ttl));
        }

        $response->headers->set('Idempotency-Key', $idempotencyKey);

        return $response;
    }

    private function cacheKey(Request $request, string $idempotencyKey): string
    {
        $userId = $request->user()?->getAuthIdentifier();
        $ip = $request->ip();

        $scope = is_string($userId) ? $userId : (is_string($ip) ? $ip : '');
        $routeName = $request->route()?->getName();
        $path = $request->path();

        return 'idempotency:' . sha1(sprintf('%s|%s|%s', $scope, $routeName ?? $path, $idempotencyKey));
    }

    private function requestHash(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->method(),
            $request->path(),
            $request->getContent(),
        ]));
    }

    private function shouldCacheResponse(Response $response): bool
    {
        return $response->getStatusCode() >= 200
            && $response->getStatusCode() < 300;
    }
}
