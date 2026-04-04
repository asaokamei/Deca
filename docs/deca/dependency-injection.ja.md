# 依存性の注入（PHP-DI と `Definitions`）

## 概要

`appDemo/getContainer.php` の `getContainer()` が **PHP-DI** の `ContainerBuilder` でコンテナを構築する。  
定義の土台は **`WScore\Deca\Definitions`**（`core/Definitions.php`）の `getDefaults()` で、プロジェクト側で **`setValue` / `setAlias` で上書き・追加**する。

## `Definitions` がデフォルトで登録するもの（例）

- `ResponseFactoryInterface` — PSR-17 ファクトリ  
- `Setting::class` — `Setting::forge($container->get(Definitions::SETTINGS_INI_PATH), $_ENV)`。パスは `getContainer()` が **`Definitions::SETTINGS_INI_PATH`** に設定（省略時はプロジェクトルートの `settings.ini`）  
- `Environment::class`（Twig）— ローダーは `{APP_DIR}/templates/`、本番時は `var/cache` にキャッシュ  
- `ViewTwig::class` / `ViewInterface` へのエイリアス  
- `Session::class` / `SessionInterface`  
- `Messages::class` / `MessageInterface`  
- `LoggerInterface` — Monolog、`var/app.log`  
- `PDO::class` — `settings.ini` の `PDO_DSN` 等（利用時はキーを設定すること）  
- `Mailer::class`（Symfony Mailer）、`PHPMailer::class` など

実際のキー名は **`core/Definitions.php` を参照**すること。

## プロジェクト側での差し替え（`getContainer()`）

`getContainer()` 内で例として行っていること:

- `Definitions::SETTINGS_INI_PATH` に `settings.ini` の絶対パス（第 1 引数が null なら `dirname(appDemo)/settings.ini`）  
- `Definitions::APP_DIR` に `__DIR__`（= `appDemo`）を設定  
- `Definitions::VAR_DIR` にプロジェクトの `var` パス  
- **`setAlias()`** でインターフェース → 実装の対応を明示:
  - `RoutingInterface` → `Routing`
  - `SessionInterface` → `Session`
  - `MessageInterface` → `Messages`
  - `ViewInterface` → `ViewTwig`
  - `MailerInterface` → `PhpMailer`（例）

別のメール実装やビューエンジンに差し替えるときは、**ここでエイリアス先を変える**か、`Definitions` に定義を追加する。

## コンテナに Slim が登録されるタイミング

`getApp()` の末尾で **`App::class`** と **`RouteCollectorInterface::class`** がコンテナに `set()` される。  
そのため **`getApp()` 完了後**でないと `App` を解決できないミドルウェアがある（`AppMiddleware` は実行時に解決される想定）。

## 制約

- **`getApp()` は `DI\Container` であることを要求する**（`$container->set(...)` を使うため）。  
  他の PSR-11 実装だけに差し替えることは、このままではできない。

## AI がサービスを追加するとき

1. `Definitions` にクロージャを追加するか、`getContainer()` で `new Definitions()` に `load()` する。  
2. コントローラのコンストラクタで型ヒントすれば PHP-DI が自動注入（ルートでクラス名を指定する場合）。  
3. ルートクロージャ内では `$this->get(インターフェース::class)`（Slim のアプリバインディング）を使える。
