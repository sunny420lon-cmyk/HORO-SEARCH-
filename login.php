<?php
// SkillzUp - login.php (User Login/Signup AJAX + Logout)
require_once __DIR__ . '/common/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  if ($action === 'logout') {
    if (!verify_csrf($_POST['csrf'] ?? '')) json_response(['ok'=>false,'error'=>'Bad CSRF'], 400);
    $_SESSION = [];
    session_destroy();
    header('Location: /index.php');
    exit;
  }
  header('Content-Type: application/json');
  $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
  $action = $payload['action'] ?? '';
  if ($action === 'login') {
    $email = trim(strtolower($payload['email'] ?? ''));
    $pass = $payload['password'] ?? '';
    if (!$email || !$pass) json_response(['ok'=>false, 'error'=>'Missing credentials'], 400);
    $stmt = db()->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($pass, $user['password_hash'])) json_response(['ok'=>false, 'error'=>'Invalid email or password'], 401);
    $_SESSION['user'] = ['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email']];
    json_response(['ok'=>true, 'redirect'=>'/index.php']);
  } elseif ($action === 'signup') {
    $name = trim($payload['name'] ?? '');
    $phone = trim($payload['phone'] ?? '');
    $email = trim(strtolower($payload['email'] ?? ''));
    $pass = $payload['password'] ?? '';
    if (!$name || !$email || !$pass) json_response(['ok'=>false, 'error'=>'Missing fields'], 400);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['ok'=>false,'error'=>'Invalid email'], 400);
    try {
      $stmt = db()->prepare('INSERT INTO users (name, email, phone, password_hash) VALUES (?,?,?,?)');
      $stmt->execute([$name, $email, $phone, password_hash($pass, PASSWORD_BCRYPT)]);
      $id = (int)db()->lastInsertId();
      $_SESSION['user'] = ['id'=>$id,'name'=>$name,'email'=>$email];
      json_response(['ok'=>true, 'redirect'=>'/index.php']);
    } catch (Throwable $e) {
      json_response(['ok'=>false, 'error'=>'Email already exists'], 409);
    }
  } else {
    json_response(['ok'=>false, 'error'=>'Unknown action'], 400);
  }
}

include __DIR__ . '/common/header.php';
?>
<div class="card p-4 rounded">
  <div class="flex gap-2 bg-slate-800 p-1 rounded mb-4">
    <button id="tabLogin" class="flex-1 py-2 rounded bg-slate-700 text-white font-medium">Login</button>
    <button id="tabSignup" class="flex-1 py-2 rounded text-slate-200">Signup</button>
  </div>

  <form id="formLogin" class="space-y-3">
    <input type="email" name="email" placeholder="Email" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
    <input type="password" name="password" placeholder="Password" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
    <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium" type="submit">Login</button>
  </form>

  <form id="formSignup" class="space-y-3 hidden">
    <input type="text" name="name" placeholder="Name" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
    <input type="tel" name="phone" placeholder="Phone" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100">
    <input type="email" name="email" placeholder="Email" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
    <input type="password" name="password" placeholder="Password" class="w-full px-3 py-2 rounded bg-slate-800 text-slate-100" required>
    <button class="w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium" type="submit">Create account</button>
  </form>

  <p id="authMsg" class="text-sm text-red-400 mt-3"></p>
</div>

<script>
  const tabLogin = document.getElementById('tabLogin');
  const tabSignup = document.getElementById('tabSignup');
  const formLogin = document.getElementById('formLogin');
  const formSignup = document.getElementById('formSignup');
  const authMsg = document.getElementById('authMsg');

  tabLogin.addEventListener('click', () => {
    tabLogin.classList.add('bg-slate-700','text-white'); tabSignup.classList.remove('bg-slate-700','text-white');
    formLogin.classList.remove('hidden'); formSignup.classList.add('hidden');
    authMsg.textContent = '';
  });
  tabSignup.addEventListener('click', () => {
    tabSignup.classList.add('bg-slate-700','text-white'); tabLogin.classList.remove('bg-slate-700','text-white');
    formSignup.classList.remove('hidden'); formLogin.classList.add('hidden');
    authMsg.textContent = '';
  });

  formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();
    authMsg.textContent = '';
    const fd = new FormData(formLogin);
    const res = await fetch('/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action:'login', email: fd.get('email'), password: fd.get('password') })
    });
    const data = await res.json();
    if (data.ok) location.href = data.redirect; else authMsg.textContent = data.error || 'Login failed';
  });

  formSignup.addEventListener('submit', async (e) => {
    e.preventDefault();
    authMsg.textContent = '';
    const fd = new FormData(formSignup);
    const res = await fetch('/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action:'signup', name: fd.get('name'), phone: fd.get('phone'), email: fd.get('email'), password: fd.get('password') })
    });
    const data = await res.json();
    if (data.ok) location.href = data.redirect; else authMsg.textContent = data.error || 'Signup failed';
  });
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>
