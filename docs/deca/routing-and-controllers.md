# Routing and controllers

## Where routes live

In **`appDemo/routes.php`**, inside **`setRoutes(App $app): void`**, using Slim 4’s **`$app->get()` / `post()` / `any()` / `group()`**, etc.

## Route callable shapes

1. **Closures**  
   First two parameters are `Request`, `Response`. Closures are bound to the app, so **`$this->get(Service::class)`** resolves from the container (see `/` in `routes.php`).

2. **Class name (`::class`)**  
   PHP-DI resolves the constructor and Slim invokes **`__invoke(Request, Response, array $args)`** (default Slim behavior).

## `AbstractController` (`WScore\Deca\Controllers\AbstractController`)

Most pages subclass this.

### `__invoke` behavior

1. Stores request, response, `$args`, and the container on properties.  
2. If an **`action`** method exists, it is called (with `$args` unpacked).  
3. Otherwise calls **`on{Method}`** where the method comes from **`determineMethod()`** (default: `$_POST['_method']` or the HTTP verb, e.g. `onGet`, `onPost`).  
4. If no handler exists, **`HttpMethodNotAllowedException`**.

### Common protected API (subset)

| Method | Purpose |
|--------|---------|
| `request()` | Current `ServerRequestInterface` |
| `getInputs()` | `getParsedBody()` (forms, POST) |
| `session()` | `SessionInterface` |
| `messages()` | Flash-backed messages (`MessageInterface`) |
| `redirect()` | `Redirect` (by route name or URL) |
| `respond()` | `Respond` (JSON, downloads, …) |
| `view($template, $data)` | Twig HTML `Response` |
| `getView()` | `ViewInterface` (inputs / errors) |
| `validate()` | After `setValidator()` (`ValidatorInterface`) |

Container access: **`$request->getAttribute(ContainerInterface::class)`** (from `AppMiddleware`).

## Thin action-only classes

You can implement only **`action()`** (e.g. `InfoAction` with `phpinfo()`).  
Register with **`$app->get('/info', InfoAction::class)`**.

## Route names and redirects

Use **`->setName('hello')`**, then **`redirect()->toRoute('hello', ...)`** via `RoutingInterface`.

## Notes

- **CSRF:** POST requests need the session token (`CsRfGuard`). Expose the token name in forms (see `Session`).  
- **Method override:** `determineMethod()` reads `_method` in POST. Align with Slim route methods if you use REST-style routing.
