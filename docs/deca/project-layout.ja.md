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
| `app/` | **初期構成には含まれない。使うなら自分でディレクトリを用意する。** Web アプリ本体（boot、ルート、コントローラ、テンプレートなど）を置くことが多い。`appDemo/` の置き換えや分割先として使う例。 |
| `src/` | **初期構成には含まれない。使うなら自分でディレクトリを用意する。** ドメインロジックやモデルなど、HTTP / Slim 層と分けたいコード向け。 |

名前や役割の分け方はプロジェクト次第で、上表はよくある分離の例にすぎない。

## Composer の `autoload`（例）

Deca 本体は `WScore\Deca\` → `core/`。**`app/`** と **`src/`** を足す場合は **`composer.json`** の PSR-4 にマッピングを追加し、**`composer dump-autoload`** を実行する。

```json
{
    "autoload": {
        "psr-4": {
            "WScore\\Deca\\": "core/",
            "App\\": "app/",
            "MyModel\\": "src/"
        }
    }
}
```

**`App\\`** や **`MyModel\\`** は名前の例。プロジェクトに合わせて変える。

## `appDemo/` の中身（現在の構成）

| パス | 役割 |
|------|------|
| `boot.php` | `error.php`、`getSettings.php`、`getDefinitions.php`、`getContainer.php`、`getApp.php`、`routes.php` をそれぞれ `require_once`。 |
| `error.php` | 起動**前**のエラー・シャットダウン・未捕捉例外のハンドラ（`ShutdownHandler`）。 |
| `getSettings.php` | `getSettings(string $settingsIniPath)` — `Setting::forge`（ini + `$_ENV`）。 |
| `getDefinitions.php` | `getDefinitions(Setting $setting)` — コアの `Definitions` に `APP_DIR` / `VAR_DIR` / 注入した `Setting` / エイリアスを載せる。 |
| `getContainer.php` | `getContainer(Definitions $definitions)` — PHP-DI のビルドのみ。 |
| `getApp.php` | `getApp(ContainerInterface $container)` — Slim `App` とミドルウェア・エラー処理。 |
| `routes.php` | `registerRoutes(App $app)` — ルート定義のみ。 |
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

1. アプリコードの置き場を決める: 例として **`appDemo/`** を **`app/`** にリネームする、または **`app/`**（HTTP 向け）と **`src/`**（ドメイン／モデル）を **新規に作成**する、など。`public/index.php` の `require` と **`Definitions::APP_DIR`** を合わせる。  
2. **`composer.json`** に PSR-4 を追加する（上の例を参照）。**`composer dump-autoload`** を実行する。  
3. `routes.php` の名前空間とルートをサイト用に置き換える。  
4. `templates/` を編集し、`var/` の権限を確認する。
