# ルーティングとコントローラ

## ルート定義の場所

**`appDemo/routes.php`** の `setRoutes(App $app): void` 内で、`$app->get()` / `post()` / `any()` / `group()` など **Slim 4 の API** で定義する。

## ルート callable の形

1. **クロージャ**  
   第 1・第 2 引数は `Request`, `Response`。Slim ではクロージャがアプリにバインドされるため、**`$this->get(Service::class)`** でコンテナからサービス取得可能（`routes.php` の `/` の例）。

2. **クラス名（`::class`）**  
   PHP-DI がコンストラクタを解決し、`__invoke(Request, Response, array $args)` が呼ばれる（Slim のデフォルト動作）。

## `AbstractController`（`WScore\Deca\Controllers\AbstractController`）

多くの画面ではこれを継承する。

### `__invoke` の振る舞い

1. リクエスト・レスポンス・`$args`・コンテナをプロパティに格納。  
2. **`action` メソッドがあれば**それを呼ぶ（引数は `$args` を展開）。  
3. なければ **`on{Method}`** を呼ぶ。メソッド名は `determineMethod()` の結果（デフォルトは `$_POST['_method']` または HTTP メソッド、例: `onGet`, `onPost`）。  
4. 該当メソッドがなければ `HttpMethodNotAllowedException`。

### よく使う protected API（抜粋）

| メソッド / プロパティ | 用途 |
|------------------------|------|
| `request()` | 現在の `ServerRequestInterface` |
| `getInputs()` | `getParsedBody()`（フォーム POST など） |
| `session()` | `SessionInterface` |
| `messages()` | フラッシュ付きメッセージ（`MessageInterface`） |
| `redirect()` | `Redirect`（ルート名・URL へリダイレクト） |
| `respond()` | `Respond`（JSON・ダウンロード等） |
| `view($template, $data)` | Twig で HTML を書き込んだ `Response` |
| `getView()` | `ViewInterface`（入力値・エラーと連携） |
| `validate()` | `setValidator()` 後に利用（`ValidatorInterface`） |

コンテナ取得: **`$request->getAttribute(ContainerInterface::class)`**（`AppMiddleware` が付与）。

## アクション専用の薄いクラス

`action()` だけを実装するスタイルも可能（例: `InfoAction` で `phpinfo()`）。  
ルートは **`$app->get('/info', InfoAction::class)`** のようにクラス名を指定。

## ルート名とリダイレクト

`->setName('hello')` のように名前を付け、`redirect()->toRoute('hello', ...)` で利用する（`RoutingInterface` 経由）。

## AI 向け注意

- **CSRF**: POST にはセッションのトークンが必要（`CsRfGuard`）。フォームではトークン名をテンプレートに出すこと（セッション実装を参照）。  
- **メソッド偽装**: `determineMethod()` は `_method` POST パラメータを見る。REST 的なルーティングをする場合は Slim 側の `any` / メソッド制限と整合させる。
