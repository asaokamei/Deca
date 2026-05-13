# 設定（`settings.ini` / `settings.demo.ini` と `Setting`）

## ファイル

プロジェクトルートの設定は **`public/index.php`** でパスを決めて読み込む。通常は **`settings.ini`** を優先し、存在しない場合は **`settings.demo.ini`** にフォールバックする。`getSettings($path)` は **`parse_ini_file`** と環境変数 **`$_ENV`** をマージし、同じキーは **`$_ENV` が優先**（`Setting::forge` 内の `array_merge($ini, $env)`）。

**`Setting`** は **`getSettings()`** で生成し、**`getDefinitions($setting)`** で **`Definitions`** に登録する（appDemo はコアの **`Setting::class`** ファクトリを、このインスタンスで上書きする）。

## `Setting` クラス（`WScore\Deca\Services\Setting`）

- `ArrayAccess` / `get($key)` / `__get` でプロパティ風アクセス。  
- **`isProduction()`** — `APP_ENV` が `production` / `prod` なら true。  
- **`isDebug()`** — `APP_DEBUG`（`getApp()` で Slim の `addErrorMiddleware` の `displayErrorDetails` に使う）。  
- **`appEnv()`** — `APP_ENV` を正規化した環境名（例: dev / production）。

## サンプル（`settings.demo.ini`）

```
[Application]
APP_ENV = dev
APP_NAME = deca-demo
APP_DEBUG = true
MAILER_DSN = null://null
```

## その他のキー

`Definitions` や `getApp()` で参照される例:

- **`PDO_DSN` / `PDO_USER` / `PDO_PASS`** — `PDO::class` を使う場合（未設定だと接続時にエラーになり得る）。  
- **メール** — Symfony Mailer の `MAILER_DSN`、PHPMailer 用のキーは `core/Definitions.php` の `PHPMailer::class` 定義を参照（プロジェクトの `settings.ini` のキー名と一致させる）。

## 環境変数

本番では **Web サーバーまたは PHP-FPM の環境変数**で `APP_ENV` などを上書きし、`settings.ini` をサーバーごとに変える運用が可能。
