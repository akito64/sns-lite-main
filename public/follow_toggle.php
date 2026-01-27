<?php require __DIR__.'/_common.php';
login_required();
$me = current_user();
$dbh = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /users.php'); exit;
}

$target = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if ($target <= 0 || $target === (int)$me['id']) {
  header('Location: /users.php'); exit;
}

/* フォローしてるならフォロ解　してないならフォローできるように */
$dbh->beginTransaction();
try {
  $chk = $dbh->prepare('SELECT 1 FROM user_relationships WHERE follower_user_id=:f AND followee_user_id=:e');
  $chk->execute([':f'=>$me['id'], ':e'=>$target]);
  if ($chk->fetch()) {
    $del = $dbh->prepare('DELETE FROM user_relationships WHERE follower_user_id=:f AND followee_user_id=:e');
    $del->execute([':f'=>$me['id'], ':e'=>$target]);
  } else {
    $ins = $dbh->prepare('INSERT INTO user_relationships(follower_user_id, followee_user_id) VALUES (:f, :e)');
    $ins->execute([':f'=>$me['id'], ':e'=>$target]);
  }
  $dbh->commit();
} catch (Throwable $e) {
  $dbh->rollBack();
  // エラーのとき一覧に飛ばす
}

$back = $_SERVER['HTTP_REFERER'] ?? '/users.php';
header('Location: '.$back);
