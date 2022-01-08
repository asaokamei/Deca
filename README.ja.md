Deca
====

10年ぐらい使えるPHPフレームワークが欲しい。
そう思って作りはじめたフレームワークです。
Slim4をベースに、比較的小さくて有名で信頼できるパッケージを利用しています。

- Slim4, PHP-DI, nyholm/psr7, monolog, Twig, Aura/Session, filp/whoops, etc.

slim-skeletonやslim-twigをベースに作りはじめ、少しずつ機能をつかしています。
なお、DB/ORMやバリデーションは未対応です。

サーバー側でHTMLを生成することを想定しています。

### 10年使えるとは？

そもそも、10年使えるフレームワークとは何でしょう？
今、考えているのは、次の点です。

1. PHPなどのバージョンアップに対して、比較的簡単な修正で対応できる。
2. 依存パッケージが変更されても、ユーザーコードへの影響が少ない。
3. 10年後に追加機能を追加することになっても、比較的いらいらせず開発できる。

### 開発方針

10年間使えるように次の方針を採用してます。

- よく使われている信頼できるパッケージを利用する。
  - 出来るだけ機能が絞られたパッケージを選ぶ。
  - 出来るだけ依存性が少ないパッケージを選ぶ。
- パッケージは、インターフェースで定義しなおして、
  将来での変更を吸収できるようにしておく。
  - 可能ならPSRを利用する。
  - 無ければ独自インターフェースを作成する。
  - コントローラーから先のユーザーコードは、インターフェースに依存するようにする。
- パッケージ間の「グルーコード」はユーザー側に置いておく。
- MVC2あるいはADRの有名な実装パターンを踏襲することで、
  10年後に触るはめになっても何となくわかるはず。

一方で、開発のしやすさにも考慮したいです。。

#### 検討課題

- DB/ORMの選定
- バリデーション・フォーム生成の選定
- 認証

## ディレクトリ構造

### Project Root

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

### public/index.php

```php

require_once dirname(__DIR__) . '/vendor/autoload.php'; // autoloader
require_once dirname(__DIR__) . '/app/error.php'; // set up error during app setup

// Create Request object from globals
$request = ServerRequestCreatorFactory::create()
    ->createServerRequestFromGlobals();

// Build application
$app = AppBuilder::forge(dirname(__DIR__)) // set the root directory of the project.
    ->loadSettings()                       // load settings from settings.ini
    ->loadContainer()                 // set up DI container with provider classes
    ->build($request);                     // build the app. 

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
```

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

#### 追加Provider

`loadProvider`メソッドを使うことで、他のプロバイダーからコンテナー定義を追加できます。

```php
$app = AppBuilder::forge(dirname(__DIR__))
    ->loadSettings()
    ->loadContainer()
    ->loadProvider(MyProvider::class) // 追加プロバイダー
```

### App構築

`AppBuilder::build`でアプリの構築を行います。
その際、必ず次のスクリプトを読み込みますので、適宜内容を修正してください。

- `app/Application/setup.php`
- `app/Application/middleware.php`
- `app/Routes/routes.php`

また、`build`に独自の設定スクリプトを追加することができます。

```php
$app = AppBuilder::forge(dirname(__DIR__))
    ->loadSettings()
    ->loadContainer()
    ->build($request, [ // App構築
        __DIR__ . 'my-settings.php', // 設定スクリプトの追加
    ]); // build the app. 
```

#### setup.php

`app/Application/setup.php`では、プロバイダー外で
アプリ構築に必要なセットアップを行います。

#### middleware.php

`app/Application/middleware.php`でアプリケーションのミドルウェアを設定する。

- AppMiddleware
  - Decaが動くのに必要な設定を行う。
  - コンテナーをリクエストのアトリビュートに設定するぐらいだけど。
- CsRfGuard
  - GET以外の場合、CSRF対策としてセッションのトークンと照合する。
  
#### ルーティング

`app/Routes/routes.php`でルートの設定を行う。

コードはSlim4と同じ。

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

### 内部関数

- `getArgs(): array`
  - `$args`を返す。
- `getRequest(): ServerRequestInterface`
  - `ServerRequestInterface`を返す。
- `getContainer(): ContainerInterface`
  - `ContainerInterface`を返す。いわゆるサービスロケーター向け。
- `getSession(): SessionInterface`
  - `SessionInterface`を返す。
  - `getFlash(string $key, $default = null)`: flashからデータを読み出す。
  - `setFlash(string $key, $val)`: flashにデータをセットする。
  - `clearFlash()`: flashにあるデータをクリアする。
  - `save(string $key, $val)`: sessionデータに値をセットする・
  - `load($key)`: sessionデータから値を読み出す。
- `getMessages(): MessageInterface`
  - `MessageInterface`を返す。メッセージはフラッシュとビュー用の両方に追加される。
  - `addSuccess(string $message)`: 成功した場合のメッセージを追加する。
  - `addError(string $message)`: 失敗した場合のメッセージを追加する。
- `redirect(): Redirect`
  - `toUrl(string $url, array $query = []): ResponseInterface`: URLへのリダイレクト用レスポンスを返す。
  - `toRoute(string $string, $options = [], $query = []): ResponseInterface`: ルート名へのリダイレクト用レスポンスを返す。
  - `getUrlFor(string $string, $options = [], $query = []): string`: ルート名からURLを返す。
  - `getRelativeUrlFor(string $string, $options = [], $query = []): string`: ルート名からBasePathを除いたを返す。
- `respond(): Respond`
  - `view(string $template, array $data = []): ResponseInterface`: テンプレートを返す。
  - `json(array $json): ResponseInterface`: JSONを返す。
  - `download(string $content, string $filename, $attach = true, $mime = null): ResponseInterface`: ファイルとしてダウンロードする。
  - `response(string $input, int $status, array $header = []): ResponseInterface`: レスポンスを返す。
- `view(string $template, array $data = []): ResponseInterface`

### Responder

ビューを別クラスにするための便利なインターフェースとクラスがあります。

- `App/Application/Interfaces/ControllerResponderInterface`を実装、あるいは
- `AbstractResponder`を継承すると便利に使えます。

### Action

コントローラーからリスポンダーに関する機能を省いた上で、
必ず`action`を呼び出します。

- `AbstractAction`を継承すること。