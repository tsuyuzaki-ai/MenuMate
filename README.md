# MenuMate

QRコードを使ったテーブル注文システムです。

## 概要

MenuMateは、レストランやカフェで使用するQRコードベースのテーブル注文システムです。お客様はテーブルに設置されたQRコードを読み取ることで、スマートフォンから直接メニューを閲覧し、注文を行うことができます。店舗側は注文の管理や売上の確認を簡単に行えます。

## 主な機能

### お客様向け機能
- **QRコード読み取り**: テーブルIDをQRコードから取得
- **メニュー閲覧**: 商品一覧と詳細情報の表示
- **オプション選択**: 商品ごとのオプション（サイズ、トッピングなど）の選択
- **カート機能**: 複数商品の追加・削除・数量変更
- **注文確認**: 注文内容の確認と送信
- **注文履歴**: 過去の注文履歴の確認

### 店舗側機能
- **注文管理**: 提供待ち・会計待ちの注文を一覧表示
- **注文ステータス更新**: 提供済み・会計済みへのステータス変更
- **商品管理**: 商品の追加・編集・削除・コピー
- **オプション管理**: 商品ごとのオプション設定
- **売上管理**: 日付や商品名での売上検索・集計

## 技術スタック

### バックエンド
- **PHP 8.3+**
- **MySQL 8.0+**
- **PDO**（データベース接続）

### フロントエンド
- **HTML5**
- **CSS3 / SCSS**
- **JavaScript (jQuery 3.6.0)**

### その他
- **Adminer**（データベース管理ツール）

## データベース設計

### テーブル構成
- **products**: 商品情報
- **option_titles**: オプションタイトル（例：サイズ、トッピング）
- **option_details**: オプション詳細（例：Sサイズ、Mサイズ）
- **orders**: 注文情報

## セットアップ

### 必要な環境
- PHP 8.3以上
- MySQL 8.0以上
- Apache / Nginx（MAMP推奨）

### インストール手順

1. リポジトリをクローン
```bash
git clone https://github.com/tsuyuzaki-ai/MenuMate.git
cd MenuMate
```

2. データベースの作成
MySQLでデータベースを作成し、`sql/`ディレクトリ内のSQLファイルをインポートしてください。

3. 環境変数の設定
`.env`ファイルを作成し、データベース接続情報を設定してください。

```env
DB_HOST=localhost
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_NAME=your_database_name
```

4. Webサーバーの設定
MAMPを使用する場合、`/Applications/MAMP/htdocs/MenuMate`に配置してください。

## アクセス方法

### お客様向けページ
- **メニュー一覧**: `http://localhost:8888/MenuMate/public/shop.php?table_id=01`
- **注文かご**: `http://localhost:8888/MenuMate/public/cart.php`
- **注文確認**: `http://localhost:8888/MenuMate/public/confirm.php`
- **注文履歴**: `http://localhost:8888/MenuMate/public/history.php`

### 店舗側ページ
- **注文管理**: `http://localhost:8888/MenuMate/public/order.php`
- **商品管理**: `http://localhost:8888/MenuMate/public/form.php`
- **売上管理**: `http://localhost:8888/MenuMate/public/sales.php`

### QRコード
各テーブル用のQRコードは`assets/img/qr/`ディレクトリに配置されています。

## ディレクトリ構成

```
MenuMate/
├── assets/          # 静的ファイル（CSS、JS、画像）
│   ├── css/        # スタイルシート
│   ├── js/         # JavaScriptファイル
│   ├── img/        # 画像ファイル
│   └── adminer/    # データベース管理ツール
├── functions/       # PHP関数ファイル
├── parts/          # 共通パーツ（ヘッダー、フッターなど）
├── public/         # 公開ページ
├── sql/            # データベースSQLファイル
└── README.md       # このファイル
```

## 機能詳細

### セッション管理
- テーブルIDはセッションで管理されます
- セッション有効期限は6時間（21600秒）に設定されています

### 注文フロー
1. QRコードを読み取り、テーブルIDを取得
2. メニューから商品を選択
3. オプションを選択（必要な場合）
4. カートに追加
5. 注文内容を確認
6. 注文を送信
7. 店舗側で注文を確認・提供
8. 会計処理

### 注文ステータス
- **0**: 提供待ち
- **1**: 会計待ち
- **2**: 会計済み（想定）

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。

