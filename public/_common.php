<?php
### 同じ関数が別ファイルで定義されていた時にエラーにならないようにする
if (!function_exists('db')) {
  function db(): PDO {
    ### 1回作ったPDO接続を使いまわす
    ### static にしておくとで、この関数の中で値を保持できるように
    static $pdo=null; 
    ### returnで接続済みならそのまま返す
    if ($pdo) return $pdo;
    ### ここはDBからデータを取得している
    $host=getenv('DB_HOST') ?: 'mysql';
    $name=getenv('DB_NAME') ?: 'example_db';
    $user=getenv('DB_USER') ?: 'root';
    $pass=getenv('DB_PASS') ?: '';
    $pdo=new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4",$user,$pass,[
      ### sqlエラーが起きたら表示する
      PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
      ### fetch() した時に連想配列で取り出せるようにする
      PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
    ]);
    ### 接続したPDOを返す
    return $pdo;
  }
}
### 同じ名前の e関数 がなければ作る
  if (!function_exists('e')) {
  function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
### session_start_once関数 がなければ作る
  if (!function_exists('session_start_once')) {
### セッションがまだ始まっていない時だけ session_start() をする
  function session_start_once(): void { if (session_status()!==PHP_SESSION_ACTIVE) session_start(); }
}
### login_required関数 がなければ作る
  if (!function_exists('login_required')) {
 ### ログインしていない人をログイン画面に飛ばすための関数
  function login_required(): void {
   ### まずセッションを開始する
    session_start_once();
    ### セッションの中に login_user_id が無ければ未ログインと判定
    if (empty($_SESSION['login_user_id'])) { header('Location:/login.php'); exit; }
  }
}
if (!function_exists('current_user')) {
  ### 今ログインしているユーザーの情報を取得する関数
  ### ログインしていなければ null を返す
  function current_user(): ?array {
    session_start_once();
    ### login_user_id がなければログインしていないので null
    if (empty($_SESSION['login_user_id'])) return null;
    ### usersテーブルから、ログイン中ユーザーの情報を探すSQLを準備
    $st=db()->prepare('SELECT * FROM users WHERE id=:id');
    ### :id に セッションの login_user_id を入れて実行
    $st->execute([':id'=>$_SESSION['login_user_id']]);
    ### 1件取り出す
    $u=$st->fetch(); return $u ?: null;
  }
}

if (!function_exists('save_base64_image')) {
### Base64形式の画像データを保存する関数
### 成功したらファイル名、失敗したら null を返す

  function save_base64_image(string $dataUrl): ?string {
    ### data:image/png;base64, などの形式かチェック
    ### png / jpg / jpeg / gif 以外なら保存しない
    if (!preg_match('#^data:image/(png|jpe?g|gif);base64,#i', $dataUrl)) return null;
    ### 先頭の data:image/...;base64, の部分を削除して
    ### 純粋なBase64文字列だけを取り出す
    $base64 = preg_replace('#^data:image/[^;]+;base64,#i', '', $dataUrl);
    ### Base64文字列をバイナリデータに変換する
    $bin = base64_decode($base64, true);
    ### 変換に失敗したら null
    if ($bin === false) return null;

    $dir = '/var/www/upload/image';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    $filename = sprintf('%d%s.png', time(), bin2hex(random_bytes(12)));
    $path = $dir . '/' . $filename;
    if (file_put_contents($path, $bin) === false) return null;
    return $filename;
  }
}


if (!function_exists('save_multiple_base64_images')) {
### 複数枚のBase64画像をまとめて保存する関数
### $limit で保存枚数の上限を決める（初期値は4枚）
  function save_multiple_base64_images(array $dataUrls, int $limit=4): array {
    $out = [];
    $n = 0;
    foreach ($dataUrls as $url) {
      if ($n >= $limit) break;
      ### 文字列に直して前後の空白を消す
      $url = trim((string)$url);
      if ($url === '') continue;
      $fn = save_base64_image($url);
      if ($fn) { $out[] = $fn; $n++; }
    }
    return $out;
  }
}
