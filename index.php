<?php
// SkillzUp - index.php (Home)
require_once __DIR__ . '/common/config.php';

// Query banners and latest courses
$banners = db()->query('SELECT * FROM banners ORDER BY id DESC LIMIT 5')->fetchAll() ?: [];
$courses = db()->query('SELECT * FROM courses WHERE is_active=1 ORDER BY id DESC LIMIT 12')->fetchAll() ?: [];

include __DIR__ . '/common/header.php';
?>
<div class="mb-4">
  <form action="/course.php" method="get">
    <input name="q" type="text" placeholder="Search courses..." class="w-full px-4 py-3 rounded bg-slate-800 text-slate-100" />
  </form>
</div>

<?php if ($banners): ?>
<div class="relative overflow-hidden rounded-lg mb-4">
  <div id="slider" class="flex transition-transform duration-500">
    <?php foreach ($banners as $b): ?>
      <a href="<?= $b['link'] ? e($b['link']) : '#' ?>" class="min-w-full block">
        <img src="<?= e($b['image']) ?>" alt="Banner" class="w-full aspect-video object-cover">
      </a>
    <?php endforeach; ?>
  </div>
</div>
<script>
  const slider = document.getElementById('slider');
  let slideIdx = 0;
  setInterval(() => {
    const count = slider.children.length;
    slideIdx = (slideIdx + 1) % count;
    slider.style.transform = `translateX(-${slideIdx * 100}%)`;
  }, 3500);
</script>
<?php endif; ?>

<h2 class="text-base font-semibold mb-2">Latest Courses</h2>
<div class="flex gap-3 overflow-x-auto no-scrollbar pb-2">
  <?php foreach ($courses as $c): ?>
  <a href="/course_detail.php?id=<?= (int)$c['id'] ?>" class="card rounded-lg overflow-hidden min-w-[70%]">
    <img src="<?= e($c['thumbnail'] ?: '/placeholder.jpg') ?>" alt="<?= e($c['title']) ?>" class="w-full aspect-video object-cover">
    <div class="p-3">
      <div class="text-sm font-medium line-clamp-2"><?= e($c['title']) ?></div>
      <div class="mt-1 text-sky-400 font-semibold text-sm">₹<?= money((float)$c['price']) ?>
        <?php if ((float)$c['mrp'] > (float)$c['price']): ?>
          <span class="text-slate-400 line-through text-xs ml-2">₹<?= money((float)$c['mrp']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
