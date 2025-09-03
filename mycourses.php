<?php
// SkillzUp - mycourses.php
require_once __DIR__ . '/common/config.php';
require_user();

$stmt = db()->prepare('
  SELECT c.* FROM purchases p 
  JOIN courses c ON c.id=p.course_id 
  WHERE p.user_id=? ORDER BY p.id DESC
');
$stmt->execute([ (int)current_user()['id'] ]);
$courses = $stmt->fetchAll();

include __DIR__ . '/common/header.php';
?>
<h1 class="text-base font-semibold mb-3">My Courses</h1>
<div class="grid grid-cols-1 gap-3">
  <?php foreach ($courses as $c): ?>
  <div class="card rounded overflow-hidden">
    <img src="<?= e($c['thumbnail'] ?: '/placeholder.svg') ?>" alt="<?= e($c['title']) ?>" class="w-full aspect-video object-cover">
    <div class="p-3">
      <div class="text-sm font-medium"><?= e($c['title']) ?></div>
      <a href="/watch.php?course=<?= (int)$c['id'] ?>" class="mt-2 inline-block px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium">Start</a>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (!$courses): ?>
    <p class="text-slate-400">No purchases yet.</p>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
