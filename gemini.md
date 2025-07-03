簡易掲示板サイト構築仕様書（React/PHP/Docker）
概要
本仕様書は、React（フロントエンド）、PHP（バックエンド）、および Docker を用いて簡易掲示板サイトを構築し、無料で利用可能なデータベースとクラウドサービスにデプロイするまでの一連の工程を定義します。開発からデプロイまでを一貫してカバーし、関係者がスムーズにプロジェクトを進められるよう指針を提供します。

使用技術・サービス
フロントエンドフレームワーク: React

バックエンド言語: PHP

Web サーバー (PHP): Nginx + PHP-FPM (Docker コンテナとして稼働)

コンテナ技術: Docker, Docker Compose

開発環境データベース: PostgreSQL (Docker コンテナとして稼働)

本番環境データベース: Supabase (PostgreSQL 互換の無料プランを利用)

フロントエンドデプロイサービス: Vercel (無料プランを利用)

バックエンドデプロイサービス: Render (無料プランを利用) または Fly.io (無料プランを利用)

1. プロジェクトの初期設定
   1.1. プロジェクトルートディレクトリの作成
   プロジェクト全体の基盤となるディレクトリを任意の名称で作成します。

1.2. Docker Compose ファイルの定義
プロジェクトルートに docker-compose.yml を配置し、以下のサービスを定義します。

db サービス:

PostgreSQL の公式イメージを使用します。

環境変数でデータベース名、ユーザー名、パスワードを設定します。

ローカルのポート (5432) をコンテナのポート (5432) にマッピングします。

永続化のためにボリュームを定義します。

backend サービス:

backend ディレクトリ内の Dockerfile を基に PHP-FPM イメージをビルドします。

backend ディレクトリ内のコードをコンテナにボリュームマウントします。

app (Nginx) サービスへの依存関係を定義します。

備考: PHP-FPM は直接ポートを公開せず、Nginx を介してリクエストを受け取ります。

app (Nginx) サービス:

Nginx の公式イメージを使用します。

nginx ディレクトリ内の設定ファイル (nginx.conf) をコンテナにマウントします。

ローカルのポート (3001) をコンテナのポート (80) にマッピングします。

backend サービスへの依存関係を定義し、Nginx が PHP-FPM よりも後に起動するようにします。

backend ディレクトリのコードを Nginx コンテナにもボリュームマウントし、Web ルートとして設定します。

frontend サービス:

frontend ディレクトリ内の Dockerfile を基にイメージをビルドします。

ローカルのポート (3000) をコンテナのポート (3000) にマッピングします。

環境変数としてバックエンド API のベース URL（REACT_APP_API_BASE_URL）を設定します。

app (Nginx) サービスへの依存関係を定義します。

開発中のコード変更をリアルタイムで反映させるため、ボリュームマウントを行います。

2. バックエンド (PHP) のセットアップ
   2.1. PHP プロジェクトの初期化
   backend ディレクトリを作成し、その中で PHP プロジェクトを初期化します。必要に応じて Composer を使用し、データベース接続（PDO または ORM）や環境変数管理のためのライブラリ（例: vlucas/phpdotenv）をインストールします。

2.2. PHP アプリケーションの構築
メインアプリケーションファイル (index.php など) の作成:

環境変数からデータベース接続情報を取得します。

PDO などを用いて PostgreSQL データベースへの接続を確立します。

CORS の設定: フロントエンドからのリクエストを許可するために、適切な HTTP ヘッダー（Access-Control-Allow-Origin など）を設定します。

ルーティングと API エンドポイントの実装:

メッセージ取得 (GET /messages): データベースからすべてのメッセージを取得し、新しい順にソートして JSON 形式で返却します。

メッセージ投稿 (POST /messages): クライアントから送信されたユーザー名と内容をリクエストボディから取得し、データベースに保存します。保存後、新しく作成されたメッセージの情報を JSON 形式で返却します。入力値のバリデーションも行います。

適切な HTTP ステータスコード（例: 200 OK, 201 Created, 400 Bad Request, 500 Internal Server Error）を返します。

