<?php
require __DIR__ . '/../db.php';
require __DIR__ . '/../functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || (int)($_SESSION['is_admin'] ?? 0) !== 1) {
  header('Location: ../login.php'); exit;
}

$msg=''; $err='';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['user_id'], $_POST['new_pass'])) {
  try {
    $uid = (int)$_POST['user_id'];
    $new = trim($_POST['new_pass']);
    if ($uid<=0 || $new==='') throw new Exception('Δώσε νέο κωδικό.');
    if (strlen($new) < 6) throw new Exception('Ο κωδικός πρέπει να έχει τουλάχιστον 6 χαρακτήρες.');

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $st = $pdo->prepare("UPDATE users SET password=:p WHERE id=:id");
    $st->execute([':p'=>$hash, ':id'=>$uid]);
    $msg = 'Ο κωδικός ενημερώθηκε.';
  } catch (Throwable $e) {
    $err = $e->getMessage();
  }
}

$users = $pdo->query("SELECT id, name, email, is_admin FROM users ORDER BY is_admin DESC, id")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../header.php';
?>
<h3 class="mb-3">Κωδικοί χρηστών</h3>
<p class="text-muted">Προβολή χρηστών και επαναφορά κωδικού (με ασφαλές hash).</p>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead>
      <tr><th>#</th><th>Όνομα</th><th>Email</th><th>Ρόλος</th><th class="text-end">Ορισμός νέου κωδικού</th></tr>
    </thead>
    <tbody>
      <?php foreach($users as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= ((int)$u['is_admin']===1)?'Admin':'User' ?></td>
          <td class="text-end">
            <form method="post" class="d-inline-flex gap-2">
              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <input type="password" name="new_pass" class="form-control form-control-sm" placeholder="νέος κωδικός" required>
              <button class="btn btn-primary btn-sm">Αποθήκευση</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$users): ?>
        <tr><td colspan="5" class="text-muted">Δεν υπάρχουν χρήστες.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
