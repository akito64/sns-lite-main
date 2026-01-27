<?php require __DIR__.'/_common.php';
login_required();
$me = current_user();

$dbh = db();

/* 自分以外の会員を見るところとフォローしてるやつを見る*/
$sql = <<<SQL
SELECT
  u.id,
  u.name,
  u.icon_filename,
  EXISTS(
    SELECT 1 FROM user_relationships r
    WHERE r.follower_user_id = :me AND r.followee_user_id = u.id
  ) AS is_following
FROM users u
ORDER BY u.id ASC
SQL;
$st = $dbh->prepare($sql);
$st->execute([':me' => $me['id']]);
$users = $st->fetchAll();
?>
<?php require __DIR__.'/_bootstrap.php'; ?>
<h2>会員一覧</h2>
<p><a href="/timeline.php">← タイムラインへ戻る</a></p>

<ul style="list-style:none;padding:0">
<?php foreach ($users as $u): ?>
  <li style="margin:.5em 0; padding:.5em; border:1px solid #ddd;">
    <a href="/profile.php?user_id=<?= e($u['id']) ?>">
      <?php if(!empty($u['icon_filename'])): ?>
        <img src="/image/<?= e($u['icon_filename']) ?>" style="height:1.6em;width:1.6em;border-radius:50%;object-fit:cover;vertical-align:middle;">
      <?php endif; ?>
      <?= e($u['name']) ?> (ID: <?= e($u['id']) ?>)
    </a>

    <?php if ($u['id'] != $me['id']): ?>
      <form method="post" action="/follow_toggle.php" style="display:inline;margin-left:1em">
        <input type="hidden" name="user_id" value="<?= e($u['id']) ?>">
        <button><?= $u['is_following'] ? 'フォロー解除' : 'フォロー' ?></button>
      </form>
    <?php else: ?>
      <span style="color:#888;margin-left:1em">※自分</span>
    <?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>
