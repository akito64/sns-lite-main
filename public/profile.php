<?php require __DIR__.'/_common.php';
login_required();
$me = current_user();
$dbh = db();

/* フォローしてるやつを表示 */
$target_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$me['id'];
$u = $dbh->prepare('SELECT * FROM users WHERE id=:id');
$u->execute([':id'=>$target_id]);
$user = $u->fetch();
if(!$user){ http_response_code(404); echo 'ユーザーが見つかりません'; exit; }

/* 自分がそいつをフォローしてるか確認するとこ */
$following = false;
if ($me['id'] !== $target_id) {
  $st = $dbh->prepare('SELECT 1 FROM user_relationships WHERE follower_user_id=:f AND followee_user_id=:e');
  $st->execute([':f'=>$me['id'], ':e'=>$target_id]);
  $following = (bool)$st->fetch();
}

/* 選んだやつの投稿が見れるようになる！ */
$posts = $dbh->prepare(
  'SELECT b.*, u.name user_name, u.icon_filename user_icon_filename
     FROM bbs_entries b
     JOIN users u ON b.user_id=u.id
    WHERE b.user_id=:uid
    ORDER BY b.created_at DESC
    LIMIT 100'
);
$posts->execute([':uid'=>$target_id]);
$entries = $posts->fetchAll();
?>
<?php require __DIR__.'/_bootstrap.php'; ?>
<p><a href="/timeline.php">← タイムライン</a> / <a href="/users.php">会員一覧</a></p>

<h2>プロフィール</h2>
<p>
  <?php if(!empty($user['icon_filename'])): ?>
    <img src="/image/<?= e($user['icon_filename']) ?>" style="height:2.4em;width:2.4em;border-radius:50%;object-fit:cover;vertical-align:middle;">
  <?php endif; ?>
  <strong><?= e($user['name']) ?></strong> (ID: <?= e($user['id']) ?>)
</p>

<?php if ($me['id'] !== $user['id']): ?>
  <form method="post" action="/follow_toggle.php">
    <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
    <button><?= $following ? 'フォロー解除' : 'フォロー' ?></button>
  </form>
<?php else: ?>
  <p><a href="/setting/index.php">プロフィール設定</a></p>
<?php endif; ?>

<hr>
<h3>投稿</h3>
<?php foreach($entries as $e): ?>
  <div style="border-bottom:1px solid #ccc; padding:.6em 0;">
    <div>
      <?= e($e['created_at']) ?>
    </div>
    <div><?= nl2br(e($e['body'])) ?></div>
    <?php if(!empty($e['image_filename'])): ?>
      <div><img src="/image/<?= e($e['image_filename']) ?>" style="max-height:10em"></div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
