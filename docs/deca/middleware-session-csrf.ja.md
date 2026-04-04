# ミドルウェア・セッション・CSRF

## 登録順と実行順（`getApp()`）

`$app->add()` の **記述順**は次のとおり。

1. **`addRoutingMiddleware()`**  
2. **`CsRfGuard`**  
3. **`AppMiddleware`**  
4. **`addErrorMiddleware(...)`**（エラー用）

Slim 4 はミドルウェアを **LIFO（後から登録したものが先に実行）** するため、**通常のリクエスト**では **実行順**は `AppMiddleware` → `CsRfGuard` → `RoutingMiddleware` → ルート となる。まずコンテナがリクエスト属性に載り、その後 POST の CSRF 検証、最後にルーティングされる。

## `AppMiddleware`

- **リクエストログ**（メソッド・URL、POST 時はボディを debug）。  
- **`ContainerInterface::class` と `App::class` をリクエスト属性に付与** — 後続のコントローラやルートでコンテナにアクセスするために使用。

## `CsRfGuard`

- **POST のみ**検査。  
- `getParsedBody()` から **セッションが定めるトークン名**のフィールドを読み、`SessionInterface::validateCsRfToken` で検証。  
- 失敗時は `HttpForbiddenException`（またはカスタム `$errorHandler`）。

フォームでは **トークン名と値を hidden で送る**必要がある（テンプレート・`Session` 実装を確認すること）。

## セッション

- **`public/index.php` で `session_start()`** している（コンテナより前）。  
- `Session` サービスは Deca のラッパーで、フラッシュ・CSRF トークン生成などを担当。  
- 詳細は `core/Services/Session.php` と `WScore\Deca\Contracts\SessionInterface` を参照。

## AI が API だけを追加する場合

- **JSON API で POST かつ CSRF を使わない**場合は、`CsRfGuard` の対象から外す・別グループにするなど **ルート設計が必要**（現状はグローバルに POST を検証）。変更時は `getApp()` のミドルウェア登録を編集する。
