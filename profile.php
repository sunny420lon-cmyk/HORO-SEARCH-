<?php
// SkillzUp - profile.php
require_once __DIR__ . '/common/config.php';
require_user();
$u = current_user();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $email = trim(strtolower($_POST['email'] ?? ''));
  $pass = $_POST['password'] ?? '';
  if ($name && $email) {
    $params = [$name, $phone, $email, $u['id']];
    $sql = 'UPDATE users SET name=?, phone=?, email=?';
    if ($pass) { $sql .= ', password_hash=?'; $params = [$name, $phone, $email, password_hash($pass, PASSWORD_BCRYPT), $u['id']]; }
    $sql .= ' WHERE id=?';
    try {
      $stmt = db()->prepare($sql);
      $stmt->execute($params);
      $_SESSION['user']['name'] = $name;
      $_SESSION['user']['email'] = $email;
      $msg = 'Profile updated';
    } catch (Throwable $e) {
      $msg = 'Update failed (email may already exist)';
    }
  }
}

include __DIR__ . '/common/header.php';
?>
<h1 class="text-base font-semibold mb-3">Profile</h1>
<?php if ($msg): ?><div class="mb-3 text-emerald-400 text-sm"><?= e($msg) ?></div><?php endif; ?>
<form method="post" class="card p-4 rounded space-y-3">
  <input type="text" name="name" value="<?= e($u['name']) ?>" placeholder="Name" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
  <input type="email" name="email" value="<?= e($u['email']) ?>" placeholder="Email" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
  <input type="tel" name="phone" value="<?= e($u['phone'] ?? '') ?>" placeholder="Phone" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100">
  <input type="password" name="password" placeholder="New Password (optional)" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Save</button>
</form>

<form method="post" action="/login.php" class="mt-4">
  <input type="hidden" name="action" value="logout">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <button class="w-full py-2 rounded bg-red-600 hover:bg-red-500 text-white font-medium">Logout</button>
</form>
<?php include __DIR__ . '/common/bottom.php'; ?>
