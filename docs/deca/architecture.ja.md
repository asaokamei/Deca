# アーキテクチャとリクエストの流れ

## 技術スタック（要点）

| 役割 | 主なライブラリ / 実装 |
|------|------------------------|
| HTTP / ルーティング | [Slim 4](https://www.slimframework.com/) |
| DI コンテナ | [PHP-DI](https://php-di.org/)（`DI\Container` が必須） |
| PSR-7 実装 | nyholm/psr7 など（Slim ファクトリ経由） |
| ビュー | Twig（`WScore\Deca\Views\Twig\ViewTwig` が `ViewInterface` を実装） |
| ログ | Monolog（`Definitions` で `LoggerInterface` を定義） |
| Deca 固有 | `core/` 配下の名前空間 `WScore\Deca` |

Deca は **Slim を置き換えない**。ルーティング・ミドルウェア・エラーミドルウェアは Slim の API をそのまま使う。

## レイヤーのイメージ

```
[ ブラウザ ]
     │
     ▼
public/index.php  … セッション開始、`getSettings` → `getDefinitions` → `getContainer`、Slim 構築、`registerRoutes`
     │
     ▼
Slim App … ミドルウェア（LIFO）→ ルートハンドラ
  実行順の例: AppMiddleware → CsRfGuard → RoutingMiddleware → コントローラ
     │
     ├─ クロージャ / Invokable コントローラ / AbstractController 継承クラス
     │
     ▼
ViewInterface（Twig）・Session・Messages など（コンテナから取得）
```

## リクエスト処理の流れ（要点）

1. **`public/index.php`**  
   静的ファイル拡張子は PHP ビルトインサーバー時に `return false` でそのまま配信。それ以外は `boot.php` 経由で起動。

2. **`session_start()`**  
   CSRF やフラッシュでセッションを使うため、コンテナ生成前に開始している。

3. **`getSettings($settingsIniPath)`**（`appDemo/getSettings.php`）  
   **`Setting::forge`** で `settings.ini` を読み込み（**`$_ENV`** とマージ）。

4. **`getDefinitions($setting)`**（`appDemo/getDefinitions.php`）  
   **`WScore\Deca\Definitions`** のデフォルトに加え、`APP_DIR`（= `appDemo`）、`VAR_DIR`、注入した **`Setting`**、appDemo の **`setAlias()`** などを載せた `Definitions` を組み立てる。

5. **`getContainer($definitions)`**（`appDemo/getContainer.php`）  
   **PHP-DI** の `ContainerBuilder` のみ実行。

6. **`getApp($container)`**（`appDemo/getApp.php`）  
   `AppFactory::createFromContainer` で Slim `App` を生成。  
   `$app->add()` の順は `RoutingMiddleware`、`CsRfGuard`、`AppMiddleware`（Slim は **LIFO** のため、**実際のリクエスト処理では** `AppMiddleware` → `CsRfGuard` → `RoutingMiddleware` の順）。  
   その後 **エラーミドルウェア**。  
   最後に **`App` と `RouteCollectorInterface` をコンテナへ登録**。

7. **`registerRoutes($app)`**（`appDemo/routes.php`）  
   `$app->get()` / `group()` 等でルートを定義。

8. **`$app->run()`**  
   Slim がリクエストを処理しレスポンスを返す。

## AI がコードを書くときの注意

- **ルートのクロージャ**では、Slim の慣例により **`$this` はアプリコンテキスト**であり、`$this->get(ViewInterface::class)` のようにコンテナからサービスを取れる（`routes.php` の `/` の例）。
- **クラスベースのコントローラ**（`AbstractController`）では、**リクエスト属性** `ContainerInterface::class` からコンテナを取る（`AppMiddleware` が付与）。
- コンテナは **`DI\Container` でなければならない**（`getApp` 内で `set()` するため）。ジェネリックな `ContainerInterface` のみの実装にはできない。
