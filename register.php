<?php
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$err=''; $ok='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email= trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';
  $pass2= $_POST['password2'] ?? '';

  if ($name==='' || $email==='' || $pass==='')        $err='Συμπλήρωσε όλα τα πεδία.';
  elseif (!filter_var($email,FILTER_VALIDATE_EMAIL))   $err='Άκυρο email.';
  elseif ($pass !== $pass2)                            $err='Οι κωδικοί δεν ταιριάζουν.';
  else {
    try {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $pdo->prepare("INSERT INTO users (name,email,password,is_admin) VALUES (:n,:e,:p,0)")
          ->execute([':n'=>$name, ':e'=>$email, ':p'=>$hash]);
      $ok='Ο λογαριασμός δημιουργήθηκε. Μπορείς να συνδεθείς.';
    } catch (PDOException $ex) {
      $err = ($ex->getCode()==='23000') ? 'Υπάρχει ήδη χρήστης με αυτό το email.' : ('Σφάλμα: '.$ex->getMessage());
    }
  }
}
include __DIR__ . '/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-7 col-lg-6">
    <h3 class="mb-3">Εγγραφή</h3>
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if ($ok):  ?><div class="alert alert-success"><?= htmlspecialchars($ok)  ?></div><?php endif; ?>
    <form method="post" action="register.php" class="card p-3 shadow-sm">
      <div class="mb-3"><label class="form-label">Ονοματεπώνυμο</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3"><label class="form-label">Κωδικός</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3"><label class="form-label">Επαλήθευση κωδικού</label>
        <input type="password" name="password2" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Δημιουργία λογαριασμού</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
