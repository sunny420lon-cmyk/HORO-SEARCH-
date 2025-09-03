<?php
// SkillzUp - admin/chapter.php (Add/Edit/Delete chapter per course)
require_once __DIR__ . '/../common/config.php';
require_admin();

$courseId = (int)($_GET['course'] ?? 0);
if ($courseId <= 0) { header('Location: /admin/course.php'); exit; }
$c = db()->prepare('SELECT * FROM courses WHERE id=?'); $c->execute([$courseId]); $course = $c->fetch();
if (!$course) { header('Location: /admin/course.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $ord = (int)($_POST['sort_order'] ?? 0);
    db()->prepare('INSERT INTO chapters (course_id, title, sort_order) VALUES (?,?,?)')->execute([$courseId, $title, $ord]);
    header('Location: ' . $_SERVER['REQUEST_URI']); exit;
  } elseif ($action === 'del') {
    $id = (int)($_POST['id'] ?? 0);
    db()->prepare('DELETE FROM chapters WHERE id=? AND course_id=?')->execute([$id, $courseId]);
    header('Location: ' . $_SERVER['REQUEST_URI']); exit;
  }
}

$chapters = db()->prepare('SELECT * FROM chapters WHERE course_id=? ORDER BY sort_order, id'); $chapters->execute([$courseId]); $chapters = $chapters->fetchAll();

include __DIR__ . '/common/header.php';
?>
<h1 class="text-lg font-semibold mb-3">Chapters - <?= e($course['title']) ?></h1>
<form method="post" class="card p-4 rounded mb-4 flex gap-2">
  <input type="hidden" name="action" value="add">
  <input name="title" placeholder="Chapter title" class="flex-1 px-3 py-2 rounded bg-slate-800 text-slate-100" required>
  <input name="sort_order" type="number" placeholder="Order" class="w-24 px-3 py-2 rounded bg-slate-800 text-slate-100" value="0">
  <button class="px-3 py-2 rounded bg-sky-600 hover:bg-sky-500 text-white">Add</button>
</form>

<div class="grid md:grid-cols-2 gap-3">
  <?php foreach ($chapters as $ch): ?>
  <div class="card p-3 rounded">
    <div class="flex items-center justify-between">
      <div class="font-medium"><?= e($ch['title']) ?></div>
      <form method="post" onsubmit="return confirm('Delete chapter?')">
        <input type="hidden" name="action" value="del"><input type="hidden" name="id" value="<?= (int)$ch['id'] ?>">
        <button class="text-red-400">Delete</button>
      </form>
    </div>
    <a class="text-xs text-sky-400" href="/admin/video.php?chapter=<?= (int)$ch['id'] ?>">Manage Videos</a>
  </div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
