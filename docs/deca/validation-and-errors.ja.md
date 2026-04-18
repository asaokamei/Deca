# バリデーションとエラー表示

## バリデーション（LeanValidator）

`composer.json` に **`wscore/leanvalidator`** がある。  
`AbstractController` は **`ValidatorInterface` / `ValidatorResultInterface`** を前提に `setValidator()` と `validate()` を提供する。

サンプル: `appDemo/Application/Forms/SampleLeanValidator.php` と `FormController::onPost()`。

流れの例:

1. コントローラのコンストラクタで `setValidator($validator)`。  
2. POST 時に `validate()` を呼ぶ。  
3. `failed()` ならフォーム再表示や `redirect()->toRoute(...)`。  
4. `getView()` がバリデーション結果に応じて **`setInputs`** し、Twig でエラー表示。

## HTTP エラー（Slim）

`getApp()` で `addErrorMiddleware` を設定し、HTML は **`SimpleErrorHandler`**（`core/Handlers/SimpleErrorHandler.php`）を使用。

`DISPLAY_ERRORS`（`settings.ini`）が有効なとき、詳細エラーが表示される想定。

## 起動前の致命的エラー（`error.php`）

オートロードや Slim より前の失敗は **`ShutdownHandler`** が HTML / ログを返す。開発時は `setDisplayErrorDetails` などの設定を確認。

## サンプル用エラーコントローラ

`appDemo/Application/Controller/ErrorController.php` などで、意図的な例外や HTTP エラーの表示を試せる。

## AI 向けメモ

- ドメインルールのバリデーションは **LeanValidator に依存しない**設計も可能。  
  その場合は `AbstractController` の `validate()` を使わず、独自サービスを DI する。  
- フォームの再表示では **`withInputs()`** と `view()` の組み合わせ、または `messages()->addError()` でユーザー向けメッセージを足す。
