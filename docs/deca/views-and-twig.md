# Views (Twig)

## Template location

The Twig `FilesystemLoader` in `Definitions` uses **`{APP_DIR}/templates/`** as the root.  
If `APP_DIR` is `appDemo`, **`appDemo/templates/hello.twig`** is referenced as **`hello.twig`**.

## `ViewInterface` and `ViewTwig`

- Implementation: **`WScore\Deca\Views\Twig\ViewTwig`** (default alias for `ViewInterface`).  
- **`render(Response, template, data)`** — writes HTML into the response body.  
- **`drawTemplate(template, data)`** — HTML string only (e.g. inside controller `view()`).

## Request integration

`ViewTwig` can pass the request to loaders via **`setRequest(ServerRequestInterface)`**.  
`AbstractController::getView()` calls `setRequest` automatically.

## Form input and validation errors

`AbstractController` may call **`getView()->setInputs(...)`** using session flash `_prev_inputs` / `_prev_errors` or validation results so Twig can show old values and errors.

Dot-notation keys (e.g. `profile.email`) work with helpers like `FormDotted`—see `core/Views/FormDotted.php`.

## Production cache

When **`Setting::isProduction()`** is true, Twig **`cache`** is **`{VAR_DIR}/cache`** (usually `var/cache`). In development, caching is effectively off (`auto_reload` stays true).

## Closure route example

```php
$view = $this->get(ViewInterface::class);
$view->setRequest($request);
return $view->render($response, 'hello.twig', ['key' => 'value']);
```

## Identity helpers (Twig)

When the request is set on the view, **`TwigLoader`** exposes **`isUserLoggedIn()`**, **`getDisplayName()`**, **`getUserId()`**, and Symfony-style **`is_granted(...)`** from the request attribute **`IdentityInterface::class`**. See **[auth-integration.md](auth-integration.md)** for BYO auth, middleware, and DI.

## Notes

- Layouts and includes use normal Twig syntax.  
- Deca-specific globals can be added via `ViewTwig::add()` or loaders.
