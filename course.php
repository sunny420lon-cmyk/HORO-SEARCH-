<?php
// SkillzUp - course.php (Listing with filters)
require_once __DIR__ . '/common/config.php';

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$sort = $_GET['sort'] ?? 'latest';
$sortSql = $sort === 'price' ? 'price ASC' : 'id DESC';

$cats = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$sql = 'SELECT * FROM courses WHERE is_active=1';
$params = [];
if ($q !== '') { $sql .= ' AND title LIKE ?'; $params[] = '%' . $q . '%'; }
if ($cat > 0) { $sql .= ' AND category_id=?'; $params[] = $cat; }
$sql .= " ORDER BY $sortSql";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

include __DIR__ . '/common/header.php';
?>
<form class="card p-3 rounded mb-3 grid grid-cols-2 gap-2" method="get">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search..." class="px-3 py-2 rounded bg-slate-800 text-slate-100 col-span-2">
  <select name="cat" class="px-3 py-2 rounded bg-slate-800 text-slate-100">
    <option value="0">All Categories</option>
    <?php foreach ($cats as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= $cat===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="sort" class="px-3 py-2 rounded bg-slate-800 text-slate-100">
    <option value="latest" <?= $sort==='latest'?'selected':'' ?>>Latest</option>
    <option value="price" <?= $sort==='price'?'selected':'' ?>>Price</option>
  </select>
  <button class="col-span-2 mt-1 py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Apply</button>
</form>

<div class="grid grid-cols-1 gap-3">
  <?php foreach ($courses as $c): ?>
  <a href="/course_detail.php?id=<?= (int)$c['id'] ?>" class="card rounded overflow-hidden">
    <img src="<?= e($c['thumbnail'] ?: '/placeholder.svg') ?>" alt="<?= e($c['title']) ?>" class="w-full aspect-video object-cover">
    <div class="p-3">
      <div class="text-sm font-medium"><?= e($c['title']) ?></div>
      <div class="mt-1 text-sky-400 font-semibold text-sm">₹<?= money((float)$c['price']) ?>
        <?php if ((float)$c['mrp'] > (float)$c['price']): ?>
          <span class="text-slate-400 line-through text-xs ml-2">₹<?= money((float)$c['mrp']) ?></span>
          <span class="ml-2 text-xs text-emerald-400">
            <?= (int)round(100 - ((float)$c['price'] / max((float)$c['mrp'], 0.01)) * 100) ?>% OFF
          </span>
        <?php endif; ?>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
