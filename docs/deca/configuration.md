# Configuration (`settings.ini` and `Setting`)

## File

The project root **`settings.ini`** is loaded by path chosen in **`public/index.php`** (typically **`dirname(__DIR__) . '/settings.ini'`** from `public/`). **`getSettings($path)`** uses **`parse_ini_file`** and merges with **`$_ENV`**; duplicate keys **`$_ENV` wins** (`array_merge($ini, $env)` inside **`Setting::forge`**).

**`Setting`** is built in **`getSettings()`**, then registered on **`Definitions`** in **`getDefinitions($setting)`** (appDemo overrides the core **`Definitions`** factory for **`Setting::class`** with that instance).

## `Setting` class (`WScore\Deca\Services\Setting`)

- `ArrayAccess` / `get($key)` / `__get` for key access.  
- **`isProduction()`** — true when `APP_ENV` is `production` or `prod`.  
- **`isDebug()`** — `APP_DEBUG` (Slim `addErrorMiddleware` uses this for `displayErrorDetails` in `getApp()`).  
- **`appEnv()`** — normalized environment name from `APP_ENV` (e.g. dev / production).

## Sample `settings.ini`

```
[Application]
APP_ENV = dev
APP_NAME = deca-demo
APP_DEBUG = true
MAILER_DSN = null://null
```

## Other keys

Examples used from `Definitions` or `getApp()`:

- **`PDO_DSN` / `PDO_USER` / `PDO_PASS`** — when using `PDO::class` (missing values can fail at connect time).  
- **Mail** — Symfony Mailer `MAILER_DSN`; PHPMailer keys must match **`core/Definitions.php`** `PHPMailer::class` (align names with your `settings.ini`).

## Environment variables

In production, override `APP_ENV` and others via the **web server or PHP-FPM** environment so you can keep one `settings.ini` per server or layer env on top.
