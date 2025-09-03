<?php
// SkillzUp - course_detail.php
require_once __DIR__ . '/common/config.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM courses WHERE id=? AND is_active=1');
$stmt->execute([$id]);
$course = $stmt->fetch();
if (!$course) { header('Location: /course.php'); exit; }
$user = current_user();
$owned = $user ? is_purchased((int)$user['id'], (int)$course['id']) : false;
include __DIR__ . '/common/header.php';
?>
<div class="rounded overflow-hidden mb-3">
  <img src="<?= e($course['thumbnail'] ?: '/placeholder.svg') ?>" alt="<?= e($course['title']) ?>" class="w-full aspect-video object-cover">
</div>
<h1 class="text-lg font-semibold"><?= e($course['title']) ?></h1>
<div class="mt-1 text-sky-400 font-semibold">₹<?= money((float)$course['price']) ?>
  <?php if ((float)$course['mrp'] > (float)$course['price']): ?>
    <span class="text-slate-400 line-through text-sm ml-2">₹<?= money((float)$course['mrp']) ?></span>
    <span class="ml-2 text-xs text-emerald-400">
      <?= (int)round(100 - ((float)$course['price'] / max((float)$course['mrp'], 0.01)) * 100) ?>% OFF
    </span>
  <?php endif; ?>
</div>
<div class="prose prose-invert mt-3 max-w-none text-sm leading-relaxed"><?= nl2br(e((string)$course['description'])) ?></div>

<div class="sticky-bottom mt-6 mb-20">
  <?php if ($owned): ?>
    <a href="/watch.php?course=<?= (int)$course['id'] ?>" class="w-full block text-center py-3 rounded bg-emerald-600 hover:bg-emerald-500 text-white font-medium">Start Course</a>
  <?php else: ?>
    <a href="/buy.php?course=<?= (int)$course['id'] ?>" class="w-full block text-center py-3 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Buy Now</a>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