2.3. バックエンド用 Dockerfile の作成
ベースイメージ: php:8.x-fpm-alpine などの PHP-FPM イメージを使用します。

作業ディレクトリ: コンテナ内の作業ディレクトリを設定します。

PHP 拡張機能のインストール: PostgreSQL 接続に必要な pdo_pgsql などの PHP 拡張機能をインストールします。

依存関係のインストール: Composer があれば composer install を実行します。

ソースコードのコピー: プロジェクトの全ソースコードを作業ディレクトリにコピーします。

2.4. Nginx 設定ファイルの作成
nginx ディレクトリを作成し、nginx.conf を配置します。

サーバーブロックの定義:

ポート (80) でリッスンするように設定します。

フロントエンドからのリクエストを処理するための location / ブロックを設定します。

PHP スクリプト（.php ファイル）のリクエストを PHP-FPM (backend サービス) に転送するように設定します。

CORS ヘッダーを適切に設定します。

3. フロントエンド (React) のセットアップ
   3.1. React プロジェクトの初期化
   frontend ディレクトリを作成し、その中で Create React App を使用して React プロジェクトを初期化します。
   必要なパッケージ（axios など）をインストールします。

3.2. React アプリケーションの構築
メインコンポーネント (App.js など) の作成:

React の useState および useEffect フックを使用して、状態管理を行います。

メッセージの表示: バックエンド API (/messages) からメッセージ一覧を取得し、表示します。メッセージは投稿日時が新しい順に並べます。

メッセージ投稿フォーム: ユーザー名とメッセージ内容を入力するフォームを作成します。

投稿処理: フォームが送信された際、入力されたユーザー名とメッセージ内容をバックエンド API (/messages) に POST リクエストで送信します。投稿成功後、フォームをクリアし、メッセージ一覧を再取得して表示を更新します。

API ベース URL: 環境変数 (REACT_APP_API_BASE_URL) からバックエンド API の URL を取得し、axios リクエストに使用します。

3.3. フロントエンド用スタイルの定義
App.css などに基本的な CSS スタイルを定義し、掲示板のレイアウトとデザインを整えます。

3.4. フロントエンド用 Dockerfile の作成
ベースイメージ: Node.js の安定版イメージを使用します。

作業ディレクトリ: コンテナ内の作業ディレクトリを設定します。

依存関係のインストール: package.json および package-lock.json (または yarn.lock) をコピーし、必要な Node.js パッケージをインストールします。

ソースコードのコピー: プロジェクトの全ソースコードを作業ディレクトリにコピーします。

ポート公開: フロントエンドがリッスンするポート (3000) を公開します。

起動コマンド: アプリケーションを起動するコマンド (npm start など) を定義します。

4. ローカル環境での起動 (Docker Compose)
   プロジェクトルートディレクトリで docker-compose up --build コマンドを実行します。
   これにより、定義されたすべてのサービス（PostgreSQL、PHP-FPM、Nginx、React）がビルドされ、起動します。

フロントエンドのアクセス URL: http://localhost:3000

バックエンドのポート: http://localhost:3001 (フロントエンドから利用)

PostgreSQL のポート: localhost:5432

ローカルの PostgreSQL データベースに対し、以下の SQL で messages テーブルを作成します。

SQL

