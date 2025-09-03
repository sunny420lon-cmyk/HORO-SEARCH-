<?php
// SkillzUp - admin/payments.php (View payments list)
require_once __DIR__ . '/../common/config.php';
require_admin();

$q = db()->query('
  SELECT p.*, o.user_id, o.course_id, u.name as user_name, c.title as course_title 
  FROM payments p 
  JOIN orders o ON o.id=p.order_id 
  JOIN users u ON u.id=o.user_id
  JOIN courses c ON c.id=o.course_id
  ORDER BY p.id DESC
');
$rows = $q->fetchAll();

include __DIR__ . '/common/header.php';
?>
<h1 class="text-lg font-semibold mb-3">Payments</h1>
<div class="card rounded overflow-x-auto">
  <table class="min-w-full text-sm">
    <thead class="bg-slate-800">
      <tr>
        <th class="text-left px-3 py-2">ID</th>
        <th class="text-left px-3 py-2">User</th>
        <th class="text-left px-3 py-2">Course</th>
        <th class="text-left px-3 py-2">Order ID</th>
        <th class="text-left px-3 py-2">Payment ID</th>
        <th class="text-left px-3 py-2">Amount</th>
        <th class="text-left px-3 py-2">Status</th>
        <th class="text-left px-3 py-2">Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="border-t border-slate-800">
          <td class="px-3 py-2"><?= (int)$r['id'] ?></td>
          <td class="px-3 py-2"><?= e($r['user_name']) ?></td>
          <td class="px-3 py-2"><?= e($r['course_title']) ?></td>
          <td class="px-3 py-2"><?= e($r['razorpay_order_id'] ?? '') ?></td>
          <td class="px-3 py-2"><?= e($r['razorpay_payment_id'] ?? '') ?></td>
          <td class="px-3 py-2">₹<?= money((float)$r['amount']) ?></td>
          <td class="px-3 py-2">
            <?php if ($r['status']==='success'): ?><span class="text-emerald-400">Success</span>
            <?php elseif ($r['status']==='failed'): ?><span class="text-red-400">Failed</span>
            <?php else: ?><span class="text-slate-300">Created</span><?php endif; ?>
          </td>
          <td class="px-3 py-2"><?= e($r['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td class="px-3 py-2" colspan="8">No payments.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/common/bottom.php'; ?>
