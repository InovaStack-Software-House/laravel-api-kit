<?php

declare(strict_types=1);

use App\Http\Middleware\AttachRequestId;
use App\Http\Middleware\EnforceTransportSecurity;
use App\Http\Middleware\EnsureJsonApiRequest;
use App\Http\Middleware\IdempotencyKey;
use App\Http\Middleware\SetRequestLocale;
use App\Http\Middleware\Sunset;
use App\Http\Responses\ProblemResponse;
use App\Support\SecurityAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api/routes.php',
        apiPrefix: '',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'idempotency' => IdempotencyKey::class,
            'sunset' => Sunset::class,
        ]);

        $trustedProxies = (string) env('TRUSTED_PROXIES', '*');
        $middleware->trustProxies('' !== $trustedProxies ? $trustedProxies : null);

        $trustedHosts = array_values(array_filter(array_map(
            static fn(string $host): string => mb_trim($host),
            explode(',', (string) env('TRUSTED_HOSTS', '')),
        )));
        if ([] !== $trustedHosts) {
            $middleware->trustHosts(at: $trustedHosts, subdomains: false);
        }

        $middleware->prependToGroup('api', EnsureJsonApiRequest::class);
        $middleware->prependToGroup('api', EnforceTransportSecurity::class);
        $middleware->prependToGroup('api', SetRequestLocale::class);
        $middleware->prependToGroup('api', AttachRequestId::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, Request $request): ProblemResponse {
            SecurityAudit::log('api.validation_failed', [
                'errors' => array_keys($e->errors()),
            ]);

            return new ProblemResponse(
                type: 'https://example.com/problems/validation-error',
                title: 'Validation Error',
                status: Response::HTTP_UNPROCESSABLE_ENTITY,
                detail: __('api.errors.validation_failed'),
                errors: $e->errors(),
            );
        });

        $exceptions->render(function (AuthenticationException $e, Request $request): ProblemResponse {
            SecurityAudit::log('auth.unauthenticated', [
                'guard' => 'sanctum',
            ]);

            return new ProblemResponse(
                type: 'https://example.com/problems/unauthenticated',
                title: 'Unauthenticated',
                status: Response::HTTP_UNAUTHORIZED,
                detail: __('api.errors.unauthenticated'),
            );
        });

        $exceptions->render(function (AuthorizationException $e, Request $request): ProblemResponse {
            SecurityAudit::log('auth.forbidden', [
                'exception' => $e::class,
            ]);

            return new ProblemResponse(
                type: 'https://example.com/problems/forbidden',
                title: 'Forbidden',
                status: Response::HTTP_FORBIDDEN,
                detail: __('api.errors.forbidden'),
            );
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request): ProblemResponse {
            SecurityAudit::log('auth.forbidden', [
                'exception' => $e::class,
            ]);

            return new ProblemResponse(
                type: 'https://example.com/problems/forbidden',
                title: 'Forbidden',
                status: Response::HTTP_FORBIDDEN,
                detail: __('api.errors.forbidden'),
            );
        });

        $exceptions->render(fn(ModelNotFoundException $e, Request $request): ProblemResponse => new ProblemResponse(
            type: 'https://example.com/problems/not-found',
            title: 'Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: __('api.errors.not_found'),
        ));

        $exceptions->render(fn(NotFoundHttpException $e, Request $request): ProblemResponse => new ProblemResponse(
            type: 'https://example.com/problems/not-found',
            title: 'Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: __('api.errors.not_found'),
        ));

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request): ProblemResponse {
            SecurityAudit::log('api.rate_limited');

            return new ProblemResponse(
                type: 'https://example.com/problems/too-many-requests',
                title: 'Too Many Requests',
                status: Response::HTTP_TOO_MANY_REQUESTS,
                detail: __('api.errors.too_many_requests'),
            );
        });

        $exceptions->render(function (InvalidSignatureException $e, Request $request): ProblemResponse {
            SecurityAudit::log('auth.email_verification.invalid_signature');

            return new ProblemResponse(
                type: 'https://example.com/problems/invalid-verification-link',
                title: 'Invalid Verification Link',
                status: Response::HTTP_FORBIDDEN,
                detail: __('api.auth.invalid_verification_link'),
            );
        });

        $exceptions->render(fn(Throwable $e, Request $request): ProblemResponse => new ProblemResponse(
            type: 'https://example.com/problems/server-error',
            title: 'Server Error',
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
            detail: __('api.errors.server_error'),
        ));
    })->create();
