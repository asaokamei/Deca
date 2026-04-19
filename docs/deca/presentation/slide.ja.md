---
marp: true
theme: default
paginate: true
header: 'Deca Framework'
footer: 'WorkSpot.JP'

---

# Deca Framework

## 「__~~10年使える~~長く使えるPHPフレームワークが欲しい__」

* PHPフレームワーク（FW）
  https://github.com/asaokamei/Deca
* HTMLを返す昔ながらの構成
  * （JavaScriptとかSPAは考えない）

> だからDeca（Decade）…
> Slim4スターターキットの一種。いわゆるMicro-Framework

---

# 「~~10年使える~~長く使える」とは？

## 「__PHPなどのバージョンアップは必ず発生する__」

* 長く使えるとは、バージョンアップ作業で行き詰まらないこと
  * PHPコードの非互換的な変更
  * 依存ライブラリのAPI変更
  * 依存ライブラリの依存性解決

---

# 具体的には？

## 「__銀の弾丸はない…__」

* 標準に寄せる（PSRとか）
  * PSR-3（logger）、PSR-7（http-message）、PSR-11（container）、PSR-15 (HTTP Server Request Handlers)、など
* 依存性が少ない、有名で、長く運用されそうなパッケージを組み合わせる
  * Slim4、PHP-DI、twig、など
* さらにInterfaceでサービスを抽象化

---

# 使い勝手も重要

## 「__使い勝手は妥協はしたくない__」

* Symfony/Laravelと似た使い勝手
  * 理想は、内部の挙動を意識しなくても動くこと
* そのためのグルーコードが`core/`
  * 初期インストールの後はユーザー側で管理
  （Decaに依存することも可能）

---

# 保守性とAIについて

## 「__もう全部AIにお任せしよう__」

* AIが把握しやすいサイズのコードベース
* Testの充実
  * `core/`専用のテスト。
    バージョンアップの際の不具合が起きたか、すぐわかる「はず」
* 最悪でもパッケージの変更で対応
  * いざとなれば、なんでもできる
  * `core/`をユーザーで管理する理由

---

# Decaの現状

## 「__出来たばっかり__」

* 「Slimで十分かも？」
  と思ったときの候補。使い勝手の面倒な部分などが対応済み
* まだ実務で使った経験はない
  * 「こうなるといいな」という状態
  * 最悪、Decaの更新が滞っても大丈夫な構造
