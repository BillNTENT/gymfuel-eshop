<?php
require __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['user_id'])) {
  $pdo->prepare("
    UPDATE user_sessions 
    SET logout_at = NOW()
    WHERE user_id = :u AND logout_at IS NULL
    ORDER BY id DESC LIMIT 1
  ")->execute([':u'=>$_SESSION['user_id']]);
}

session_unset();
session_destroy();
header('Location: index.php'); exit;
