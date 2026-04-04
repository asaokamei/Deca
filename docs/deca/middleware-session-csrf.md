# Middleware, session, and CSRF

## Registration vs execution order (`getApp()`)

**`$app->add()` order** (as written):

1. **`addRoutingMiddleware()`**  
2. **`CsRfGuard`**  
3. **`AppMiddleware`**  
4. **`addErrorMiddleware(...)`** (errors)

Slim 4 runs middleware **LIFO** (last added runs first on the request), so **typical request order** is **`AppMiddleware` → `CsRfGuard` → `RoutingMiddleware` → route**. The container is attached first, then POST CSRF is checked, then routing runs.

## `AppMiddleware`

- **Request logging** (method, URL; POST body at debug level).  
- **`ContainerInterface::class` and `App::class` on request attributes** so controllers and routes can access the container.

## `CsRfGuard`

- Runs on **POST** only.  
- Reads the field name from the session, from **`getParsedBody()`**, and validates with **`SessionInterface::validateCsRfToken`**.  
- On failure: **`HttpForbiddenException`** (or a custom `$errorHandler`).

Forms must submit the token name and value in hidden fields—see templates and `Session`.

## Session

- **`session_start()`** in **`public/index.php`** before the container.  
- The **`Session`** service wraps flash, CSRF token generation, etc.  
- See `core/Services/Session.php` and **`WScore\Deca\Contracts\SessionInterface`**.

## JSON APIs without CSRF

For **POST JSON APIs that skip CSRF**, you must **exclude routes or use a separate group**—today `CsRfGuard` applies globally to POST. Adjust middleware registration in **`getApp()`** if needed.
