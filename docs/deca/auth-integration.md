# Authentication integration (bring your own)

Deca does **not** ship a login library or session-based “real” authentication. Your application chooses the stack (session form login, JWT, external IdP, etc.) and implements credential checks. The framework provides:

- A small **`IdentityInterface`** contract for “who is the principal on this request?”
- **`ResolveIdentityMiddleware`** — runs your resolver and sets the identity on the request (or `null` for guests)
- **`RequireIdentityMiddleware`** — blocks the pipeline when no identity is present
- **Twig helpers** that read the same request attribute

Related code: `core/Contracts/IdentityInterface.php`, `core/Middleware/ResolveIdentityMiddleware.php`, `core/Middleware/RequireIdentityMiddleware.php`, `core/Views/Twig/TwigLoader.php`.

**Japanese:** [auth-integration.ja.md](auth-integration.ja.md)

---

## 1. Role of `IdentityInterface`

- A **read-only snapshot** of the authenticated principal for the current HTTP request.
- It is **not** meant to be implemented by your ORM `User` entity. Keep persistence models separate from the HTTP identity.
- Prefer a **small request-scoped adapter** as the implementation. It may hold a `User` entity internally, but the shared interface should stay narrow.

| Method | Purpose |
|--------|---------|
| `getId(): string` | Stable principal id (login, email, UUID, …) |
| `getDisplayName(): string` | Label for UI / Twig; implementations often fall back to `getId()` |
| `getRoles(): array` | Coarse role names (`string[]`; may be empty) |

---

## 2. Request attribute key

Use **`IdentityInterface::class`** (the interface FQCN) as the PSR-7 attribute name:

```php
use WScore\Deca\Contracts\IdentityInterface;

$request = $request->withAttribute(IdentityInterface::class, $identity);

// Guest: set null (Deca treats missing attribute like guest in Twig helpers;
// middleware that resolves identity should set the attribute explicitly to null).
$request = $request->withAttribute(IdentityInterface::class, null);
```

Readers:

```php
$identity = $request->getAttribute(IdentityInterface::class);
if ($identity instanceof IdentityInterface) {
    // authenticated
}
```

---

## 3. Where the domain `User` entity lives

Controllers often need a full **`User` aggregate**. Do **not** widen `IdentityInterface` with something like `getUserObject()` at framework level: return types and loading policy belong in the app.

**Recommended approach**

1. Use **`IdentityInterface`** (or session) for *whether* someone is authenticated and their **principal id**.
2. Load the **`User`** row via your **Auth service / repository** using that id.
3. Optionally cache the hydrated entity on a **second** request attribute keyed by your app’s **`User::class`** FQCN, set in your own middleware after resolution.

---

## 4. Middleware

### `ResolveIdentityMiddleware`

- Injects **`IdentityResolverInterface`**.
- Calls **`resolve(ServerRequestInterface $request): ?IdentityInterface`** and sets **`IdentityInterface::class`** on the request (including **`null`** for guests).

### `RequireIdentityMiddleware`

- Injects **`IdentityUnauthorizedHandlerInterface`**.
- If the attribute is **not** an `IdentityInterface` instance, returns **`handle($request)`** from that handler (default: **401** with a short body). The next handler is **not** invoked.

Both classes are **`final`**; customize behavior by **binding different implementations** of the two interfaces in **`Definitions`** / **`getDefinitions()`**, not by subclassing the middleware.

---

## 5. Default DI entries (`core/Definitions.php`)

| Service | Default implementation |
|---------|------------------------|
| `IdentityResolverInterface` | `NullIdentityResolver` — always guest |
| `IdentityUnauthorizedHandlerInterface` | `DefaultIdentityUnauthorizedHandler` — plain 401 |
| `ResolveIdentityMiddleware` | Middleware wired with `IdentityResolverInterface` |
| `RequireIdentityMiddleware` | Middleware wired with `IdentityUnauthorizedHandlerInterface` |

**Multi-strategy auth:** use one **`IdentityResolverInterface`** implementation that **delegates** (e.g. by path or `Authorization` header), or register **different middleware instances** via explicit factories—see [dependency-injection.md](dependency-injection.md).

---

## 6. Middleware order (`getApp()`)

Slim runs middleware **LIFO** relative to `$app->add()`. See **[middleware-session-csrf.md](middleware-session-csrf.md)** for the baseline stack.

`ResolveIdentityMiddleware` usually belongs **after** the container is on the request (same layer as **`AppMiddleware`** concerns). **`RequireIdentityMiddleware`** should be applied only to **routes or groups** that must be authenticated (not necessarily global).

`appDemo/getApp.php` includes comments suggesting where to **`$app->add(...)`** these classes.

---

## 7. Twig

`TwigLoader` registers (when the view has **`setRequest`**):

| Function | Behavior |
|----------|----------|
| `isUserLoggedIn()` | `true` if attribute is an `IdentityInterface` instance |
| `getDisplayName()` | `getDisplayName()` or `''` for guests |
| `getUserId()` | `getId()` or `''` for guests |
| `is_granted(attribute, subject = null)` | Symfony-style **role** check: `attribute` must appear in `getRoles()` with strict `in_array`. If **`subject`** is not `null`, returns **`false`** (reserved for app-level voters). |

For more on views, see **[views-and-twig.md](views-and-twig.md)**.

---

## 8. Testing

Following **[bootstrap.md](bootstrap.md)**, customize **`Definitions`** before **`getContainer($definitions)`** and/or attach a test double **`IdentityInterface`** on the request for controller tests.

---

## 9. Checklist (Deca core / your app)

- [ ] Keep **`IdentityInterface`** under `core/Contracts` (or equivalent).
- [ ] Document attribute key **`IdentityInterface::class`** (this page).
- [ ] Implement **`IdentityResolverInterface`** in the app; bind it in **`getDefinitions()`**.
- [ ] Add **`ResolveIdentityMiddleware`** to **`getApp()`** when ready; use **`RequireIdentityMiddleware`** only where needed.
- [ ] Replace **`IdentityUnauthorizedHandlerInterface`** if you need login redirects or JSON 401 bodies.

Library-specific wiring (e.g. a particular OSS auth package) stays in **application code** or recipes, not in Deca core.
