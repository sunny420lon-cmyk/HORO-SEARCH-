<?php
// SkillzUp - common/sidebar.php
$u = current_user();
?>
<aside id="sidebar" class="fixed inset-0 z-40 hidden">
  <div id="sidebarOverlay" class="absolute inset-0 bg-black/50"></div>
  <nav class="absolute left-0 top-0 h-full w-72 bg-slate-900 border-r border-slate-800 p-4">
    <div class="flex items-center gap-3 pb-4 border-b border-slate-800">
      <div class="h-10 w-10 rounded-full bg-slate-800 flex items-center justify-center">
        <i class="fa-solid fa-user text-slate-300"></i>
      </div>
      <div>
        <div class="text-slate-100 text-sm font-medium"><?= $u ? e($u['name']) : 'Guest' ?></div>
        <div class="text-slate-400 text-xs"><?= $u ? e($u['email']) : 'Not signed in' ?></div>
      </div>
    </div>
    <ul class="mt-4 space-y-2 text-sm">
      <li><a class="flex items-center gap-3 p-2 rounded hover:bg-slate-800" href="/index.php"><i class="fa-solid fa-house"></i> Home</a></li>
      <li><a class="flex items-center gap-3 p-2 rounded hover:bg-slate-800" href="/mycourses.php"><i class="fa-solid fa-graduation-cap"></i> My Courses</a></li>
      <li><a class="flex items-center gap-3 p-2 rounded hover:bg-slate-800" href="/help.php"><i class="fa-solid fa-circle-question"></i> Help</a></li>
      <li><a class="flex items-center gap-3 p-2 rounded hover:bg-slate-800" href="/profile.php"><i class="fa-solid fa-user-gear"></i> Profile</a></li>
      <?php if ($u): ?>
      <li>
        <form method="post" action="/login.php" onsubmit="return true;">
          <input type="hidden" name="action" value="logout">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <button class="w-full flex items-center gap-3 p-2 rounded hover:bg-slate-800 text-left" type="submit">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
          </button>
        </form>
      </li>
      <?php else: ?>
      <li><a class="flex items-center gap-3 p-2 rounded hover:bg-slate-800" href="/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login / Signup</a></li>
      <?php endif; ?>
      <li class="pt-2 border-t border-slate-800"><a class="flex items-center gap-3 p-2 rounded hover:bg-slate-800" href="/admin/login.php"><i class="fa-solid fa-lock"></i> Admin</a></li>
    </ul>
  </nav>
</aside>
<script>
  const sidebar = document.getElementById('sidebar');
  const openSidebarBtn = document.getElementById('openSidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  openSidebarBtn?.addEventListener('click', () => sidebar.classList.remove('hidden'));
  sidebarOverlay?.addEventListener('click', () => sidebar.classList.add('hidden'));
</script>
