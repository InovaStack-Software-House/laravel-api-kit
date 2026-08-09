---
paths:
  - 'bootstrap/app.php'
---

# Bootstrap

## Laravel 13 throws NotFoundHttpException/AccessDeniedHttpException, not the Illuminate exceptions
In Laravel 13, implicit route model binding failures throw Symfony's NotFoundHttpException (not ModelNotFoundException) and FormRequest::authorize() failures throw AccessDeniedHttpException (not AuthorizationException). The withExceptions() handler must register render closures for ALL of: ValidationException, AuthenticationException, AuthorizationException, AccessDeniedHttpException, ModelNotFoundException, NotFoundHttpException, TooManyRequestsHttpException, InvalidSignatureException, plus a Throwable catch-all — each returning ProblemResponse with translated detail strings.

## API middleware is prepended to the api group, not per-route
bootstrap/app.php prepends to the 'api' middleware group (order matters, via repeated prependToGroup calls so the LAST call runs FIRST): AttachRequestId, SetRequestLocale, EnforceTransportSecurity, EnsureJsonApiRequest. Do not add force.json-style middlewares to individual route groups. Aliases registered: abilities, ability, idempotency, sunset.

## Exception responses are RFC 9457 and localized
Every render closure returns App\Http\Responses\ProblemResponse (type/title/status/detail[/errors]) with Content-Type: application/problem+json. detail strings come from lang/{en,es}/api.php via __(). SecurityAudit::log is called on unauthenticated/forbidden/validation/rate-limit renderers.
