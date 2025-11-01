<?php
require __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$email = isset($_GET['email']) ? trim($_GET['email']) : 'admin@gymfuel.local';

$stmt = $pdo->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
echo "DATABASE() = " . $pdo->query("SELECT DATABASE()")->fetchColumn() . "\n";
echo "Email ζητήθηκε: {$email}\n\n";
if ($user) {
  echo "Βρέθηκε χρήστης:\n";
  print_r($user);
  $pwd = $user['password'];
  $isHash = preg_match('/^\$2y\$|\$2a\$|\$argon2i\$|\$argon2id\$/', $pwd) ? 'YES' : 'NO';
  echo "\nΕίναι hashed? {$isHash}\n";
} else {
  echo "ΔΕΝ βρέθηκε χρήστης με αυτό το email.\n";
}
echo "</pre>";
