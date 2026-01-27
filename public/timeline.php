<?php
require __DIR__.'/_common.php';
login_required();
$me  = current_user();
$dbh = db();

/* 1) 投稿 */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['body'])) {
  $dbh->beginTransaction();
  try {
    // 本文
    $ins = $dbh->prepare('INSERT INTO bbs_entries(user_id, body) VALUES(:u,:b)');
    $ins->execute([':u'=>$me['id'], ':b'=>$_POST['body']]);
    $entryId = (int)$dbh->lastInsertId();

    // 画像（最大4）
    $files = [];
    if (!empty($_POST['image_base64']) && is_array($_POST['image_base64'])) {
      $files = save_multiple_base64_images($_POST['image_base64'], 4);
    }
    if ($files) {
      $imgs = $dbh->prepare('INSERT INTO bbs_entry_images(entry_id, filename, sort_no) VALUES(?,?,?)');
      foreach ($files as $i => $fn) $imgs->execute([$entryId, $fn, $i]);
    }

    $dbh->commit();
    header('Location:/timeline.php'); exit;
  } catch (Throwable $e) {
    $dbh->rollBack();
    throw $e;
  }
}

/* 2) タイムライン（自分とフォローしてるやつのみ） */
$sql = <<<SQL
SELECT b.*, u.name AS user_name, u.icon_filename AS user_icon_filename
FROM bbs_entries b
JOIN users u ON u.id=b.user_id
WHERE b.user_id = :me
   OR b.user_id IN (SELECT followee_user_id FROM user_relationships WHERE follower_user_id=:me)
ORDER BY b.created_at DESC
SQL;
$st = $dbh->prepare($sql);
$st->execute([':me'=>$me['id']]);
$entries = $st->fetchAll(PDO::FETCH_ASSOC);

// まとめて画像を上げる
$entryIds = array_column($entries, 'id');
$imagesMap = [];
if ($entryIds) {
  $in = implode(',', array_fill(0, count($entryIds), '?'));
  $imgSt = $dbh->prepare("SELECT entry_id, filename FROM bbs_entry_images WHERE entry_id IN ($in) ORDER BY sort_no, id");
  $imgSt->execute($entryIds);
  foreach ($imgSt as $r) $imagesMap[$r['entry_id']][] = $r['filename'];
}
?>
<?php require __DIR__.'/_bootstrap.php'; ?>

<div class="topnav">
  <span>現在 <?= e($me['name']) ?> (ID: <?= e($me['id']) ?>)</span>
  <nav>
    <a href="/users.php">会員一覧</a>
    <a href="/setting/index.php">設定</a>
    <a href="/logout.php">ログアウト</a>
  </nav>
</div>

<section class="card">
  <h2>投稿</h2>
  <form method="post" class="stack">
    <textarea name="body" required placeholder="いまどうしてる？" class="input"></textarea>

    <div class="stack-xs">
      <input type="file" accept="image/*" id="imageInput" multiple>
      <small>最大4枚まで。大きい画像は自動縮小されます。</small>
      <div id="thumbs" class="grid grid-2 gap-xs"></div>
    </div>

    <!-- 画像のボタン -->
    <div id="hiddenArea"></div>
    <button class="btn">送信</button>
  </form>
</section>

<section class="stack">
  <h2>タイムライン</h2>
  <?php foreach($entries as $e): ?>
    <article class="card">
      <header class="row gap-s vcenter">
        <?php if(!empty($e['user_icon_filename'])): ?>
          <img src="/image/<?= e($e['user_icon_filename']) ?>" class="avatar" alt="">
        <?php endif; ?>
        <a href="/profile.php?user_id=<?= e($e['user_id']) ?>" class="bold"><?= e($e['user_name']) ?></a>
        <span class="muted">｜<?= e($e['created_at']) ?></span>
      </header>
      <p><?= nl2br(e($e['body'])) ?></p>
        <?php
        $imgs = $imagesMap[$e['id']] ?? [];
        $n = count($imgs);
        ?>
        <?php if ($n): ?>
        <div class="imgs n<?= $n ?>">  
            <?php foreach ($imgs as $fn): ?>
            <img src="/image/<?= e($fn) ?>" alt="">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </article>
  <?php endforeach; ?>
</section>

<script>
// 画像を圧縮して４枚あげれるように
const input = document.getElementById('imageInput');
const hiddenArea = document.getElementById('hiddenArea');
const thumbs = document.getElementById('thumbs');

function resizeToBase64(file, max=1000){
  return new Promise((resolve,reject)=>{
    const fr = new FileReader();
    fr.onload = () => {
      const img = new Image();
      img.onload = () => {
        let w = img.naturalWidth, h = img.naturalHeight;
        if (w>max || h>max){
          if (w>=h){ h = Math.round(h*max/w); w = max; }
          else     { w = Math.round(w*max/h); h = max; }
        }
        const cv = document.createElement('canvas');
        cv.width = w; cv.height = h;
        cv.getContext('2d').drawImage(img,0,0,w,h);
        resolve(cv.toDataURL('image/png'));
      };
      img.onerror = reject;
      img.src = fr.result;
    };
    fr.onerror = reject;
    fr.readAsDataURL(file);
  });
}

input.addEventListener('change', async () => {
  hiddenArea.innerHTML = '';
  thumbs.innerHTML = '';
  const files = Array.from(input.files || []).filter(f=>f.type.startsWith('image/')).slice(0,4);
  for (let i=0; i<files.length; i++){
    const b64 = await resizeToBase64(files[i], 1000);
    const h = document.createElement('input');
    h.type = 'hidden';
    h.name = 'image_base64[]';
    h.value = b64;
    hiddenArea.appendChild(h);

  
    const img = document.createElement('img');
    img.src = b64; img.className = 'thumb';
    thumbs.appendChild(img);
  }
});
</script>
