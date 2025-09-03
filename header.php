<?php
// SkillzUp - common/header.php
require_once __DIR__ . '/config.php';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= e(APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta name="theme-color" content="#0ea5e9">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-bx..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    /* Disable text selection globally except inputs */
    * { -webkit-tap-highlight-color: transparent; user-select: none; }
    input, textarea, select { user-select: text; }
    img { pointer-events: none; }
    /* App-like container */
    body { background: #0b1220; color: #e5e7eb; }
    .card { background: #0f172a; border: 1px solid #1f2937; }
    .sticky-bottom { position: sticky; bottom: 0; }
    /* Hide scrollbar for horizontal cards */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
  <script>
    // Disable right-click, text selection, pinch-to-zoom assistance
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('selectstart', e => {
      if(!(e.target instanceof HTMLInputElement) && !(e.target instanceof HTMLTextAreaElement)) e.preventDefault();
    });
    // Guard against zoom with key combos
    document.addEventListener('keydown', e => {
      if ((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '0')) e.preventDefault();
    });
  </script>
</head>
<body class="min-h-screen flex flex-col">
  <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur border-b border-slate-800">
    <div class="max-w-xl mx-auto px-4 h-14 flex items-center justify-between">
      <button id="openSidebar" class="p-2 rounded hover:bg-slate-800" aria-label="Menu">
        <i class="fa-solid fa-bars text-slate-200"></i>
      </button>
      <div class="font-semibold text-slate-100"><?= e(APP_NAME) ?></div>
      <a href="/profile.php" class="p-2 rounded hover:bg-slate-800" aria-label="Profile">
        <i class="fa-solid fa-user text-slate-200"></i>
      </a>
    </div>
  </header>

  <?php include __DIR__ . '/sidebar.php'; ?>

  <main class="flex-1 max-w-xl w-full mx-auto px-4 pt-4 pb-20">
