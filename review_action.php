<?php
require __DIR__.'/db.php';
header('Content-Type: application/json; charset=utf-8');

if(!isset($_SESSION['user_id'])){
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Πρέπει να συνδεθείς.']);
  exit;
}

$uid = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$rid = intval($_POST['review_id'] ?? 0);

if(!$rid){ echo json_encode(['ok'=>false,'error'=>'Λείπει review_id']); exit; }

// έλεγχος ότι υπάρχει review και δεν είναι κρυμμένο
$chk = $pdo->prepare("SELECT id, is_hidden FROM reviews WHERE id=?");
$chk->execute([$rid]); $R = $chk->fetch();
if(!$R || $R['is_hidden']){ echo json_encode(['ok'=>false,'error'=>'Το review δεν είναι διαθέσιμο.']); exit; }

if($action === 'like'){
  // toggle like
  $exists = $pdo->prepare("SELECT 1 FROM review_likes WHERE review_id=? AND user_id=?");
  $exists->execute([$rid,$uid]);
  if($exists->fetch()){
    $pdo->prepare("DELETE FROM review_likes WHERE review_id=? AND user_id=?")->execute([$rid,$uid]);
  }else{
    $pdo->prepare("INSERT INTO review_likes (review_id,user_id) VALUES(?,?)")->execute([$rid,$uid]);
  }
  // επιστροφή νέου count
  $cnt = $pdo->prepare("SELECT COUNT(*) FROM review_likes WHERE review_id=?");
  $cnt->execute([$rid]);
  echo json_encode(['ok'=>true,'likes'=>(int)$cnt->fetchColumn()]);
  exit;
}

if($action === 'report'){
  $reason = trim($_POST['reason'] ?? '');
  if($reason && mb_strlen($reason) > 255) $reason = mb_substr($reason,0,255);
  try{
    $pdo->prepare("INSERT INTO review_reports (review_id,user_id,reason) VALUES (?,?,?)")
        ->execute([$rid,$uid,$reason ?: null]);
  }catch(Exception $e){
    // πιθανό DUPLICATE KEY αν έχει ξανακάνει report ο ίδιος χρήστης
  }
  echo json_encode(['ok'=>true,'message'=>'Η αναφορά καταχωρίστηκε. Ευχαριστούμε!']);
  exit;
}

echo json_encode(['ok'=>false,'error'=>'Άγνωστη ενέργεια.']);
