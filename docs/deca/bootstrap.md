# Bootstrap

## `public/index.php`

Order of operations:

1. On the **PHP built-in server**, static assets (images, CSS, JS) may use `return false` to serve files as-is.  
2. Load `vendor/autoload.php`.  
3. Load `appDemo/boot.php` (which pulls in `error.php` → container helpers → `routes.php`).  
4. **`session_start()`** if the session is not active.  
5. **`getContainer()`** — builds the DI container. If you omit the first argument, the **`settings.ini` at the project root** (parent of `appDemo`) is used. `Setting::class` is created **once** via `Setting::forge(..., $_ENV)` using **`Definitions::SETTINGS_INI_PATH`**.  
6. **`getApp($container)`** — Slim `App`.  
7. **`setRoutes($app)`** — register routes.  
8. **`$app->run()`**.

## `appDemo/boot.php`

```php
require __DIR__ . '/error.php';
require __DIR__ . '/getContainer.php';
require __DIR__ . '/getApp.php';
require __DIR__ . '/routes.php';
```

Load **`error.php` before** the container or Slim—it handles failures before autoload in edge cases.

## Role of `error.php`

- Suppress `display_errors` and tune `error_reporting` (e.g. skip `E_DEPRECATED`).  
- **`set_error_handler`** — promote warnings to `ErrorException`.  
- **`register_shutdown_function`** — on fatal errors, `ShutdownHandler` for simple HTML / logging.  
- **`set_exception_handler`** — uncaught exceptions similarly.

Template path `appDemo/templates/layouts`, log `var/raw-error.log`—see `error.php` for exact paths.

## Notes

- In production, **do not expose** anything outside **`public/`** (document root = `public` only).  
- Tests: **`getContainer('/path/to/settings.ini')`** for another ini file. To inject a mock or hand-built `Setting`, use the second argument: **`setValue(Setting::class, ...)` on `Definitions`**, then **`getContainer(null, $definitions)`**.
