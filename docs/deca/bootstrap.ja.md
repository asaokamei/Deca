# 起動処理（bootstrap）

## `public/index.php`

処理の順序は次のとおり。

1. **PHP ビルトインサーバー**利用時、画像・CSS・JS などは `return false` で静的配信に任せる。  
2. `vendor/autoload.php` を読み込む。  
3. `appDemo/boot.php` を読み込む（内部で `error.php` → コンテナ関連 → `routes.php` が読み込まれる）。  
4. **`session_start()`** — セッションが未開始なら開始。  
5. **`getContainer()`** — DI コンテナを取得。第 1 引数で `settings.ini` の絶対パスを省略した場合は、**プロジェクトルート**（`appDemo` の親）の `settings.ini` を使う。コンテナ内の `Setting::class` は **`Definitions::SETTINGS_INI_PATH`** を読んで **一度だけ** `Setting::forge(..., $_ENV)` される。  
6. **`getApp($container)`** — Slim `App` を取得。  
7. **`setRoutes($app)`** — ルート登録。  
8. **`$app->run()`**。

## `appDemo/boot.php`

```php
require __DIR__ . '/error.php';
require __DIR__ . '/getContainer.php';
require __DIR__ . '/getApp.php';
require __DIR__ . '/routes.php';
```

`error.php` は **コンテナや Slim より前**に読み込む。オートロード前の致命エラーに備えた処理がある。

## `error.php` の役割

- `display_errors` を抑え、`E_DEPRECATED` などを除外した `error_reporting`。  
- **`set_error_handler`** — 警告などを `ErrorException` に昇格。  
- **`register_shutdown_function`** — 致命的エラー時に `ShutdownHandler` で簡易 HTML / ログ。  
- **`set_exception_handler`** — 未捕捉例外も同様。

テンプレートパスは `appDemo/templates/layouts`、ログは `var/raw-error.log` を参照（コード上のパスは `error.php` を確認）。

## AI 向けメモ

- 本番では **`public/` 以外を Web から見えない**ようにする（ドキュメントルートは `public` のみ）。  
- テストで別の ini を使う場合は **`getContainer('/path/to/settings.ini')`**。`Setting` をモックや手組みのインスタンスに差し替える場合は、**第 2 引数の `Definitions` に `setValue(Setting::class, ...)`** してから `getContainer(null, $definitions)` とする。
