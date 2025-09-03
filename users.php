<?php
// SkillzUp - admin/users.php (List users, view purchases)
require_once __DIR__ . '/../common/config.php';
require_admin();

$users = db()->query('SELECT * FROM users ORDER BY id DESC')->fetchAll();

include __DIR__ . '/common/header.php';
?>
<h1 class="text-lg font-semibold mb-3">Users</h1>
<div class="grid md:grid-cols-2 gap-3">
  <?php foreach ($users as $u): ?>
    <?php
      $p = db()->prepare('SELECT c.title FROM purchases pu JOIN courses c ON c.id=pu.course_id WHERE pu.user_id=? ORDER BY pu.id DESC');
      $p->execute([$u['id']]);
      $purchases = $p->fetchAll();
    ?>
    <div class="card p-3 rounded">
      <div class="font-medium"><?= e($u['name']) ?></div>
      <div class="text-xs text-slate-400"><?= e($u['email']) ?><?= $u['phone'] ? ' • ' . e($u['phone']) : '' ?></div>
      <div class="mt-2">
        <div class="text-xs text-slate-400 mb-1">Purchases (<?= count($purchases) ?>)</div>
        <ul class="text-sm list-disc pl-4 space-y-1">
          <?php foreach ($purchases as $row): ?>
            <li><?= e($row['title']) ?></li>
          <?php endforeach; ?>
          <?php if (!$purchases): ?><li class="text-slate-500">None</li><?php endif; ?>
        </ul>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$users): ?><p class="text-slate-400 text-sm">No users.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
