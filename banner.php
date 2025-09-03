<?php
// SkillzUp - admin/banner.php (Add/Edit/Delete banners)
require_once __DIR__ . '/../common/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'create') {
    $link = trim($_POST['link'] ?? '');
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
      $error = 'Image required';
    } else {
      $up = upload_file($_FILES['image'], ['image/jpeg','image/png','image/webp'], __DIR__ . '/../uploads/images');
      if (!isset($up['ok']) || !$up['ok']) { $error = $up['error'] ?? 'Upload error'; }
      else {
        $img = '/uploads/images/' . $up['name'];
        db()->prepare('INSERT INTO banners (image, link) VALUES (?,?)')->execute([$img, $link ?: null]);
      }
    }
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT image FROM banners WHERE id=?'); $stmt->execute([$id]); $b = $stmt->fetch();
    if ($b) {
      $path = $_SERVER['DOCUMENT_ROOT'] . $b['image'];
      if (is_file($path)) @unlink($path);
      db()->prepare('DELETE FROM banners WHERE id=?')->execute([$id]);
    }
  }
  header('Location: ' . $_SERVER['REQUEST_URI']); exit;
}

$list = db()->query('SELECT * FROM banners ORDER BY id DESC')->fetchAll();

include __DIR__ . '/common/header.php';
?>
<h1 class="text-lg font-semibold mb-3">Banners</h1>

<div class="grid md:grid-cols-3 gap-4">
  <div class="card p-4 rounded">
    <h2 class="font-medium mb-2">Add Banner</h2>
    <form method="post" enctype="multipart/form-data" class="space-y-2">
      <input type="hidden" name="action" value="create">
      <input type="url" name="link" placeholder="Link (optional)" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100">
      <input type="file" name="image" accept="image/*" class="w-full text-sm" required>
      <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Save</button>
      <p class="text-xs text-slate-400">Use 16:9 images for best results.</p>
    </form>
  </div>
  <div class="md:col-span-2 card p-4 rounded">
    <h2 class="font-medium mb-2">Banner List</h2>
    <div class="grid md:grid-cols-2 gap-3">
      <?php foreach ($list as $b): ?>
        <div class="rounded overflow-hidden border border-slate-800">
          <img src="<?= e($b['image']) ?>" alt="" class="w-full aspect-video object-cover">
          <div class="p-3">
            <div class="text-xs text-slate-400 break-all"><?= e($b['link'] ?? '') ?></div>
            <form method="post" class="mt-2" onsubmit="return confirm('Delete banner?')">
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button class="px-3 py-1 rounded bg-red-700 text-white text-xs">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$list): ?><p class="text-slate-400 text-sm">No banners yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
