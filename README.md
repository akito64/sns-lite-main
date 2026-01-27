タイムライン　期末課題

sudo dnf update -y
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

アクセス:　http://3.237.88.149/login.php
