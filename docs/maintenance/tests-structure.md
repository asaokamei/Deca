# tests/ 再編の方針（Decaテンプレ凍結運用）

## 背景（前提）

- このリポジトリ（`wscore/deca`）は **Composer `create-project` で配布するスケルトン**。
- 生成されたプロジェクトは **アップストリームDecaとは同期しない**（切り離し運用）。
- 依存は **`composer.lock` を同梱・コミットして凍結**（生成直後の再現性最優先）。
- 更新は基本しない。**PHPバージョン更新などイベント時のみ** `composer.json/lock` を更新する。
- `core/` は外部パッケージの薄いブリッジ層（あまり変更しない）。`src/` はモデル/ドメイン（純粋前提は置かず、I/O混在は許容）。`app/` はコントローラ等アプリ層。
- E2Eは別プロセス起動しない。**同一プロセスで Request→`$app->handle()`→Response** のHTTPスモークを採用。

---

## ゴール：`tests/` の最終ディレクトリ構成（確定）

`tests/` は「対象×粒度＋HTTP」で統一する。

```text
tests/
  Core/
    Unit/
    Integration/
  Src/
    Unit/
    Integration/
  Http/
    App/
    Demo/
  Support/
  Fixtures/
  bootstrap.php
```

### 各フォルダの定義（運用ルール）

- **`tests/Core/Unit`**：coreブリッジの純粋ユニット（少数でOK）
- **`tests/Core/Integration`**：外部パッケージとの“配線”確認（更新イベント時に重要）
- **`tests/Src/Unit`**：ドメイン/モデル等のユニット（増えてOK）
- **`tests/Src/Integration`**：DB/外部I/Oが絡む `src` の統合テスト（必要な場合のみ）
- **`tests/Http/App`**：`app/` をHTTPとして叩く最小スモーク（welcome/404/例外など数本）
- **`tests/Http/Demo`**：`appDemo/` の腐敗防止スモーク（1〜3本だけ）
- **`tests/Support`**：アプリ組み立て・Request生成・共通アサートなど、テスト共通化
- **`tests/Fixtures`**：固定ファイル（テンプレ断片、期待値、サンプル設定等）

---

## 既存フォルダからの移行マップ（迷ったらこの優先順位）

現状 `tests/` 配下にあるフォルダ（例：`Application`, `Routes`, `Views`, `End2End`）は以下の基準で振り分ける。

### 1) End2End → Http/*

- Request→Response を確認しているものは **全部 `tests/Http/`** へ
  - `app/` を対象 → `tests/Http/App`
  - `appDemo/` を対象 → `tests/Http/Demo`

### 2) Routes / Views → “何を保証しているか”で決める

- ルーティング定義のロードやDI配線など「内部のつなぎ込み」 → `tests/Core/Integration` か `tests/App/Integration`
  - ※今回は `App/Integration` フォルダを作らず、`Http/App` と `Core/Integration` で吸収してもOK。どうしても必要なら `tests/App/Integration` を追加するのは許容。
- 「HTTPとしてこのURLがこの表示になる」 → `tests/Http/App` or `tests/Http/Demo`
- Twig等の描画エンジン“ブリッジ”の確認 → `tests/Core/Integration`

### 3) Application → 対象に応じて Core / Src

- coreブリッジのユニット/結合 → `tests/Core/(Unit|Integration)`
- ドメイン/モデル → `tests/Src/(Unit|Integration)`

---

## テスト実行の思想（CI/ローカルを楽にする）

- **普段**：`Core/Unit` + `Src/Unit` を中心（高速）
- **PHP更新などイベント時**：`Core/Integration` + `Http/App`（必要に応じて `Http/Demo` を少数）

### コマンド例

```bash
composer test              # 全テスト
composer test:unit         # ユニットのみ（高速）
composer test:integration  # 統合のみ
composer test:http         # HTTPスモークのみ
```

---

## 追加でやって良いこと（任意だが推奨）

- `tests/Support/AppFactory.php` のような共通ヘルパーを作り、HTTPスモークの準備コードを集約する
- `Http/Demo` は増やしすぎない（腐敗防止の最小限に留める）
