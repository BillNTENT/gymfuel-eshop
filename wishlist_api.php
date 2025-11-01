<?php
require __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['ok'=>false,'message'=>'Απαιτείται σύνδεση']); exit; }

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$action  = $payload['action'] ?? '';
$user_id = (int)$_SESSION['user_id'];

try {
  if ($action === 'toggle') {
    $pid = (int)($payload['product_id'] ?? 0);
    // αν υπάρχει, σβήστο, αλλιώς πρόσθεσέ το
    $st = $pdo->prepare("SELECT id FROM wishlist WHERE user_id=:u AND product_id=:p");
    $st->execute([':u'=>$user_id, ':p'=>$pid]);
    $exists = $st->fetchColumn();

    if ($exists) {
      $pdo->prepare("DELETE FROM wishlist WHERE id=:id")->execute([':id'=>$exists]);
      echo json_encode(['ok'=>true,'message'=>'Αφαιρέθηκε από τα αγαπημένα']); exit;
    } else {
      $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (:u,:p)")
          ->execute([':u'=>$user_id, ':p'=>$pid]);
      echo json_encode(['ok'=>true,'message'=>'Προστέθηκε στα αγαπημένα']); exit;
    }
  }

  echo json_encode(['ok'=>false,'message'=>'Άγνωστη ενέργεια']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'message'=>'Σφάλμα server']);
}
