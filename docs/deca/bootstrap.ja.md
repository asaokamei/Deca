# 起動処理（bootstrap）

## `public/index.php`

処理の順序は次のとおり。

1. **PHP ビルトインサーバー**利用時、画像・CSS・JS などは `return false` で静的配信に任せる。  
2. `vendor/autoload.php` を読み込む。  
3. `appDemo/boot.php` を読み込む（内部で `error.php`、`getSettings.php`、`getDefinitions.php`、`getContainer.php`、`getApp.php`、`routes.php` が `require_once` される）。  
4. **`session_start()`** — セッションが未開始なら開始。  
5. **`settings.ini`** のパスを決める — 通常は **`$settingsIniPath = dirname(__DIR__) . '/settings.ini'`**（プロジェクトルート）。  
6. **`getSettings($settingsIniPath)`** — **`Setting::forge(..., $_ENV)`** で **`Setting`** を返す。  
7. **`getDefinitions($setting)`** — **`WScore\Deca\Definitions`** のデフォルトに加え、`APP_DIR` / `VAR_DIR` / 注入した **`Setting`** / appDemo の **`setAlias()`** などを載せた **`Definitions`** を組み立てる。  
8. **`getContainer($definitions)`** — **PHP-DI** の `ContainerBuilder` でコンテナを構築するだけ（追加マージはしない）。  
9. **`getApp($container)`** — Slim の **`App`** を構築。  
10. **`registerRoutes($app)`** — ルート登録（`appDemo/routes.php`）。  
11. **`$app->run()`**。

## `appDemo/boot.php`

```php
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/getSettings.php';
require_once __DIR__ . '/getDefinitions.php';
require_once __DIR__ . '/getContainer.php';
require_once __DIR__ . '/getApp.php';
require_once __DIR__ . '/routes.php';
```

`error.php` は **コンテナや Slim より前**に読み込む。オートロード前の致命エラーに備えた処理がある。

## `error.php` の役割

- PHP の `display_errors` を抑え、`E_DEPRECATED` などを除外した `error_reporting`。  
- **`set_error_handler`** — 警告などを `ErrorException` に昇格。  
- **`register_shutdown_function`** — 致命的エラー時に `ShutdownHandler` で簡易 HTML / ログ。  
- **`set_exception_handler`** — 未捕捉例外も同様。

テンプレートパスは `appDemo/templates/layouts`、ログは `var/raw-error.log` を参照（コード上のパスは `error.php` を確認）。

## AI 向けメモ

- 本番では **`public/` 以外を Web から見えない**ようにする（ドキュメントルートは `public` のみ）。  
- **テスト**で別の ini を使う場合は **`getSettings('/path/to/settings.ini')`**。`Setting` や `Session` を差し替える場合は **`getDefinitions($setting)` のあと**に **`$definitions->setValue(Session::class, ...)`** などで上書きし、**`getContainer($definitions)`** へ渡す。
