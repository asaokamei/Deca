# ディレクトリ構成と命名

## リポジトリ直下（重要なものだけ）

| パス | 役割 |
|------|------|
| `core/` | Deca の共有ライブラリ（Composer の `WScore\Deca\`）。アプリ横断で再利用。 |
| `appDemo/` | **デモ兼サンプルアプリ**。本番サイトではこのディレクトリをコピー／リネームして使う想定。 |
| `public/` | ドキュメントルート。`index.php` のみがエントリ。 |
| `var/` | ログ（`app.log`）、Twig キャッシュ（本番時）、生エラーログなど。書き込み可能にする。 |
| `settings.ini` | アプリ名、環境、デバッグ、メール関連など（`parse_ini_file` + `$_ENV` マージ）。 |
| `vendor/` | Composer 依存。 |

## `appDemo/` の中身（現在の構成）

| パス | 役割 |
|------|------|
| `boot.php` | `error.php`、`getContainer.php`、`getApp.php`、`routes.php` を読み込む。 |
| `error.php` | 起動**前**のエラー・シャットダウン・未捕捉例外のハンドラ（`ShutdownHandler`）。 |
| `getContainer.php` | `getContainer(?string $settingsIniPath = null, ?Definitions $definitions = null)` — `SETTINGS_INI_PATH` / `APP_DIR` / `VAR_DIR` とエイリアスを載せて PHP-DI ビルド。 |
| `getApp.php` | `getApp()` 関数: Slim `App` 生成とミドルウェア・エラー処理。 |
| `routes.php` | `setRoutes()` 関数: ルート定義のみ。 |
| `Application/Controller/` | サンプルコントローラ（`AbstractController` 継承など）。 |
| `Application/Action/` | ルート 1 アクション向けの小さなクラス例。 |
| `Application/Forms/` | バリデータ例（LeanValidator）。 |
| `templates/` | Twig テンプレート。`Definitions` の Twig `FilesystemLoader` は **`{APP_DIR}/templates/`** を指す。 |

## `core/` の主要な名前空間

- `WScore\Deca\Controllers\` — `AbstractController`、`Respond`、`Redirect` など
- `WScore\Deca\Middleware\` — `AppMiddleware`、`CsRfGuard`
- `WScore\Deca\Services\` — `Setting`、`Session`、`Routing` など
- `WScore\Deca\Views\Twig\` — `ViewTwig`、`TwigLoader` など
- `WScore\Deca\Contracts\` — `ViewInterface`、`SessionInterface`、`RoutingInterface` など（差し替えの境界）

## 新規サイトでやること（チェックリスト）

1. `appDemo` を **`app` や `src` など好みの名前にリネーム**し、`public/index.php` の `require` と `Definitions::APP_DIR` に渡すパスを合わせる。  
2. `composer.json` の `autoload` にアプリの PSR-4 名前空間を追加する。  
3. `routes.php` の名前空間とルートをサイト用に置き換える。  
4. `templates/` を編集し、`var/` の権限を確認する。
