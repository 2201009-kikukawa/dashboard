# 簡易掲示板システム

React (フロントエンド) + PHP (バックエンド) + PostgreSQL で構築された掲示板アプリケーションです。

## 機能

- ユーザー登録・ログイン
- メッセージ投稿（テキスト・画像対応）
- メッセージ一覧表示

## 技術スタック

- **フロントエンド**: React
- **バックエンド**: PHP + Nginx
- **データベース**: PostgreSQL
- **開発環境**: Docker Compose
- **本番環境**: Vercel (フロントエンド) + Render (バックエンド) + Supabase (データベース)

## ローカル開発環境のセットアップ

1. リポジトリをクローン

```bash
git clone <repository-url>
cd dashboard
```

2. Docker Compose で起動

```bash
docker-compose up --build
```

3. アクセス

- フロントエンド: http://localhost:3000
- バックエンド API: http://localhost:3001

## データベース構造

### users テーブル

- id (SERIAL PRIMARY KEY)
- username (VARCHAR UNIQUE)
- password (VARCHAR)

### messages テーブル

- id (SERIAL PRIMARY KEY)
- user_id (INTEGER, FOREIGN KEY)
- content (TEXT)
- image_path (VARCHAR, nullable)
- created_at (TIMESTAMP)

## デプロイ

詳細は `gemini.md` の仕様書を参照してください。

### 必要な環境変数

**バックエンド (Render)**

- `DATABASE_URL`: Supabase のデータベース接続 URL

**フロントエンド (Vercel)**

- `REACT_APP_API_BASE_URL`: デプロイされたバックエンドの URL
