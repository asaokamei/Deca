# Deca ドキュメント（日本語）

Deca は **Slim 4** を HTTP 層に使い、`core/`（名前空間 `WScore\Deca`）に共通機能を置いた **アプリケーションスターター**です。実際の Web サイト用コードは、リポジトリでは **`appDemo/`** にあります（新規プロジェクトでは任意の名前にリネームしてよい）。

このディレクトリのドキュメントは、**Deca でサイトを開発するときに AI や新メンバーが参照する前提**で、全体像・起動手順・拡張ポイントをまとめています。

**English:** [README.md](README.md)

## 目次

| ドキュメント | 内容 |
|--------------|------|
| [architecture.ja.md](architecture.ja.md) | 技術スタック、レイヤー、リクエストの流れ |
| [project-layout.ja.md](project-layout.ja.md) | ディレクトリ構成、`appDemo` の役割 |
| [bootstrap.ja.md](bootstrap.ja.md) | `public/index.php` から Slim 起動まで |
| [dependency-injection.ja.md](dependency-injection.ja.md) | `Definitions`、PHP-DI、差し替え可能なインターフェース |
| [routing-and-controllers.ja.md](routing-and-controllers.ja.md) | ルート定義、`AbstractController`、アクション |
| [views-and-twig.ja.md](views-and-twig.ja.md) | Twig、`ViewInterface`、テンプレート配置 |
| [middleware-session-csrf.ja.md](middleware-session-csrf.ja.md) | ミドルウェア、セッション、CSRF |
| [configuration.ja.md](configuration.ja.md) | `settings.ini`、`Setting` クラス |
| [validation-and-errors.ja.md](validation-and-errors.ja.md) | バリデーション、エラーハンドリング |

## 最短の全体像（30 秒）

1. **入口**: `public/index.php` が Composer のオートロード、`appDemo/boot.php`、セッション、**`getContainer()`**（内部で `settings.ini` パスを登録し `Setting` を生成）→ `getApp()` → `setRoutes()` を順に実行し、**`$app->run()`** で Slim がリクエストを処理する。
2. **アプリ固有コード**: ルートは `appDemo/routes.php` の `setRoutes()`。コントローラは `AppDemo\Application\...`、Twig は `appDemo/templates/`。
3. **フレームワーク寄りの共通処理**: `core/` のミドルウェア、抽象コントローラ、Twig ラッパー、セッション、ログなど。
4. **差し替え**: PHP-DI の定義は `Definitions` と `getContainer()` の `setAlias()` でまとめて調整する（例: `ViewInterface` → `ViewTwig`）。

詳細は各ファイルを参照してください。
