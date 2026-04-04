# Deca documentation (English)

Deca is an **application starter** that uses **Slim 4** for HTTP and puts shared behavior under `core/` (namespace `WScore\Deca`). In this repository, the sample app lives in **`appDemo/`** (rename it in your own project if you like).

These pages are written for **humans and AI** building sites on Deca: architecture, bootstrap, and extension points.

**Japanese:** [README.ja.md](README.ja.md)

## Contents

| Document | Topic |
|----------|--------|
| [architecture.md](architecture.md) | Stack, layers, request flow |
| [project-layout.md](project-layout.md) | Directory layout, role of `appDemo` |
| [bootstrap.md](bootstrap.md) | From `public/index.php` to Slim |
| [dependency-injection.md](dependency-injection.md) | `Definitions`, PHP-DI, swappable interfaces |
| [routing-and-controllers.md](routing-and-controllers.md) | Routes, `AbstractController`, actions |
| [views-and-twig.md](views-and-twig.md) | Twig, `ViewInterface`, templates |
| [middleware-session-csrf.md](middleware-session-csrf.md) | Middleware, session, CSRF |
| [configuration.md](configuration.md) | `settings.ini`, `Setting` |
| [validation-and-errors.md](validation-and-errors.md) | Validation, error handling |

## Big picture (30 seconds)

1. **Entry:** `public/index.php` loads Composer autoload, `appDemo/boot.php`, starts the session, calls **`getContainer()`** (registers `settings.ini` and builds `Setting`), then `getApp()` → `setRoutes()`, and **`$app->run()`** lets Slim handle the request.
2. **App code:** Routes in `setRoutes()` in `appDemo/routes.php`. Controllers under `AppDemo\Application\...`, Twig under `appDemo/templates/`.
3. **Shared stack:** Middleware, abstract controller, Twig wrapper, session, logging, etc. in `core/`.
4. **Swapping:** Adjust PHP-DI via `Definitions` and `getContainer()`’s `setAlias()` (e.g. `ViewInterface` → `ViewTwig`).

See each file for details.
