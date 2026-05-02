# ビュー（Twig）

## テンプレートの配置

`Definitions` 内の Twig `FilesystemLoader` は、**`{APP_DIR}/templates/`** をルートとする。  
`APP_DIR` が `appDemo` なら、**`appDemo/templates/hello.twig`** はテンプレート名 **`hello.twig`** で参照する。

## `ViewInterface` と `ViewTwig`

- 実装クラスは **`WScore\Deca\Views\Twig\ViewTwig`**（デフォルトで `ViewInterface` にエイリアス）。  
- **`render(Response, template, data)`** — レスポンスボディに HTML を書き込む。  
- **`drawTemplate(template, data)`** — HTML 文字列のみ（コントローラの `view()` 内部など）。

## リクエストとの連携

`ViewTwig` は **`setRequest(ServerRequestInterface)`** により、ローダー（`TwigLoader` 等）にリクエストを渡せる。  
`AbstractController::getView()` は自動で `setRequest` を呼ぶ。

## フォーム入力・バリデーションエラー

`AbstractController` は、セッションフラッシュの `_prev_inputs` / `_prev_errors` や、バリデーション結果に応じて **`getView()->setInputs(...)`** を呼び、Twig 側で旧入力・エラー表示に使える。

ドット記法のキー（例: `profile.email`）は `FormDotted` 等のヘルパと組み合わせる想定（`core/Views/FormDotted.php` などを参照）。

## 本番キャッシュ

`Setting::isProduction()` が真のとき、Twig の `cache` は **`{VAR_DIR}/cache`**（通常 `var/cache`）。開発時は `cache => false` に近い動作（`auto_reload` は true）。

## クロージャルートからの描画例

```php
$view = $this->get(ViewInterface::class);
$view->setRequest($request);
return $view->render($response, 'hello.twig', ['key' => 'value']);
```

## 認証主体まわりの Twig 関数

リクエストがビューに渡されているとき、**`TwigLoader`** が **`isUserLoggedIn()`**、**`getDisplayName()`**、**`getUserId()`**、Symfony 風の **`is_granted(...)`** を登録する。値はリクエスト属性 **`IdentityInterface::class`** から読む。BYO 認証・ミドルウェア・DI は **[auth-integration.ja.md](auth-integration.ja.md)**。

## AI 向けメモ

- レイアウト継承やパーツ分割は **素の Twig の書き方**でよい。  
- Deca 固有のグローバル変数は `ViewTwig::add()` やローダー経由で足す。
