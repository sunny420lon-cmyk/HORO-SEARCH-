<?php
// SkillzUp - install.php
require_once __DIR__ . '/common/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $sql = file_get_contents(__DIR__ . '/scripts/install.sql');
  try {
    db()->exec($sql);
    $ok = true;
  } catch (Throwable $e) {
    $ok = false; $err = $e->getMessage();
  }
  ?>
  <!doctype html><meta charset="utf-8">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.14/dist/tailwind.min.css">
  <div class="max-w-lg mx-auto p-6">
    <?php if ($ok): ?>
    <div class="p-4 rounded bg-green-600 text-white">Installation complete. Go to <a class="underline" href="/index.php">Home</a> or <a class="underline" href="/admin/login.php">Admin</a></div>
    <?php else: ?>
    <div class="p-4 rounded bg-red-600 text-white">Error: <?= e($err ?? 'Unknown') ?></div>
    <?php endif; ?>
  </div>
  <?php
  exit;
}
?>
<?php include __DIR__ . '/common/header.php'; ?>
<div class="card p-4 rounded">
  <h1 class="text-lg font-semibold mb-2">Install SkillzUp</h1>
  <p class="text-sm text-slate-300 mb-4">This will create required tables in database "<?= e(DB_NAME) ?>" on <?= e(DB_HOST) ?>.</p>
  <form method="post">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Run Installation</button>
  </form>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
