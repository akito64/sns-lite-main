タイムライン(PHP、NGinx、sql、Docker)

某 X みたいな感じのやつを作成

１．まずDockerおよびdocker composeのインストール方法
sudo dnf update -y,

sudo dnf install -y git

sudo dnf install -y docker

sudo systemctl enable docker

sudo systemctl start docker

sudo mkdir -p /usr/local/lib/docker/cli-plugins/

sudo curl -SL https://github.com/docker/compose/releases/download/v2.36.0/docker-compose-linux-x86_64 -o /usr/local/lib/docker/cli-plugins/docker-compose

sudo chmod +x /usr/local/lib/docker/cli-plugins/docker-compose

sudo usermod -aG docker ec2-user

再ログイン後に確認:

docker --version
docker compose version

gitからソースコードを取得

git clone https://github.com/akito64/sns-lite-main.git
cd sns-lite-main

# ビルド  起動
docker compose up -d --build

データベースの初期化

docker compose exec -T mysql mysql -uroot example_db < db/schema.sql

DBの作成
docker compose exec mysql mysql -uroot -e 'CREATE DATABASE IF NOT EXISTS example_db;

スキーマを入れる
docker compose exec -T mysql sh -lc 'mysql -uroot example_db' < db/schema.sql

DBがあるかの確認
docker compose exec mysql mysql -uroot -e 'SHOW DATABASES LIKE "example_db";'


テーブルが入っているかの確認
docker compose exec -it mysql mysql -uroot example_db -e 'SHOW TABLES;'


代表テーブルにデータが入ってるかの確認
docker compose exec mysql mysql -uroot example_db -e 'SELECT COUNT(*) FROM users;'



画像保存ディレクトリを作ってそれに権限を付与
docker compose exec php sh -lc '
  mkdir -p /var/www/upload/image &&
  chown -R www-data:www-data /var/www/upload &&
  chmod -R 775 /var/www/upload &&
  ls -ld /var/www/upload /var/www/upload/image
'


怖いしNginxから直配信の確認
docker compose exec nginx sh -lc 'nginx -T | sed -n "/location \\^~ \\/image\\//,+8p"'

念のためリロード
docker compose exec nginx nginx -s reload



アクセス:　http://3.236.40.98/login.php
