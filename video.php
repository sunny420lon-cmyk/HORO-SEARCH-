<?php
// SkillzUp - admin/video.php (MP4 uploads with progress, list & delete)
require_once __DIR__ . '/../common/config.php';
require_admin();

$chapterId = (int)($_GET['chapter'] ?? 0);
$ch = db()->prepare('SELECT ch.*, c.title as course_title FROM chapters ch JOIN courses c ON c.id=ch.course_id WHERE ch.id=?');
$ch->execute([$chapterId]);
$chapter = $ch->fetch();
if (!$chapter) { header('Location: /admin/course.php'); exit; }

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT file_path FROM videos WHERE id=? AND chapter_id=?');
    $stmt->execute([$id, $chapterId]);
    $v = $stmt->fetch();
    if ($v) {
      // attempt unlink if local
      $path = $_SERVER['DOCUMENT_ROOT'] . $v['file_path'];
      if (is_file($path)) @unlink($path);
      db()->prepare('DELETE FROM videos WHERE id=? AND chapter_id=?')->execute([$id, $chapterId]);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']); exit;
  } elseif (($_POST['action'] ?? '') === 'upload') {
    // AJAX upload handler: MP4 only
    header('Content-Type: application/json');
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
      json_response(['ok'=>false,'error'=>'Upload error'], 400);
    }
    $up = upload_file($_FILES['file'], ['video/mp4'], __DIR__ . '/../uploads/videos');
    if (!$up['ok']) json_response(['ok'=>false,'error'=>$up['error']], 400);

    $title = trim($_POST['title'] ?? pathinfo($up['name'], PATHINFO_FILENAME));
    $sort = (int)($_POST['sort_order'] ?? 0);
    $relPath = '/uploads/videos/' . $up['name'];
    $stmt = db()->prepare('INSERT INTO videos (chapter_id, title, file_path, sort_order) VALUES (?,?,?,?)');
    $stmt->execute([$chapterId, $title, $relPath, $sort]);
    json_response(['ok'=>true, 'file'=>$relPath]);
  }
}

// List videos
$list = db()->prepare('SELECT * FROM videos WHERE chapter_id=? ORDER BY sort_order, id');
$list->execute([$chapterId]);
$videos = $list->fetchAll();

include __DIR__ . '/common/header.php';
?>
<h1 class="text-lg font-semibold mb-3">Videos - <?= e($chapter['course_title']) ?> / <?= e($chapter['title']) ?></h1>

<div class="grid md:grid-cols-2 gap-4">
  <div class="card p-4 rounded">
    <h2 class="font-medium mb-2">Upload MP4</h2>
    <form id="uploadForm" class="space-y-2">
      <input type="hidden" name="action" value="upload" />
      <input type="text" name="title" placeholder="Title" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100">
      <input type="number" name="sort_order" placeholder="Order" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" value="0">
      <input type="file" name="file" accept="video/mp4" class="w-full text-sm" required>
      <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium" type="submit">Upload</button>
      <div class="w-full h-2 bg-slate-800 rounded overflow-hidden hidden" id="barWrap">
        <div id="bar" class="h-2 bg-sky-500" style="width:0%"></div>
      </div>
      <p id="upMsg" class="text-sm mt-1"></p>
    </form>
  </div>

  <div class="card p-4 rounded">
    <h2 class="font-medium mb-2">Uploaded Videos</h2>
    <div class="space-y-2">
      <?php foreach ($videos as $v): ?>
        <div class="rounded border border-slate-800 p-3">
          <div class="font-medium text-sm"><?= e($v['title']) ?></div>
          <div class="text-xs text-slate-400 break-all"><?= e($v['file_path']) ?></div>
          <form method="post" class="mt-2" onsubmit="return confirm('Delete video?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
            <button class="px-3 py-1 rounded bg-red-700 text-white text-xs">Delete</button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (!$videos): ?>
        <p class="text-slate-400 text-sm">No videos yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  const form = document.getElementById('uploadForm');
  const upMsg = document.getElementById('upMsg');
  const barWrap = document.getElementById('barWrap');
  const bar = document.getElementById('bar');
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    upMsg.textContent = '';
    barWrap.classList.remove('hidden');
    bar.style.width = '0%';

    const fd = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', location.href);
    xhr.upload.addEventListener('progress', (evt) => {
      if (evt.lengthComputable) {
        const pct = Math.round((evt.loaded / evt.total) * 100);
        bar.style.width = pct + '%';
      }
    });
    xhr.onreadystatechange = () => {
      if (xhr.readyState === 4) {
        try {
          const data = JSON.parse(xhr.responseText);
          if (data.ok) {
            upMsg.className = 'text-sm text-emerald-400';
            upMsg.textContent = 'Uploaded';
            setTimeout(()=>location.reload(), 600);
          } else {
            upMsg.className = 'text-sm text-red-400';
            upMsg.textContent = data.error || 'Upload failed';
          }
        } catch(e) {
          upMsg.className = 'text-sm text-red-400';
          upMsg.textContent = 'Upload error';
        }
      }
    };
    xhr.send(fd);
  });
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>
