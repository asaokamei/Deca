# Bootstrap

## `public/index.php`

Order of operations:

1. On the **PHP built-in server**, static assets (images, CSS, JS) may use `return false` to serve files as-is.  
2. Load `vendor/autoload.php`.  
3. Load `appDemo/boot.php` (which pulls in `error.php`, `getSettings.php`, `getDefinitions.php`, `getContainer.php`, `getApp.php`, and `routes.php`).  
4. **`session_start()`** if the session is not active.  
5. Resolve **`settings.ini`** — typically **`$settingsIniPath = dirname(__DIR__) . '/settings.ini'`** (project root).  
6. **`getSettings($settingsIniPath)`** — returns **`Setting`** via **`Setting::forge(..., $_ENV)`**.  
7. **`getDefinitions($setting)`** — builds **`Definitions`**: `APP_DIR`, `VAR_DIR`, the **`Setting`** instance, and appDemo **`setAlias()`** entries, on top of **`WScore\Deca\Definitions`** defaults.  
8. **`getContainer($definitions)`** — **PHP-DI** `ContainerBuilder` only (no extra merging).  
9. **`getApp($container)`** — Slim **`App`**.  
10. **`registerRoutes($app)`** — register routes (`appDemo/routes.php`).  
11. **`$app->run()`**.

## `appDemo/boot.php`

```php
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/getSettings.php';
require_once __DIR__ . '/getDefinitions.php';
require_once __DIR__ . '/getContainer.php';
require_once __DIR__ . '/getApp.php';
require_once __DIR__ . '/routes.php';
```

Load **`error.php` before** the container or Slim—it handles failures before autoload in edge cases.

## Role of `error.php`

- Suppress PHP’s `display_errors` and tune `error_reporting` (e.g. skip `E_DEPRECATED`).  
- **`set_error_handler`** — promote warnings to `ErrorException`.  
- **`register_shutdown_function`** — on fatal errors, `ShutdownHandler` for simple HTML / logging.  
- **`set_exception_handler`** — uncaught exceptions similarly.

Template path `appDemo/templates/layouts`, log `var/raw-error.log`—see `error.php` for exact paths.

## Notes

- In production, **do not expose** anything outside **`public/`** (document root = `public` only).  
- **Tests:** use another ini with **`getSettings('/path/to/settings.ini')`**. To inject a hand-built **`Setting`** or override **`Session`**, call **`getDefinitions($setting)`**, then **`$definitions->setValue(Session::class, ...)`** (or adjust **`Setting`** before **`getDefinitions`**), then **`getContainer($definitions)`**.
