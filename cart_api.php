<?php
require __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Απαιτείται σύνδεση']); exit; }
$user_id = (int)$_SESSION['user_id'];

/* Υποστήριξη και για JSON και για form-data */
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) $payload = $_POST;
$action  = $payload['action'] ?? '';

function cart_totals(PDO $pdo, int $user_id): array {
  $st = $pdo->prepare("SELECT SUM(qty) AS items, SUM(qty*price) AS total FROM cart_items WHERE user_id=:u AND (reserved_until IS NULL OR reserved_until>NOW())");
  $st->execute([':u'=>$user_id]);
  $r = $st->fetch(PDO::FETCH_ASSOC) ?: ['items'=>0,'total'=>0];
  return ['items'=>(int)$r['items'], 'total'=>(float)$r['total']];
}

try {
  if ($action === 'add') {
    $pid = (int)($payload['product_id'] ?? 0);
    $qty = max(1, (int)($payload['qty'] ?? 1));
    // πάρε τρέχουσα τιμή
    $st = $pdo->prepare("SELECT price FROM products WHERE id=:id");
    $st->execute([':id'=>$pid]);
    $price = (float)$st->fetchColumn();
    if ($price <= 0) { echo json_encode(['ok'=>false,'message'=>'Άκυρο προϊόν/τιμή']); exit; }

    $pdo->prepare("
      INSERT INTO cart_items (user_id, product_id, qty, price, reserved_until)
      VALUES (:u,:p,:q,:pr, DATE_ADD(NOW(), INTERVAL 24 HOUR))
      ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty), price=VALUES(price), reserved_until=VALUES(reserved_until)
    ")->execute([':u'=>$user_id, ':p'=>$pid, ':q'=>$qty, ':pr'=>$price]);

    $tot = cart_totals($pdo, $user_id);
    echo json_encode(['ok'=>true,'message'=>'Προστέθηκε στο καλάθι','totals'=>$tot]);
    exit;
  }

  if ($action === 'list') {
    $st = $pdo->prepare("
      SELECT ci.id, ci.product_id, ci.qty, ci.price, p.name, p.image
      FROM cart_items ci JOIN products p ON p.id=ci.product_id
      WHERE ci.user_id=:u AND (ci.reserved_until IS NULL OR ci.reserved_until>NOW())
      ORDER BY ci.id DESC
    ");
    $st->execute([':u'=>$user_id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    $tot = cart_totals($pdo, $user_id);
    echo json_encode(['ok'=>true,'items'=>$rows,'totals'=>$tot]); exit;
  }

  if ($action === 'update') {
    $id  = (int)($payload['item_id'] ?? 0);
    $qty = max(1, (int)($payload['qty'] ?? 1));
    $pdo->prepare("UPDATE cart_items SET qty=:q, reserved_until=DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE id=:id AND user_id=:u")
        ->execute([':q'=>$qty, ':id'=>$id, ':u'=>$user_id]);
    $tot = cart_totals($pdo, $user_id);
    echo json_encode(['ok'=>true,'message'=>'Ενημερώθηκε','totals'=>$tot]); exit;
  }

  if ($action === 'remove') {
    $id  = (int)($payload['item_id'] ?? 0);
    $pdo->prepare("DELETE FROM cart_items WHERE id=:id AND user_id=:u")->execute([':id'=>$id, ':u'=>$user_id]);
    $tot = cart_totals($pdo, $user_id);
    echo json_encode(['ok'=>true,'message'=>'Αφαιρέθηκε','totals'=>$tot]); exit;
  }

  echo json_encode(['ok'=>false,'message'=>'Άγνωστη ενέργεια']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'message'=>'Σφάλμα server']);
}
