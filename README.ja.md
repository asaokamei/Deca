Deca
====

10年使えるPHPフレームワークが欲しい。
そう思って作ってます。
Slim4をベースに、比較的小さくて有名で信頼できるパッケージを利用しています。

- Slim4, PHP-DI, nyholm/psr7, monolog, Twig, Aura/Session, filp/whoops, etc.

最初はslim-skeletonやslim-twigをベースに作ってました。
まだ少しコードが残っているかもしれません。
なお、DB/ORMやバリデーションは未対応です。

サーバー側でHTMLを生成することを想定しています。

### 10年使えるとは？

そもそも、10年使えるフレームワークとは何でしょう？
今、考えているのは、次の点です。

1. PHPなどのバージョンアップに対して、比較的簡単な修正で対応できる。
2. 10年後に追加機能を追加することになっても、比較的いらいらすることなく開発できる。

### 特徴

- よく使われている信頼できるパッケージを利用する。
- パッケージは、インターフェースで定義しなおして、
  将来での変更を吸収できるようにしておく。
  - 可能ならPSRを利用する。
  - 無ければ独自インターフェースを作成する。
  - コントローラーから先のユーザーコードは、インターフェースに依存するようにする。
- パッケージ間の「グルーコード」はユーザー側に置いておく。
- MVC2あるいはADRの有名な実装パターンを踏襲することで、
  10年後に触るはめになっても何となくわかるはず。
- 軽量なフレームワークですが、使いやすさにも考慮してます。

今後、検討することは。

- DB/ORMの選定
- バリデーション・フォーム生成の選定
- 認証

## ディレクトリ構造

### Root 

- `settings.ini`
  - 環境変数を指定する。
- `app/`
  - アプリケーションの本体。
- `public/`
- `tests/`
- `var/`
- `vendor/`

### app/

- `app/error.php`
  - アプリ構築する前のエラー処理設定
- `app/AppBuilder.php`
  - アプリ構築用クラス
- `app/Application/`
  - アプリ構築用クラスとスクリプト
  - `app/Routes/setup.php`: セットアップ
  - `app/Routes/middleware.php`: ミドルウェア設定スクリプト
- `app/Routes/`
  - ルート定義およびコントローラーなどのユーザーコード。
  - `app/Routes/routes.php`: ルート設定スクリプト
- `app/templates/`
  - テンプレートフォルダー



## アプリ構築

### setting.ini

ルートにある`setting.ini`内にDB接続などの環境設定を記述する。

- APP_ENV = prod
  - 環境。`prod`または`production`で本番環境となる。
  - 環境に応じてプロバイダーを読み込む。
  - 設定されていない場合は本番環境とみなす。
- app_name = deca-demo
  - 特に重要な役割はないがログなどで現れる。
- APP_DEBUG = true
  - デバッグ表示を行うかどうか。
- display_errors = true
  - エラー画面で、トレースなどの詳細情報を表示するかどうか。

### Settingsクラス

`Setting`クラスは、最初は`$_ENV`を、次に`setting.ini`の値を返します。

```php
$setting = Setting::forge('settings.ini', $_ENV);
echo $setting->get('DB_CONN');
echo $setting->DB_CONN;
echo $setting->['DB_CONN'];
```

### コンテナー・プロバイダー

`App\Application\Container\Provider`クラスは、本番環境用のコンテナー定義を設定します。

- 環境によらず、必ず読み込みます。
- APP_DEBUGが真のとき、`ProviderForDebug`クラスを読み込みます。

環境が本番以外の場合、本番用プロバイダーを読み込んだ後、
環境名に応じたプロバイダーを読み込みます。

- 環境が`Dev`の場合、`ProviderDev`を読み込む。

### setup.php

`app/Application/setup.php`では、プロバイダー外で
アプリ構築に必要なセットアップを行います。

### middleware.php

`app/Application/middleware.php`でアプリケーションのミドルウェアを設定する。

- AppMiddleware
  - Decaが動くのに必要な設定を行う。
  - コンテナーをリクエストのアトリビュートに設定するぐらいだけど。
- CsRfGuard
  - GET以外の場合、CSRF対策としてセッションのトークンと照合する。
  
### ルーティング

`app/Routes/routes.php`でルートの設定を行う。
Slim4と同じ。

## コントローラー・アクション

Slim4では以下の引数でルートコーラブルが呼ばれる。

```php
public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // do something here...
        return $response;
    }
```

### ControllerArgFilterInterface

ルートコーラブルに渡される配列`$args`の操作を行う。

下記の例では、コントローラーを起動する前に
`PostAsArg`や`PostArray(name: 'posted)`が呼ばれる。

```php
#[PostAsArgs]
#[PostArray(name: 'posted')]
class FormController extends AbstractController
{
    public function __invoke(...$arg)
}
```

フィルターとしてクラスを利用するには、

- `App\Application\Interfaces\ControllerArgFilterInterface`を実装する、
- クラスをアトリビュートとして作成する
  - `#[Attribute]`を付与する。

### AbstractController

便利なコントローラにしてくれます。

#### on{$method}($argName...)

HTTPメソードに応じて関数を呼び出します。

- `onGet`や`onPost`など。

呼び出す関数を変更するには、次のように
`determineMethod`をオーバーライドしてください。

```php
protected function determineMethod(): string
{
    return $method = $this->getRequest()->getParsedBody()['action'] ?? 'get';
}
```

あと`action`があると、必ずそちらを呼び出します。

#### onGet($varName)

関数の引数で、ルート変数を読み込めます。

```php
// ルート設定
$app->any('/welcome/{name:.*}', WelcomeController::class)->setName('welcome');

class WelcomeController extends AbstractController
    public function action(string $name): ResponseInterface
    {
        return $this->responder->view($name);
    }
}
```

#### 内部関数

- `getArgs(): array`
- `getRequest(): ServerRequestInterface`
- `getSession(): SessionInterface`
- `getContainer(): ContainerInterface`
- `getMessages(): MessageInterface`
- `redirect(): Redirect`
- `respond(): Respond`
- `view(string $template, array $data = []): ResponseInterface`

### Responder

ビューを別クラスにするための便利なインターフェースとクラスがあります。

- `App/Application/Interfaces/ControllerResponderInterface`を実装すること、
- `AbstractResponder`を継承すると便利。

### Action

コントローラーからリスポンダーに関する機能を省いた上で、
必ず`action`を呼び出します。

- `AbstractAction`を継承すること。