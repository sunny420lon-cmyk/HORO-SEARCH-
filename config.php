<?php
// SkillzUp - common/config.php
// Database + session + helpers + security

declare(strict_types=1);
ini_set('display_errors', '0');
error_reporting(E_ALL);

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'skillzup');

define('APP_NAME', 'SkillzUp');
define('BASE_URL', rtrim((isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? ''), '/'));

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
  return $pdo;
}

function json_response(array $data, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}
function verify_csrf(string $token): bool {
  return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}
function is_logged_in(): bool {
  return current_user() !== null;
}
function require_user(): void {
  if (!is_logged_in()) {
    header('Location: /login.php');
    exit;
  }
}

function is_admin_logged_in(): bool {
  return !empty($_SESSION['admin']);
}
function require_admin(): void {
  if (!is_admin_logged_in()) {
    header('Location: /admin/login.php');
    exit;
  }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function money(float $n): string { return number_format($n, 2); }

function is_purchased(int $userId, int $courseId): bool {
  $stmt = db()->prepare('SELECT 1 FROM purchases WHERE user_id=? AND course_id=? LIMIT 1');
  $stmt->execute([$userId, $courseId]);
  return (bool)$stmt->fetchColumn();
}

function upload_file(array $file, array $allowedMime, string $destDir): array {
  if (!is_dir($destDir)) @mkdir($destDir, 0775, true);
  if (!isset($file['error']) || is_array($file['error'])) return ['ok'=>false,'error'=>'Invalid upload'];
  if ($file['error'] !== UPLOAD_ERR_OK) return ['ok'=>false,'error'=>'Upload error'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  if (!in_array($mime, $allowedMime, true)) return ['ok'=>false,'error'=>'Invalid file type'];
  $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin';
  $name = bin2hex(random_bytes(8)) . '.' . $ext;
  $path = rtrim($destDir,'/') . '/' . $name;
  if (!move_uploaded_file($file['tmp_name'], $path)) return ['ok'=>false,'error'=>'Failed to save file'];
  return ['ok'=>true,'path'=>$path,'name'=>$name,'mime'=>$mime];
}