CREATE TABLE messages (
id SERIAL PRIMARY KEY,
username VARCHAR(255) NOT NULL,
content TEXT NOT NULL,
created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
ブラウザでフロントエンドの URL にアクセスし、掲示板機能が正常に動作することを確認します。

5. 無料クラウドサービスへのデプロイ
   5.1. データベースのセットアップ (Supabase)
   Supabase アカウントの作成: Supabase の公式ウェブサイトからアカウントを新規作成します。

新しいプロジェクトの作成: Supabase ダッシュボードで新しいプロジェクトを作成し、データベースパスワードとリージョンを設定します。

データベース接続情報の取得: 作成したプロジェクトの「Project Settings」→「Database」セクションから、データベース接続 URI をコピーします。この URI が本番環境のバックエンドからデータベースへ接続するための情報となります。

テーブルの作成: Supabase ダッシュボードの「SQL Editor」を利用し、ローカル環境で作成した messages テーブルと同じスキーマ定義でテーブルを作成します。

5.2. バックエンドのデプロイ (Render または Fly.io)
Render を例に説明します。

Render アカウントの作成: Render の公式ウェブサイトからアカウントを新規作成します。

GitHub リポジトリの準備: バックエンドのソースコード（backend ディレクトリと nginx ディレクトリの内容）を専用の GitHub リポジトリにプッシュします。環境変数ファイルは含めないように .gitignore に追加します。

Render Web Service の作成: Render ダッシュボードで「New」→「Web Service」を選択し、GitHub リポジトリと連携させます。

Root Directory: GitHub リポジトリのルート（通常、PHP と Nginx の設定を適切に配置します）

Runtime: Docker を選択し、Dockerfile を使用してビルドするように設定します。Nginx と PHP-FPM を組み合わせた環境を想定し、単一の Docker イメージにパッケージングするか、または Render のサービス間連携機能を使用することを検討します（無料プランの制限に注意）。

Build Command: docker build -t app . など（Nginx と PHP-FPM をまとめてビルドする Dockerfile を想定）

Start Command: nginx -g 'daemon off;' など（Nginx がフォアグラウンドで起動し、PHP-FPM へのプロキシを設定）

Environment Variables: 取得した Supabase のデータベース接続 URI を DATABASE_URL という環境変数として設定します。

Instance Type: Free を選択します。

デプロイの確認: デプロイが完了すると、Render から発行される公開 URL が提供されます。この URL がフロントエンドからバックエンド API へアクセスするためのベース URL となります。

5.3. フロントエンドのデプロイ (Vercel)
Vercel アカウントの作成: Vercel の公式ウェブサイトからアカウントを新規作成します。

GitHub リポジトリの準備: フロントエンドのソースコード（frontend ディレクトリの内容）を専用の GitHub リポジトリにプッシュします。

Vercel プロジェクトの作成: Vercel ダッシュボードで「Add New...」→「Project」を選択し、GitHub リポジトリと連携させます。

Root Directory: GitHub リポジトリのルートに対するフロントエンドのディレクトリパスを指定します。

Framework Preset: Create React App を選択します。

Environment Variables: Render でデプロイしたバックエンドの公開 URL を REACT_APP_API_BASE_URL という環境変数として設定します。

デプロイの確認: デプロイが完了すると、Vercel から発行される公開 URL が提供されます。この URL にアクセスし、デプロイされた掲示板サイトが正常に動作することを確認します。

6. トラブルシューティングの指針
   Docker Compose 関連:

起動ログの詳細を確認し、ポート競合や依存サービスの起動状況をチェックします。

PHP-FPM と Nginx 間の接続（fastcgi_pass の設定）が正しいか確認します。

不要な Docker リソースをクリーンアップしてから再試行します。

データベース接続関連 (バックエンド):

DATABASE_URL 環境変数の値が正確であることを複数回確認します。

Supabase のセキュリティ設定（ファイアウォールなど）が、デプロイ元からの接続を許可しているか確認します。

PHP の PostgreSQL 拡張機能が正しくインストールされているか確認します。

API 接続関連 (フロントエンド):

REACT_APP_API_BASE_URL 環境変数の値が、デプロイされたバックエンドの公開 URL と完全に一致していることを確認します。

ブラウザの開発者ツール（コンソール、ネットワークタブ）で、API リクエストの成否、エラーメッセージ、CORS エラーの有無などを詳細に調査します。PHP バックエンドでの CORS ヘッダー設定を確認します。

デプロイサービス関連 (Render/Vercel):

各サービスのデプロイログを詳細に確認し、ビルドや実行時のエラーメッセージから原因を特定します。

設定した環境変数、ビルドコマンド、スタートコマンドが正しいことを確認します。

特に PHP と Nginx をデプロイする場合、Docker イメージのビルド方法や起動コマンドが複雑になることがあるため、Render の Docker デプロイのドキュメントを詳細に確認することが重要です。

最新のコードが GitHub リポジトリにプッシュされていることを確認します。


エラーが発生したばあいには、googlesearch機能を用いてそのエラーの原因を調べ解決してください。