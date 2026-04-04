Deca
====

**Slim 4** を土台にした **アプリケーションスターターキット**で、**何年も同じスタックの上で**実アプリを作り続け、保守し続けることを目指しています。

- **Slim** が HTTP ルーティングとリクエスト／レスポンスのサイクルを扱います。
- **Deca** は、PHP-DI、nyholm/psr7、Monolog、Twig、filp/whoops など、よく知られた依存の少ないパッケージを **Slim の上に** 載せ、**独自の `core` コード**でそれらをすっきりと組み合わせます。
- **インターフェース**（多くは PSR）で外部ライブラリとの境界を抽象化し、ライブラリを更新してもアプリ側のコードを保ちやすくします。

英語版は [README.md](README.md)。詳細ドキュメントは [docs/deca/README.md](docs/deca/README.md)（英語）· [docs/deca/README.ja.md](docs/deca/README.ja.md)（日本語）。

### インストール

PHP 8.0 以上と [Composer](https://getcomposer.org/) が必要です。

Composer の [`create-project`](https://getcomposer.org/doc/03-cli.md#create-project) で新規プロジェクトを作成し、依存関係をまとめてインストールします。`my-app` は任意のディレクトリ名に置き換えてください。

```
composer create-project wscore/deca my-app
```

### デモ

```
cd my-app
cd public
php -S 127.0.0.1:8000 index.php
```

ブラウザで `http://127.0.0.1:8000` を開きます。
