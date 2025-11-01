<?php
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = :email LIMIT 1");
  $stmt->execute([':email' => $email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user) {
    $stored = (string)$user['password'];
    $looksHashed = (bool)preg_match('/^\$2y\$|\$2a\$|\$argon2i\$|\$argon2id\$/', $stored);

    $ok = $looksHashed ? password_verify($pass, $stored) : hash_equals($stored, $pass);
    if ($ok && !$looksHashed) {
      $newHash = password_hash($pass, PASSWORD_DEFAULT);
      $pdo->prepare("UPDATE users SET password=:p WHERE id=:id")->execute([':p'=>$newHash, ':id'=>$user['id']]);
    }

    if ($ok) {
      $_SESSION['user_id']    = (int)$user['id'];
      $_SESSION['user_name']  = $user['name'];
      $_SESSION['user_email'] = $user['email'];
      $_SESSION['is_admin']   = (int)$user['is_admin'];
      // συμβατότητα
      $_SESSION['name'] = $user['name'];
      $_SESSION['user'] = ['id'=>(int)$user['id'],'name'=>$user['name'],'email'=>$user['email'],'is_admin'=>(int)$user['is_admin']];

      // log session start
      $pdo->prepare("INSERT INTO user_sessions (user_id, ip, user_agent) VALUES (:u,:ip,:ua)")
          ->execute([':u'=>$user['id'], ':ip'=>$_SERVER['REMOTE_ADDR'] ?? null, ':ua'=>substr($_SERVER['HTTP_USER_AGENT'] ?? '',0,255)]);

      header('Location: '.(((int)$user['is_admin']===1)?'admin/index.php':'index.php')); exit;
    } else {
      $err = 'Λάθος στοιχεία.';
    }
  } else {
    $err = 'Δεν βρέθηκε χρήστης με αυτό το email.';
  }
}
include __DIR__ . '/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h3 class="mb-3">Σύνδεση</h3>
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <form method="post" action="login.php" class="card p-3 shadow-sm">
      <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3"><label class="form-label">Κωδικός</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Σύνδεση</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
