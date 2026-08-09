---
paths:
  - 'routes/api/**'
---

# Api

## API routes: no api prefix, versioned resources, named routes
routes/api/routes.php is loaded with apiPrefix: '' (no /api prefix). Each resource has its own file under routes/api/v1/ (e.g. v1/auth.php). Routes are named explicitly (auth:v1:register) except the two framework-required names verification.verify and password.reset, which must stay unprefixed. Controllers are final single-action invokables under App\Http\Controllers\Api\V1\{Resource}.

## Entity responses use JSON:API (application/vnd.api+json)
Entity output uses Illuminate\Http\Resources\JsonApi\JsonApiResource with toId()/toType()/toAttributes() (e.g. UserResource, PersonalAccessTokenResource). Token issuance adds top-level meta via ->additional(['meta' => ['token', 'token_type', 'expires_at']]). Do not use JsonResource::withoutWrapping() — it does not apply to JsonApiResource and is removed from AppServiceProvider.

## Apply idempotency middleware to write routes only
The 'idempotency' middleware alias (App\Http\Middleware\IdempotencyKey) must be applied to every state-mutating route (POST/PUT/DELETE) via ->middleware('idempotency') on the individual route, not the group (GET stays uncached). It caches 2xx responses keyed by user/IP + route + Idempotency-Key header; replays return the cached body with Idempotency-Replayed: true, and a reused key with different request data returns 409 (ProblemDetails). Invalid key format returns 422.

## Token abilities and expiration
New tokens get least-privilege abilities from config('sanctum.abilities.default') (auth:me, auth:logout, auth:verification:send, auth:tokens:read, auth:tokens:delete) via the IssueApiTokenAction. Protected routes enforce them with the 'abilities:' middleware. SANCTUM_EXPIRATION controls token expiry and the response meta expires_at.

## Rate limiters are named, not throttle:api
Routes use specific limiters defined in AppServiceProvider: auth-register (10/min/IP), auth-login (10/min/IP+email), auth-password (5/min/IP+email), auth-protected (60/min/user). Email verification/signed routes use throttle:6,1.

## Route params in Form Requests must use validationData()
Form Requests that validate a route parameter (e.g. token_id, id/hash from the URL) must override validationData() to return ['param' => $this->route('param')]; otherwise rules() validates against the empty input bag and fails with 422.
