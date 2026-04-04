# Dependency injection (PHP-DI and `Definitions`)

## Overview

`appDemo/getContainer.php` builds the container with **PHP-DI**’s `ContainerBuilder`.  
The base definitions live in **`WScore\Deca\Definitions`** (`core/Definitions.php`) `getDefaults()`; the app overrides or extends them with **`setValue` / `setAlias`**.

## What `Definitions` registers by default (examples)

- `ResponseFactoryInterface` — PSR-17 factory  
- `Setting::class` — `Setting::forge($container->get(Definitions::SETTINGS_INI_PATH), $_ENV)`; path is set by **`getContainer()`** on **`Definitions::SETTINGS_INI_PATH`** (default: project root `settings.ini`)  
- `Environment::class` (Twig) — loader `{APP_DIR}/templates/`, cache under `var/cache` in production  
- `ViewTwig::class` / alias to `ViewInterface`  
- `Session::class` / `SessionInterface`  
- `Messages::class` / `MessageInterface`  
- `LoggerInterface` — Monolog, `var/app.log`  
- `PDO::class` — from `PDO_DSN` etc. in `settings.ini` (configure keys when used)  
- `Mailer::class` (Symfony Mailer), `PHPMailer::class`, etc.

See **`core/Definitions.php`** for exact keys.

## Project overrides (`getContainer()`)

`getContainer()` typically:

- Sets **`Definitions::SETTINGS_INI_PATH`** to the absolute `settings.ini` path (default: `dirname(appDemo)/settings.ini` when the first argument is null)  
- Sets **`Definitions::APP_DIR`** to `__DIR__` (= `appDemo`)  
- Sets **`Definitions::VAR_DIR`** to the project `var` directory  
- Uses **`setAlias()`** for interface → implementation, e.g.:  
  - `RoutingInterface` → `Routing`  
  - `SessionInterface` → `Session`  
  - `MessageInterface` → `Messages`  
  - `ViewInterface` → `ViewTwig`  
  - `MailerInterface` → `PhpMailer` (example)

To swap mail or the view engine, **change aliases here** or add definitions to `Definitions`.

## When Slim is registered on the container

At the end of **`getApp()`**, **`App::class`** and **`RouteCollectorInterface::class`** are `set()` on the container.  
Middleware that needs `App` therefore resolves only **after** `getApp()` completes (`AppMiddleware` resolves at request time).

## Constraints

- **`getApp()` requires `DI\Container`** (it uses `set()`).  
  You cannot drop in a generic PSR-11-only container without changes.

## Adding services

1. Add closures to `Definitions`, or `load()` extra definitions from `getContainer()`.  
2. Type-hint constructors on controllers; PHP-DI autowires when the route uses a class name.  
3. In route closures, use `$this->get(FooInterface::class)` (Slim app binding).
