<p align="center">
  <h1 align="center">Laravel API Kit</h1>
</p>

<p align="center">
  Opinionated Laravel API starter kit for token-based authentication with strong defaults around API design, security, localization, documentation, and testing.
</p>

## Highlights

- PHP `8.5` + Laravel `13`
- SQLite-first local development (`DB_CONNECTION=sqlite`)
- Sanctum personal access token authentication with **least-privilege abilities** and expiration
- Versioned API routes with no `/api` prefix (`/v1/...`)
- **JSON:API** entity responses (`application/vnd.api+json`) with token metadata
- **RFC 9457 Problem Details** error responses (RFC 7807 successor)
- Invokable controllers, Form Request validation + DTO payloads, Action classes
- Full auth flow: register, login, logout, current user, token management, email verification, password reset
- API localization via `Accept-Language` + `Content-Language` (English, Spanish, Brazilian Portuguese)
- Scribe attribute-based API docs + OpenAPI generation
- OpenAPI contract tests to keep docs and runtime behavior in sync
- Sunset middleware to deprecate and retire endpoints safely
- GitHub Actions for CI tests and daily dependency update PRs

## Tech Stack

- Laravel Framework: `^13`
- PHP: `^8.5`
- Auth: `laravel/sanctum`
- Docs/OpenAPI: `knuckleswtf/scribe` (attributes, not docblocks)
- Test Runner: Pest + Laravel test tooling
- Static Analysis / Quality: PHPStan (Larastan, level 10), Pint, Rector

## Quick Start

### 1) Install dependencies

```bash
composer install
```

### 2) Bootstrap environment

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

Or run the bundled setup script:

```bash
composer run setup
```

### 3) Run the API

```bash
composer run dev
```

API base path is versioned and has no global `/api` prefix:

- `http://127.0.0.1:8000/v1/...`

## API Routing

Routing is intentionally split:

- `routes/api/routes.php` — top-level entry point
- `routes/api/v1/auth.php` — one file per resource, versioned

Framework routing is configured with `apiPrefix: ''` in `bootstrap/app.php`, so URLs stay clean.

## Auth Endpoints (V1)

| Method | Path | Auth | Purpose |
|---|---|---|---|
| POST | `/v1/auth/register` | No | Register and issue token |
| POST | `/v1/auth/login` | No | Login and issue token |
| GET | `/v1/auth/me` | Bearer `auth:me` | Current authenticated user |
| POST | `/v1/auth/logout` | Bearer `auth:logout` | Revoke current token |
| GET | `/v1/auth/tokens` | Bearer `auth:tokens:read` | List personal access tokens |
| DELETE | `/v1/auth/tokens` | Bearer `auth:tokens:delete` | Revoke all tokens |
| DELETE | `/v1/auth/tokens/{token_id}` | Bearer `auth:tokens:delete` | Revoke one token |
| POST | `/v1/auth/email/verification-notification` | Bearer `auth:verification:send` | Send/resend verification email |
| GET | `/v1/auth/email/verify/{id}/{hash}` | Signed URL | Verify email |
| POST | `/v1/auth/password/forgot` | No | Request reset email (anti-enumeration) |
| GET | `/v1/auth/password/reset/{token}` | No | Return reset payload for API clients |
| POST | `/v1/auth/password/reset` | No | Reset password |

## First Requests (cURL)

Register:

```bash
curl -X POST http://127.0.0.1:8000/v1/auth/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "device_name": "cli"
  }'
```

Login:

```bash
curl -X POST http://127.0.0.1:8000/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jane@example.com",
    "password": "Password123!",
    "device_name": "cli"
  }'
```

Use token on protected route:

```bash
curl http://127.0.0.1:8000/v1/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Localized response (Brazilian Portuguese):

```bash
curl -X POST http://127.0.0.1:8000/v1/auth/password/forgot \
  -H "Accept: application/json" \
  -H "Accept-Language: pt-BR" \
  -H "Content-Type: application/json" \
  -d '{"email":"unknown@example.com"}'
