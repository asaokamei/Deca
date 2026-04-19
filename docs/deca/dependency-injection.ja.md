# 依存性の注入（PHP-DI と `Definitions`）

## 概要

**`appDemo/getContainer.php`** は、組み立て済みの **`Definitions`** に対して **PHP-DI** の `ContainerBuilder` を実行するだけである。  
定義の土台は **`WScore\Deca\Definitions`**（`core/Definitions.php`）の `getDefaults()` で、**`appDemo/getDefinitions.php`** の **`getDefinitions($setting)`** が **`APP_DIR`** / **`VAR_DIR`** / 注入する **`Setting`** / **`setAlias()`** を追加する。

## `Definitions` がデフォルトで登録するもの（例）

- `ResponseFactoryInterface` — PSR-17 ファクトリ  
- `Setting::class` — コアでは `Setting::forge($container->get(Definitions::SETTINGS_INI_PATH), $_ENV)`。**appDemo** は **`getSettings()`** のあと **`setValue(Setting::class, $setting)`** で上書きする  
- `Environment::class`（Twig）— ローダーは `{APP_DIR}/templates/`、本番時は `var/cache` にキャッシュ  
- `ViewTwig::class` / `ViewInterface` へのエイリアス  
- `Session::class` / `SessionInterface`  
- `Messages::class` / `MessageInterface`  
- `LoggerInterface` — Monolog、`var/app.log`  
- `PDO::class` — `settings.ini` の `PDO_DSN` 等（利用時はキーを設定すること）  
- `Mailer::class`（Symfony Mailer）、`PHPMailer::class` など

実際のキー名は **`core/Definitions.php` を参照**すること。

## プロジェクト側での配線（`getDefinitions()`）

**`getDefinitions(Setting $setting)`** で行っていることの例:

- `Definitions::APP_DIR` に `__DIR__`（= `appDemo`）  
- `Definitions::VAR_DIR` にプロジェクトの `var` パス  
- **`Setting::class`** に **`getSettings()`** で得たインスタンスを登録  
- **`setAlias()`** でインターフェース → 実装の対応を明示:
  - `RoutingInterface` → `Routing`
  - `SessionInterface` → `Session`
  - `MessageInterface` → `Messages`
  - `ViewInterface` → `ViewTwig`
  - `MailerInterface` → `PhpMailer`（例）

別のメール実装やビューエンジンに差し替えるときは、**ここでエイリアス先を変える**か、`Definitions` に **`setValue` / `load()`** で追加する。テストでは **`getDefinitions($setting)` のあと** **`$definitions->setValue(Session::class, ...)`** などしてから **`getContainer($definitions)`** とする。

## コンテナに Slim が登録されるタイミング

`getApp()` の末尾で **`App::class`** と **`RouteCollectorInterface::class`** がコンテナに `set()` される。  
そのため **`getApp()` 完了後**でないと `App` を解決できないミドルウェアがある（`AppMiddleware` は実行時に解決される想定）。

## 制約

- **`getApp()` は `DI\Container` であることを要求する**（`$container->set(...)` を使うため）。  
  他の PSR-11 実装だけに差し替えることは、このままではできない。

## AI がサービスを追加するとき

1. `Definitions` にクロージャを追加するか、**`getDefinitions()`** で載せたうえで **`getContainer($definitions)`** する。  
2. コントローラのコンストラクタで型ヒントすれば PHP-DI が自動注入（ルートでクラス名を指定する場合）。  
3. ルートクロージャ内では `$this->get(インターフェース::class)`（Slim のアプリバインディング）を使える。
