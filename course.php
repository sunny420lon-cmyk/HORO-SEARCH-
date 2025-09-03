<?php
// SkillzUp - admin/course.php (Add/Edit/Delete Courses, AJAX form, click to manage)
require_once __DIR__ . '/../common/config.php';
require_admin();

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'create' || $action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $mrp = (float)($_POST['mrp'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $cat = (int)($_POST['category_id'] ?? 0);
    $thumbPath = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['size'] > 0) {
      $up = upload_file($_FILES['thumbnail'], ['image/jpeg','image/png','image/webp'], __DIR__ . '/../uploads/images');
      if (!$up['ok']) { json_response(['ok'=>false,'error'=>$up['error']], 400); }
      $thumbPath = '/uploads/images/' . $up['name'];
    }
    if ($action === 'create') {
      $stmt = db()->prepare('INSERT INTO courses (category_id, title, description, price, mrp, thumbnail, is_active) VALUES (?,?,?,?,?,?,1)');
      $stmt->execute([$cat, $title, $desc, $price, $mrp, $thumbPath]);
    } else {
      if ($thumbPath) {
        $stmt = db()->prepare('UPDATE courses SET category_id=?, title=?, description=?, price=?, mrp=?, thumbnail=? WHERE id=?');
        $stmt->execute([$cat,$title,$desc,$price,$mrp,$thumbPath,$id]);
      } else {
        $stmt = db()->prepare('UPDATE courses SET category_id=?, title=?, description=?, price=?, mrp=? WHERE id=?');
        $stmt->execute([$cat,$title,$desc,$price,$mrp,$id]);
      }
    }
    json_response(['ok'=>true]);
  } elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare('DELETE FROM courses WHERE id=?')->execute([$id]);
    json_response(['ok'=>true]);
  }
  json_response(['ok'=>false,'error'=>'Unknown action'], 400);
}

$courses = db()->query('SELECT c.*, (SELECT COUNT(*) FROM chapters ch WHERE ch.course_id=c.id) chCount FROM courses c ORDER BY id DESC')->fetchAll();
$cats = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();

include __DIR__ . '/common/header.php';
?>
<div class="grid md:grid-cols-3 gap-4">
  <div class="md:col-span-1 card p-4 rounded">
    <h2 class="font-semibold mb-3">Add / Edit Course</h2>
    <form id="courseForm" class="space-y-2" enctype="multipart/form-data">
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="id" value="0">
      <input name="title" placeholder="Title" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
      <select name="category_id" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100">
        <option value="0">No Category</option>
        <?php foreach ($cats as $cat): ?>
          <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="flex gap-2">
        <input name="mrp" type="number" step="0.01" placeholder="MRP" class="w-1/2 px-3 py-2 rounded bg-slate-800 text-slate-100">
        <input name="price" type="number" step="0.01" placeholder="Price" class="w-1/2 px-3 py-2 rounded bg-slate-800 text-slate-100">
      </div>
      <textarea name="description" placeholder="Description" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100"></textarea>
      <input type="file" name="thumbnail" accept="image/*" class="w-full text-sm">
      <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Save</button>
      <p id="courseMsg" class="text-sm text-emerald-400"></p>
    </form>
  </div>
  <div class="md:col-span-2 card p-4 rounded">
    <h2 class="font-semibold mb-3">Courses</h2>
    <div class="grid md:grid-cols-2 gap-3">
      <?php foreach ($courses as $c): ?>
        <div class="rounded overflow-hidden border border-slate-800">
          <img src="<?= e($c['thumbnail'] ?: '/placeholder.svg') ?>" class="w-full aspect-video object-cover" alt="">
          <div class="p-3">
            <div class="font-medium"><?= e($c['title']) ?></div>
            <div class="text-xs text-slate-400 mb-2"><?= (int)$c['chCount'] ?> chapters</div>
            <div class="flex gap-2">
              <a href="/admin/chapter.php?course=<?= (int)$c['id'] ?>" class="px-3 py-1 rounded bg-emerald-700 text-white text-xs">Manage</a>
              <button data-id="<?= (int)$c['id'] ?>" data-edit='<?= e(json_encode($c)) ?>' class="px-3 py-1 rounded bg-sky-700 text-white text-xs editBtn">Edit</button>
              <button data-id="<?= (int)$c['id'] ?>" class="px-3 py-1 rounded bg-red-700 text-white text-xs delBtn">Delete</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<script>
  const form = document.getElementById('courseForm');
  const msg = document.getElementById('courseMsg');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.textContent = '';
    const fd = new FormData(form);
    const res = await fetch(location.href, { method:'POST', body: fd });
    const data = await res.json();
    if (data.ok) { msg.textContent = 'Saved'; setTimeout(()=>location.reload(), 600); } else { msg.textContent = data.error || 'Error'; }
  });

  document.querySelectorAll('.editBtn').forEach(btn => btn.addEventListener('click', () => {
    const c = JSON.parse(btn.dataset.edit);
    form.action.value = 'update';
    form.id.value = c.id;
    form.title.value = c.title;
    form.category_id.value = c.category_id || 0;
    form.mrp.value = c.mrp;
    form.price.value = c.price;
    form.description.value = c.description || '';
    window.scrollTo({top:0, behavior:'smooth'});
  }));
  document.querySelectorAll('.delBtn').forEach(btn => btn.addEventListener('click', async () => {
    if (!confirm('Delete course?')) return;
    const fd = new FormData(); fd.append('action','delete'); fd.append('id', btn.dataset.id);
    const res = await fetch(location.href, { method:'POST', body: fd }); const data = await res.json();
    if (data.ok) location.reload();
  }));
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>
