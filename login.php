<?php
// SkillzUp - admin/login.php
require_once __DIR__ . '/../common/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['action'] ?? '') === 'logout') { $_SESSION['admin'] = null; header('Location: /admin/login.php'); exit; }
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $stmt = db()->prepare('SELECT * FROM admin_users WHERE username=?');
  $stmt->execute([$username]);
  $adm = $stmt->fetch();
  if ($adm && password_verify($password, $adm['password_hash'])) {
    $_SESSION['admin'] = ['id'=>$adm['id'], 'username'=>$adm['username']];
    header('Location: /admin/index.php'); exit;
  }
  $error = 'Invalid credentials';
}

include __DIR__ . '/common/header.php';
?>
<div class="max-w-sm mx-auto card p-4 rounded">
  <h1 class="text-lg font-semibold mb-3">Admin Login</h1>
  <?php if (!empty($error)): ?><div class="text-red-400 text-sm mb-2"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="space-y-3">
    <input type="text" name="username" placeholder="Username" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
    <input type="password" name="password" placeholder="Password" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
    <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Login</button>
  </form>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
