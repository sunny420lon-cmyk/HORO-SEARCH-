<?php
// SkillzUp - admin/index.php (Dashboard)
require_once __DIR__ . '/../common/config.php';
require_admin();

$totUsers = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$rev = db()->query('SELECT COALESCE(SUM(amount),0) FROM orders WHERE status="paid"')->fetchColumn();
$activeCourses = (int)db()->query('SELECT COUNT(*) FROM courses WHERE is_active=1')->fetchColumn();
$purchaseCount = (int)db()->query('SELECT COUNT(*) FROM purchases')->fetchColumn();

include __DIR__ . '/common/header.php';
?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
  <div class="card p-4 rounded"><div class="text-slate-400 text-sm">Total Users</div><div class="text-xl font-semibold"><?= $totUsers ?></div></div>
  <div class="card p-4 rounded"><div class="text-slate-400 text-sm">Revenue</div><div class="text-xl font-semibold">₹<?= money((float)$rev) ?></div></div>
  <div class="card p-4 rounded"><div class="text-slate-400 text-sm">Active Courses</div><div class="text-xl font-semibold"><?= $activeCourses ?></div></div>
  <div class="card p-4 rounded"><div class="text-slate-400 text-sm">Purchases</div><div class="text-xl font-semibold"><?= $purchaseCount ?></div></div>
</div>

<div class="mt-4 flex gap-2">
  <a href="/admin/course.php" class="px-3 py-2 rounded bg-sky-600 hover:bg-sky-500 text-white text-sm font-medium"><i class="fa-solid fa-plus mr-2"></i>Add Course</a>
  <a href="/admin/banner.php" class="px-3 py-2 rounded bg-sky-600 hover:bg-sky-500 text-white text-sm font-medium"><i class="fa-solid fa-plus mr-2"></i>Add Banner</a>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
