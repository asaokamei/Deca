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
| `app/` | **Not in the stock repo—create if you want.** Often holds the main web app tree (boot, routes, controllers, templates) when you replace or split from `appDemo/`. |
| `src/` | **Not in the stock repo—create if you want.** Often holds domain logic, models, or other code kept separate from the HTTP / Slim layer. |

Names and layout are up to you; the table only sketches a common split.

## Composer `autoload` (example)

Deca ships with `WScore\Deca\` → `core/`. When you add **`app/`** and **`src/`**, register PSR-4 roots in **`composer.json`** (then run **`composer dump-autoload`**):

```json
{
    "autoload": {
        "psr-4": {
            "WScore\\Deca\\": "core/",
            "App\\": "app/",
            "MyModel\\": "src/"
        }
    }
}
```

Adjust namespaces and folder names to match your project; **`App\\`** / **`MyModel\\`** are placeholders.

## Inside `appDemo/` (current layout)

| Path | Role |
|------|------|
| `boot.php` | Loads `error.php`, `getSettings.php`, `getDefinitions.php`, `getContainer.php`, `getApp.php`, `routes.php` (each `require_once`). |
| `error.php` | Error / shutdown / uncaught handlers **before** the container (`ShutdownHandler`). |
| `getSettings.php` | `getSettings(string $settingsIniPath)`: `Setting::forge` (ini + `$_ENV`). |
| `getDefinitions.php` | `getDefinitions(Setting $setting)`: `APP_DIR`, `VAR_DIR`, injected `Setting`, interface aliases on top of core `Definitions`. |
| `getContainer.php` | `getContainer(Definitions $definitions)`: PHP-DI `ContainerBuilder` only. |
| `getApp.php` | `getApp(ContainerInterface $container)`: Slim `App`, middleware, error handling. |
| `routes.php` | `registerRoutes(App $app)`: route definitions only. |
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

1. Decide where your app code lives: e.g. rename **`appDemo/`** to **`app/`**, or introduce **`app/`** (HTTP-facing) and **`src/`** (domain/models) as **new directories you create**; update `require` in `public/index.php` and **`Definitions::APP_DIR`**.  
2. Add PSR-4 mappings in **`composer.json`** (see example above), then **`composer dump-autoload`**.  
3. Replace namespaces and routes in `routes.php` for your site.  
4. Edit `templates/` and ensure `var/` permissions.
