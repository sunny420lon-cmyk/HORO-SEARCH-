<?php
// SkillzUp - buy.php (Razorpay Integration via cURL + signature verification)
require_once __DIR__ . '/common/config.php';
require_user();

$courseId = (int)($_GET['course'] ?? 0);
$stmt = db()->prepare('SELECT id, title, price FROM courses WHERE id=? AND is_active=1');
$stmt->execute([$courseId]);
$course = $stmt->fetch();
if (!$course) { header('Location: /course.php'); exit; }

$settingsStmt = db()->prepare('SELECT `key`,`value` FROM settings WHERE `key` IN ("razorpay_key_id","razorpay_key_secret")');
$settingsStmt->execute();
$settings = array_column($settingsStmt->fetchAll(), 'value', 'key');
$RZP_KEY = trim($settings['razorpay_key_id'] ?? '');
$RZP_SECRET = trim($settings['razorpay_key_secret'] ?? '');

$user = current_user();

// Create local order
$orderStmt = db()->prepare('INSERT INTO orders (user_id, course_id, amount, status) VALUES (?,?,?, "pending")');
$orderStmt->execute([(int)$user['id'], (int)$course['id'], (float)$course['price']]);
$localOrderId = (int)db()->lastInsertId();

// Endpoint: Create Razorpay Order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $payload = json_decode(file_get_contents('php://input'), true) ?: [];
  if (($payload['action'] ?? '') === 'create_order') {
    if (!$RZP_KEY || !$RZP_SECRET) json_response(['ok'=>false,'error'=>'Razorpay not configured'], 500);
    $amountPaise = (int)round(((float)$course['price']) * 100);
    $body = json_encode(['amount'=>$amountPaise,'currency'=>'INR','receipt'=>"order_$localOrderId","payment_capture"=>1]);
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $body,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_USERPWD => $RZP_KEY . ':' . $RZP_SECRET,
    ]);
    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http >= 200 && $http < 300) {
      $r = json_decode($res, true);
      // save payment record
      $pstmt = db()->prepare('INSERT INTO payments (order_id, razorpay_order_id, status, amount) VALUES (?,?, "created", ?)');
      $pstmt->execute([$localOrderId, $r['id'], (float)$course['price']]);
      json_response(['ok'=>true, 'rzp_order'=>$r]);
    } else {
      json_response(['ok'=>false, 'error'=>'Failed to create order'], 500);
    }
  } elseif (($payload['action'] ?? '') === 'verify_payment') {
    // Verify signature
    $rzpOrderId = $payload['razorpay_order_id'] ?? '';
    $rzpPaymentId = $payload['razorpay_payment_id'] ?? '';
    $signature = $payload['razorpay_signature'] ?? '';
    if (!$rzpOrderId || !$rzpPaymentId || !$signature) json_response(['ok'=>false,'error'=>'Missing params'], 400);
    $expected = hash_hmac('sha256', $rzpOrderId . '|' . $rzpPaymentId, $RZP_SECRET);
    if (!hash_equals($expected, $signature)) {
      // mark failed
      db()->prepare('UPDATE orders SET status="failed" WHERE id=?')->execute([$localOrderId]);
      db()->prepare('UPDATE payments SET status="failed", razorpay_payment_id=? WHERE order_id=?')->execute([$rzpPaymentId, $localOrderId]);
      json_response(['ok'=>false, 'error'=>'Signature mismatch'], 400);
    }
    // Optional: verify payment fetch
    $ch = curl_init('https://api.razorpay.com/v1/payments/' . urlencode($rzpPaymentId));
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
      CURLOPT_USERPWD => $RZP_KEY . ':' . $RZP_SECRET,
    ]);
    $res = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http >= 200 && $http < 300) {
      $info = json_decode($res, true);
      if (($info['status'] ?? '') === 'captured') {
        db()->prepare('UPDATE orders SET status="paid" WHERE id=?')->execute([$localOrderId]);
        db()->prepare('UPDATE payments SET status="success", razorpay_payment_id=?, payload=? WHERE order_id=?')
          ->execute([$rzpPaymentId, json_encode($info), $localOrderId]);
        // Grant purchase
        db()->prepare('INSERT IGNORE INTO purchases (user_id, course_id) VALUES (?,?)')
          ->execute([(int)$user['id'], (int)$course['id']]);
        json_response(['ok'=>true, 'redirect'=>'/mycourses.php']);
      }
    }
    json_response(['ok'=>false, 'error'=>'Payment not captured'], 400);
  }
  json_response(['ok'=>false, 'error'=>'Unknown action'], 400);
}

include __DIR__ . '/common/header.php';
?>
<div class="card p-4 rounded">
  <h1 class="text-lg font-semibold mb-2">Checkout</h1>
  <div class="flex items-center gap-3">
    <div class="h-16 w-24 rounded overflow-hidden bg-slate-800">
      <img src="<?= e($course['thumbnail'] ?: '/placeholder.svg') ?>" alt="<?= e($course['title']) ?>" class="w-full h-full object-cover">
    </div>
    <div>
      <div class="font-medium"><?= e($course['title']) ?></div>
      <div class="text-sky-400 font-semibold">₹<?= money((float)$course['price']) ?></div>
    </div>
  </div>
  <button id="payBtn" class="mt-4 w-full py-2 rounded bg-sky-600 hover:bg-sky-500 text-white font-medium">Pay with Razorpay</button>
  <p id="payMsg" class="text-sm text-red-400 mt-2"></p>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  const payBtn = document.getElementById('payBtn');
  const payMsg = document.getElementById('payMsg');
  payBtn.addEventListener('click', async () => {
    payMsg.textContent = '';
    // create order
    const res = await fetch(location.href, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'create_order'}) });
    const data = await res.json();
    if (!data.ok) { payMsg.textContent = data.error || 'Failed to init payment'; return; }
    const rzpOrder = data.rzp_order;
    const options = {
      key: <?= json_encode($RZP_KEY) ?>,
      amount: rzpOrder.amount,
      currency: rzpOrder.currency,
      name: <?= json_encode(APP_NAME) ?>,
      description: <?= json_encode($course['title']) ?>,
      order_id: rzpOrder.id,
      handler: async function (response) {
        // verify with server
        const vres = await fetch(location.href, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({
          action:'verify_payment',
          razorpay_order_id: response.razorpay_order_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_signature: response.razorpay_signature
        })});
        const vdata = await vres.json();
        if (vdata.ok) { location.href = vdata.redirect; } else { payMsg.textContent = vdata.error || 'Payment verification failed'; }
      },
      theme: { color: '#0ea5e9' }
    };
    const rzp = new Razorpay(options);
    rzp.on('payment.failed', function (res) {
      payMsg.textContent = res.error.description || 'Payment failed';
    });
    rzp.open();
  });
</script>
<?php include __DIR__ . '/common/bottom.php'; ?>
