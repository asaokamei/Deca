# Directory layout and naming

## Repository root (essentials)

| Path | Role |
|------|------|
| `core/` | Shared Deca library (Composer `WScore\Deca\`), reusable across apps. |
| `appDemo/` | **Demo / sample app**—copy or rename for production sites. |
| `public/` | Document root; only `index.php` is the entry point. |
| `var/` | Logs (`app.log`), Twig cache (production), raw error logs, etc.—must be writable. |
| `settings.ini` | App name, environment, debug, mail, etc. (`parse_ini_file` merged with `$_ENV`). |
| `vendor/` | Composer dependencies. |

## Inside `appDemo/` (current layout)

| Path | Role |
|------|------|
| `boot.php` | Loads `error.php`, `getContainer.php`, `getApp.php`, `routes.php`. |
| `error.php` | Error / shutdown / uncaught handlers **before** the container (`ShutdownHandler`). |
| `getContainer.php` | `getContainer(?string $settingsIniPath = null, ?Definitions $definitions = null)`—sets `SETTINGS_INI_PATH` / `APP_DIR` / `VAR_DIR` and aliases, builds PHP-DI. |
| `getApp.php` | `getApp()`: builds Slim `App`, middleware, error handling. |
| `routes.php` | `setRoutes()`: route definitions only. |
| `Application/Controller/` | Sample controllers (`AbstractController`, etc.). |
| `Application/Action/` | Small single-action classes. |
| `Application/Forms/` | Sample validators (LeanValidator). |
| `templates/` | Twig templates. The Twig `FilesystemLoader` in `Definitions` uses **`{APP_DIR}/templates/`**. |

## Main `core/` namespaces

- `WScore\Deca\Controllers\` — `AbstractController`, `Respond`, `Redirect`, …
- `WScore\Deca\Middleware\` — `AppMiddleware`, `CsRfGuard`
- `WScore\Deca\Services\` — `Setting`, `Session`, `Routing`, …
- `WScore\Deca\Views\Twig\` — `ViewTwig`, `TwigLoader`, …
- `WScore\Deca\Contracts\` — `ViewInterface`, `SessionInterface`, `RoutingInterface`, … (swap boundaries)

## Checklist for a new site

1. Rename `appDemo` to **`app`**, `src`, or similar; update `require` in `public/index.php` and paths passed as `Definitions::APP_DIR`.  
2. Add your app’s PSR-4 namespace in `composer.json` `autoload`.  
3. Replace namespaces and routes in `routes.php` for your site.  
4. Edit `templates/` and ensure `var/` permissions.
