<?php
require __DIR__.'/../_common.php';
login_required();
$me  = current_user();
$dbh = db();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $name = trim((string)($_POST['name'] ?? ''));
  if ($name === '') $name = $me['name'];

  // アイコン（任意）
  $icon = $me['icon_filename'] ?? null;
  if (!empty($_POST['icon_base64'])) {
    $b64 = $_POST['icon_base64'];

    // 256pxに縮小して保存
    $fn = save_base64_image($b64);
    if ($fn) $icon = $fn;
  }

  $st = $dbh->prepare('UPDATE users SET name=:n, icon_filename=:i WHERE id=:id');
  $st->execute([':n'=>$name, ':i'=>$icon, ':id'=>$me['id']]);

  header('Location:/timeline.php'); exit;
}
?>
<?php require __DIR__.'/../_bootstrap.php'; ?>

<div class="topnav">
  <span>設定</span>
  <nav>
    <a href="/timeline.php">タイムライン</a>
    <a href="/logout.php">ログアウト</a>
  </nav>
</div>

<section class="card stack">
  <h2>プロフィール編集</h2>
  <form method="post" class="stack">
    <label class="stack-xs">
      表示名
      <input class="input" type="text" name="name" value="<?= e($me['name']) ?>" maxlength="50">
    </label>

    <div class="stack-xs">
      <label>アイコン画像（正方形推奨・最大256pxに縮小）</label>
      <input type="file" accept="image/*" id="iconInput">
      <input type="hidden" name="icon_base64" id="iconBase64Input">
      <div id="iconPreview" class="row vcenter gap-s">
        <?php if(!empty($me['icon_filename'])): ?>
          <img src="/image/<?= e($me['icon_filename']) ?>" class="avatar" alt="">
        <?php endif; ?>
      </div>
    </div>

    <button class="btn">保存</button>
  </form>
</section>

<script>
// 256pxに収まるよう縮小して
function resizeIcon(file, max=256){
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

const iconInput = document.getElementById('iconInput');
iconInput.addEventListener('change', async () => {
  const f = iconInput.files[0]; if(!f || !f.type.startsWith('image/')) return;
  const b64 = await resizeIcon(f, 256);
  document.getElementById('iconBase64Input').value = b64;
  const pv = document.getElementById('iconPreview');
  pv.innerHTML = '';
  const img = document.createElement('img');
  img.src = b64; img.className='avatar';
  pv.appendChild(img);
});
</script>