```

## Response Design

### Entity responses — JSON:API

Entities are returned using Laravel's `JsonApiResource` (`application/vnd.api+json`) with a top-level `meta` block carrying the issued token:

```json
{
  "data": {
    "id": "01HZX3W3T4J8Q57XNZD5BPHJ92",
    "type": "users",
    "attributes": {
      "name": "Jane Doe",
      "email": "jane@example.com",
      "email_verified_at": null,
      "created_at": "2026-01-01T00:00:00+00:00",
      "updated_at": "2026-01-01T00:00:00+00:00"
    }
  },
  "meta": {
    "token": "1|example-token",
    "token_type": "Bearer",
    "expires_at": "2026-01-01T02:00:00+00:00"
  }
}
```

### Error responses — RFC 9457 Problem Details

Every error is a `ProblemResponse` (`Content-Type: application/problem+json`):

```json
{
  "type": "https://example.com/problems/validation-error",
  "title": "Validation Error",
  "status": 422,
  "detail": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

The global exception handler in `bootstrap/app.php` normalizes validation, auth, authorization, rate-limit, signature, not-found, and server errors — never an HTML page.

## Authentication

### Token abilities (scopes)

Tokens are issued with least-privilege abilities from `config/sanctum.php` (`abilities.default`):

```
auth:me, auth:logout, auth:verification:send, auth:tokens:read, auth:tokens:delete
```

Routes enforce them via the `abilities:` middleware. No token carries a wildcard `*` by default.

### Token expiration

`SANCTUM_EXPIRATION` (default `120` minutes) controls token lifetime. The `expires_at` value is returned in the response `meta`.

### device_name

Register/login accept a `device_name` used as the token name, making the token list human-readable.

## Localization

Locale resolution is API-first:

- Middleware reads `Accept-Language`
- Locale is resolved against `APP_SUPPORTED_LOCALES`
- Response includes `Content-Language`
- Unsupported locales fall back to `APP_FALLBACK_LOCALE`

Supported out of the box: `en`, `es`, `pt_BR`.

Relevant config/env:

- `APP_LOCALE`
- `APP_FALLBACK_LOCALE`
- `APP_SUPPORTED_LOCALES` (default: `en,es,pt_BR`)
- `SANCTUM_EXPIRATION` (default: `120` minutes)

## Security Defaults

- ULID primary keys for users
- Password hashing via model casts
- Email verification required model contract (`MustVerifyEmail`)
- Rate limits configured in `AppServiceProvider`:
  - `auth-register`: 10/minute per IP
  - `auth-login`: 10/minute per IP + email
  - `auth-password`: 5/minute per IP + email
  - `auth-protected`: 60/minute per authenticated user
- Verification endpoints use signed URLs and throttling
- Write endpoints enforce JSON payloads (`application/json`) → `415` otherwise
- API responses include baseline hardening headers (`nosniff`, `DENY`, `no-referrer`)
- API responses include an `X-Request-Id` header (propagated or generated)
- Security-sensitive auth/token actions emit structured `security.audit` log events
- Critical write endpoints support `Idempotency-Key` replay/conflict handling
- Configurable transport hardening for HTTPS enforcement, HSTS, trusted proxies/hosts, and strict CORS origins
- Production boot checks fail fast on unsafe config (`APP_DEBUG`, HTTPS, CORS wildcard, trusted hosts)

## Sunset Middleware (Endpoint Deprecation)

`App\Http\Middleware\Sunset` adds deprecation metadata and can enforce retirement.

```php
Route::middleware('sunset:2030-01-01,https://api.example.com/v2/auth/login,true')
    ->post('/v1/auth/login', LoginController::class);
```

Behavior:

- Adds `Deprecation` and `Sunset` headers
- Adds `Link: <...>; rel="successor-version"` when successor URL is valid
- Can return `410 Gone` after sunset date when enforcement is enabled

## API Documentation (Scribe + OpenAPI)

Scribe is configured for this no-prefix API shape:

- Route matching uses `v1/*` prefixes (`config/scribe.php`)
- Endpoints are documented via PHP attributes
- OpenAPI output is generated to `public/docs/openapi.yaml`

Generate docs/spec:

```bash
php artisan scribe:generate --no-interaction
```

Generated artifacts:

- `public/docs/index.html`
- `public/docs/openapi.yaml`
- `public/docs/collection.json`

## Testing & Quality

```bash
composer test       # Pest suite (parallel)
composer lint       # Pint (check only)
composer pint       # Pint (apply)
composer stan       # PHPStan level 10 (Larastan)
```

Feature tests cover:

- Token/auth flows (register, login, logout, me, token management)
- Email verification and password reset workflows
- Abilities enforcement (403 on missing scope)
- Idempotency replay/conflict
- Localization behavior (en/es/pt_BR)
- Sunset middleware behavior
- Security hardening (headers, media type, HTTPS/HSTS, request id)
- OpenAPI generation and contract verification

## CI & Dependency Automation

GitHub Actions workflows:

- `.github/workflows/ci-tests.yml` — runs tests on every push and pull request
- `.github/workflows/dependency-updates.yml` — daily `composer update` PR
- `.github/workflows/security-gate.yml` — Composer security audit + Gitleaks secret scan

## Project Structure

```
app/
  Actions/Auth/                     ← business logic (register, login, tokens, ...)
  Http/
    Controllers/Api/V1/Auth/        ← single-action invokable controllers
    Middleware/                     ← AttachRequestId, SetRequestLocale, ...
    Payloads/Auth/                  ← DTO payloads
    Requests/Auth/V1/               ← Form Requests with payload()
    Resources/                      ← JSON:API resources
    Responses/ProblemResponse.php   ← RFC 9457
  Models/                           ← User (ULID, MustVerifyEmail)
  Support/                          ← SecurityAudit, ProductionSecurityChecks
routes/
  api/
    routes.php
    v1/auth.php
lang/
  en/
  es/
  pt_BR/
tests/
  Feature/
config/
  sanctum.php
  security.php
  scribe.php
.github/
  workflows/
```

## License

MIT
