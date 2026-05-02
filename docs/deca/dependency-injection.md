# Dependency injection (PHP-DI and `Definitions`)

## Overview

**`appDemo/getContainer.php`** only runs **PHP-DI**’s `ContainerBuilder` on an assembled **`Definitions`** object.  
The base definitions live in **`WScore\Deca\Definitions`** (`core/Definitions.php`) `getDefaults()`; **`getDefinitions($setting)`** in **`appDemo/getDefinitions.php`** adds **`APP_DIR`**, **`VAR_DIR`**, the **`Setting`** instance, and **`setAlias()`** entries.

## What `Definitions` registers by default (examples)

- `ResponseFactoryInterface` — PSR-17 factory  
- `Setting::class` — in core, **`Setting::forge($container->get(Definitions::SETTINGS_INI_PATH), $_ENV)`**; **appDemo** overrides this by **`setValue(Setting::class, $setting)`** after **`getSettings()`**  
- `Environment::class` (Twig) — loader `{APP_DIR}/templates/`, cache under `var/cache` in production  
- `ViewTwig::class` / alias to `ViewInterface`  
- `Session::class` / `SessionInterface`  
- `Messages::class` / `MessageInterface`  
- `IdentityResolverInterface` → `NullIdentityResolver`; `IdentityUnauthorizedHandlerInterface` → `DefaultIdentityUnauthorizedHandler`; **`ResolveIdentityMiddleware`** / **`RequireIdentityMiddleware`** (see **[auth-integration.md](auth-integration.md)**)  
- `LoggerInterface` — Monolog, `var/app.log`  
- `PDO::class` — from `PDO_DSN` etc. in `settings.ini` (configure keys when used)  
- `Mailer::class` (Symfony Mailer), `PHPMailer::class`, etc.

See **`core/Definitions.php`** for exact keys.

## Project wiring (`getDefinitions()`)

**`getDefinitions(Setting $setting)`** typically:

- Sets **`Definitions::APP_DIR`** to `__DIR__` (= `appDemo`)  
- Sets **`Definitions::VAR_DIR`** to the project `var` directory  
- Sets **`Setting::class`** to the instance from **`getSettings()`**  
- Uses **`setAlias()`** for interface → implementation, e.g.:  
  - `RoutingInterface` → `Routing`  
  - `SessionInterface` → `Session`  
  - `MessageInterface` → `Messages`  
  - `ViewInterface` → `ViewTwig`  
  - `MailerInterface` → `PhpMailer` (example)

To swap mail or the view engine, **change aliases here** or add **`setValue` / `load()`** on **`Definitions`** before **`getContainer($definitions)`**. In tests, call **`getDefinitions($setting)`** then **`$definitions->setValue(Session::class, ...)`** (etc.) before **`getContainer`**.

## When Slim is registered on the container

At the end of **`getApp()`**, **`App::class`** and **`RouteCollectorInterface::class`** are `set()` on the container.  
Middleware that needs `App` therefore resolves only **after** `getApp()` completes (`AppMiddleware` resolves at request time).

## Constraints

- **`getApp()` requires `DI\Container`** (it uses `set()`).  
  You cannot drop in a generic PSR-11-only container without changes.

## Adding services

1. Add closures to `Definitions`, or extend them in **`getDefinitions()`** before **`getContainer($definitions)`**.  
2. Type-hint constructors on controllers; PHP-DI autowires when the route uses a class name.  
3. In route closures, use `$this->get(FooInterface::class)` (Slim app binding).
