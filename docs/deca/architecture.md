# Architecture and request flow

## Stack (summary)

| Role | Main library / implementation |
|------|-------------------------------|
| HTTP / routing | [Slim 4](https://www.slimframework.com/) |
| DI container | [PHP-DI](https://php-di.org/) (**must** be `DI\Container`) |
| PSR-7 | nyholm/psr7, etc. (via Slim factories) |
| Views | Twig (`WScore\Deca\Views\Twig\ViewTwig` implements `ViewInterface`) |
| Logging | Monolog (`LoggerInterface` in `Definitions`) |
| Deca-specific | Namespace `WScore\Deca` under `core/` |

Deca **does not replace Slim**. Routing, middleware, and error middleware use Slim’s APIs as-is.

## Layer sketch

```
[ Browser ]
     │
     ▼
public/index.php  … session, getSettings → getDefinitions → getContainer, Slim, registerRoutes
     │
     ▼
Slim App … middleware (LIFO) → route handler
  Example order: AppMiddleware → CsRfGuard → RoutingMiddleware → controller
     │
     ├─ Closures / invokable controllers / AbstractController subclasses
     │
     ▼
ViewInterface (Twig), Session, Messages, etc. (from the container)
```

## Request flow (summary)

1. **`public/index.php`**  
   On the PHP built-in server, static extensions may `return false` for direct file serving; otherwise boot via `boot.php`.

2. **`session_start()`**  
   Session runs before the container so CSRF and flash work.

3. **`getSettings($settingsIniPath)`** (`appDemo/getSettings.php`)  
   Loads **`settings.ini`** via **`Setting::forge`** (merged with **`$_ENV`**).

4. **`getDefinitions($setting)`** (`appDemo/getDefinitions.php`)  
   Builds **`Definitions`**: **`APP_DIR`** (= `appDemo`), **`VAR_DIR`**, the **`Setting`** instance, and appDemo **`setAlias()`** entries, on top of **`WScore\Deca\Definitions`** defaults.

5. **`getContainer($definitions)`** (`appDemo/getContainer.php`)  
   Runs **PHP-DI** `ContainerBuilder` only.

6. **`getApp($container)`** (`appDemo/getApp.php`)  
   Builds Slim `App` with `AppFactory::createFromContainer`.  
   `$app->add()` order: `RoutingMiddleware`, `CsRfGuard`, `AppMiddleware` (Slim is **LIFO**, so **runtime order** is `AppMiddleware` → `CsRfGuard` → `RoutingMiddleware`).  
   Then **error middleware**.  
   Finally registers **`App`** and **`RouteCollectorInterface`** on the container.

7. **`registerRoutes($app)`** (`appDemo/routes.php`)  
   Defines routes with `$app->get()` / `group()`, etc.

8. **`$app->run()`**  
   Slim handles the request and returns a response.

## Notes for implementers

- In **route closures**, Slim binds **`$this`** to the app context, so you can resolve services with `$this->get(ViewInterface::class)` (see `/` in `routes.php`).
- For **class-based controllers** (`AbstractController`), get the container from the request attribute **`ContainerInterface::class`** (injected by `AppMiddleware`).
- The container **must** be **`DI\Container`** (`getApp` uses `set()`). A generic `ContainerInterface`-only implementation will not work as-is.
