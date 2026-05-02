# 認証の統合方針（Bring your own）

Deca は **ログイン用のライブラリや「本物の」認証処理を同梱しません**。セッション＋フォーム、JWT、外部 IdP などは **アプリが選び、資格検証を実装**します。フレームワーク側では次だけを揃えます。

- リクエスト上の主体を表す小さな契約 **`IdentityInterface`**
- リゾルバを実行してリクエストに主体を載せる **`ResolveIdentityMiddleware`**（ゲストは `null`）
- 未認証を拒否する **`RequireIdentityMiddleware`**
- 同じリクエスト属性を読む **Twig ヘルパー**

関連コード: `core/Contracts/IdentityInterface.php`、`core/Middleware/ResolveIdentityMiddleware.php`、`core/Middleware/RequireIdentityMiddleware.php`、`core/Views/Twig/TwigLoader.php`。

**English:** [auth-integration.md](auth-integration.md)

---

## 1. `IdentityInterface` の役割

- **読み取り専用**の「この HTTP リクエストでは誰を主体とするか」のスナップショット。
- **ORM の `User` エンティティが implements することを想定しません**。永続化モデルと HTTP 上の認証主体は分けた方が、アプリ形や API と相性が良いです。
- 具象は **リクエストスコープの小さなアダプタ**にします。内部にだけ `User` を保持してもよいが、共通インターフェースは狭く保つ。

| メソッド | 用途 |
|----------|------|
| `getId(): string` | 安定した主体 ID（ログイン名・メール・UUID 等） |
| `getDisplayName(): string` | UI / Twig 向け表示名。無い場合は `getId()` を返す実装が典型 |
| `getRoles(): array` | 粗いロール名の配列（`string[]`、空でも可） |

---

## 2. PSR-7 リクエスト属性のキー

**キーには `IdentityInterface::class`（インターフェースの FQCN）を使います。**

```php
use WScore\Deca\Contracts\IdentityInterface;

$request = $request->withAttribute(IdentityInterface::class, $identity);

// ゲスト: null を明示（Twig 側は属性欠如もゲスト扱いだが、
// ResolveIdentityMiddleware はゲストでも属性を null で揃える想定）。
$request = $request->withAttribute(IdentityInterface::class, null);
```

読み取り:

```php
$identity = $request->getAttribute(IdentityInterface::class);
if ($identity instanceof IdentityInterface) {
    // ログイン済み
}
```

---

## 3. ドメインの `User` エンティティはどこに置くか

コントローラで **ORM の `User` が必要**なのはよくあるが、**`IdentityInterface` に `getUserObject()` のような拡張をフレームワーク契約に載せない**方がよい（戻り値の型・ロード方針はアプリの責務）。

**推奨**

1. **認証済みか・主体 ID** は **`IdentityInterface`**（またはセッション）から得る。
2. **`User` 本体**は **Auth / リポジトリ**が ID から解決する。
3. 同一リクエスト内の二重クエリを避けたいときだけ、**第 2 のリクエスト属性**（例: アプリの **`User::class`** をキーにした属性）にミドルウェアで載せる。

---

## 4. ミドルウェア

### `ResolveIdentityMiddleware`

- **`IdentityResolverInterface`** を注入する。
- **`resolve(ServerRequestInterface $request): ?IdentityInterface`** を呼び、戻り値を **`IdentityInterface::class`** 属性に設定する（ゲストは **`null`**）。

### `RequireIdentityMiddleware`

- **`IdentityUnauthorizedHandlerInterface`** を注入する。
- 属性が **`IdentityInterface` のインスタンスでない**とき、ハンドラの **`handle($request)`** のレスポンスを返す（デフォルトは **401** と短い本文）。**次のハンドラは呼ばない**。

両クラスは **`final`**。挙動を変えるときはミドルウェアを継承するのではなく、**`Definitions` / `getDefinitions()` で上記 2 インターフェースの実装を差し替える**。

---

## 5. デフォルトの DI（`core/Definitions.php`）

| サービス | デフォルト実装 |
|----------|----------------|
| `IdentityResolverInterface` | `NullIdentityResolver` — 常にゲスト |
| `IdentityUnauthorizedHandlerInterface` | `DefaultIdentityUnauthorizedHandler` — 素の 401 |
| `ResolveIdentityMiddleware` | 上記リゾルバで組み立て |
| `RequireIdentityMiddleware` | 上記未認証ハンドラで組み立て |

**認証路線が複数ある場合:** **`IdentityResolverInterface` の実装 1 つ**の中で **委譲**（パスや `Authorization` ヘッダで振り分け）するか、**ファクトリで別インスタンスのミドルウェア**を登録する。詳しくは **[dependency-injection.ja.md](dependency-injection.ja.md)**。

---

## 6. ミドルウェアの順序（`getApp()`）

Slim 4 は `$app->add()` の記述と逆順（**LIFO**）で実行する。既定スタックは **[middleware-session-csrf.ja.md](middleware-session-csrf.ja.md)** を参照。

`ResolveIdentityMiddleware` は、少なくとも **コンテナがリクエストに載ったあと**（`AppMiddleware` と同じ関心事のレイヤ）に置くのが自然です。**`RequireIdentityMiddleware`** は **認証必須のルート／グループだけ**に適用するのが一般的で、グローバル必須とは限りません。

`appDemo/getApp.php` に、**`$app->add(...)` する位置の目安**のコメントがある。

---

## 7. Twig

`TwigLoader` が登録する関数（ビューで **`setRequest`** 済みのとき）:

| 関数 | 挙動 |
|------|------|
| `isUserLoggedIn()` | 属性が `IdentityInterface` なら `true` |
| `getDisplayName()` | ゲストは `''` |
| `getUserId()` | `getId()`、ゲストは `''` |
| `is_granted(attribute, subject = null)` | Symfony 風の **ロール**判定。`getRoles()` に `attribute` が **厳密一致**で含まれるか。`subject` が **`null` でない**ときは **`false`**（将来のアプリ側 Voter 用に予約）。 |

ビュー全般は **[views-and-twig.ja.md](views-and-twig.ja.md)**。

---

## 8. テスト

**[bootstrap.ja.md](bootstrap.ja.md)** のとおり、**`getContainer($definitions)` の前に `Definitions` を調整**したり、リクエスト属性にテスト用の **`IdentityInterface`** を載せてコントローラを試す。

---

## 9. チェックリスト（Deca / アプリ）

- [ ] **`IdentityInterface`** を `core/Contracts` に維持する。
- [ ] 属性キー **`IdentityInterface::class`** をドキュメント化（本ページ）。
- [ ] アプリで **`IdentityResolverInterface`** を実装し、**`getDefinitions()`** でバインドする。
- [ ] 準備ができたら **`getApp()`** に **`ResolveIdentityMiddleware`** を追加する。必要なルートだけ **`RequireIdentityMiddleware`** を掛ける。
- [ ] ログイン画面へのリダイレクトや JSON 用 401 が必要なら **`IdentityUnauthorizedHandlerInterface`** を差し替える。

特定 OSS（例: 某認証パッケージ）の配線手順は **アプリ／レシピ側**に書き、Deca コアはライブラリ非依存のままにする。
