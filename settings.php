<?php
// SkillzUp - admin/settings.php (Update site settings incl. Razorpay)
require_once __DIR__ . '/../common/config.php';
require_admin();

$keys = ['site_name', 'razorpay_key_id', 'razorpay_key_secret'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($keys as $k) {
    $val = trim($_POST[$k] ?? '');
    $stmt = db()->prepare('INSERT INTO settings (`key`, `value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
    $stmt->execute([$k, $val]);
  }
  header('Location: ' . $_SERVER['REQUEST_URI']); exit;
}

$settings = db()->query('SELECT `key`,`value` FROM settings')->fetchAll();
$map = array_column($settings, 'value', 'key');

include __DIR__ . '/common/header.php';
?>
<h1 class="text-lg font-semibold mb-3">Settings</h1>
<form method="post" class="card p-4 rounded space-y-3 max-w-lg">
  <label class="block">
    <span class="text-sm text-slate-300">Site Name</span>
    <input name="site_name" class="mt-1 w-full px-3 py-2 rounded bg-slate-800 text-slate-100" value="<?= e($map['site_name'] ?? APP_NAME) ?>">
  </label>
  <label class="block">
    <span class="text-sm text-slate-300">Razorpay Key ID</span>
    <input name="razorpay_key_id" class="mt-1 w-full px-3 py-2 rounded bg-slate-800 text-slate-100" value="<?= e($map['razorpay_key_id'] ?? '') ?>">
  </label>
  <label class="block">
    <span class="text-sm text-slate-300">Razorpay Key Secret</span>
    <input name="razorpay_key_secret" class="mt-1 w-full px-3 py-2 rounded bg-slate-800 text-slate-100" value="<?= e($map['razorpay_key_secret'] ?? '') ?>">
  </label>
  <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Save</button>
</form>
<?php include __DIR__ . '/common/bottom.php'; ?>
