# Configuration (`settings.ini` and `Setting`)

## File

The project root **`settings.ini`** (path from `getContainer`’s first argument or the default) is read with **`parse_ini_file`** and merged with **`$_ENV`**. For duplicate keys, **`$_ENV` wins** (`array_merge($ini, $env)` inside `Setting::forge`).

**`Setting` is created only inside the container factory** (via `Definitions::SETTINGS_INI_PATH`). `public/index.php` does **not** call `Setting::forge` separately.

## `Setting` class (`WScore\Deca\Services\Setting`)

- `ArrayAccess` / `get($key)` / `__get` for key access.  
- **`isProduction()`** — true when `APP_ENV` is `production` or `prod`.  
- **`isDebug()`** — `APP_DEBUG`.  
- **`appEnv()`** — normalized environment name from `APP_ENV` (e.g. dev / production).

## Sample `settings.ini`

```
[Application]
APP_ENV = dev
app_name = deca-demo
APP_DEBUG = true
display_errors = true
MAILER_DSN = null://null
```

## Other keys

Examples used from `Definitions` or `getApp()`:

- **`display_errors`** — detail level for Slim’s error middleware.  
- **`PDO_DSN` / `PDO_USER` / `PDO_PASS`** — when using `PDO::class` (missing values can fail at connect time).  
- **Mail** — Symfony Mailer `MAILER_DSN`; PHPMailer keys must match **`core/Definitions.php`** `PHPMailer::class` (align names with your `settings.ini`).

## Environment variables

In production, override `APP_ENV` and others via the **web server or PHP-FPM** environment so you can keep one `settings.ini` per server or layer env on top.
