<?php
require __DIR__.'/../db.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { header('Location: ../login.php'); exit; }

$errors=[]; $success=null;

/* Δημιουργία νέου χρήστη (επιτρέπεται ορισμός email+password ΜΟΝΟ κατά τη δημιουργία) */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='create') {
  $name  = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $phone = trim($_POST['phone'] ?? '');
  $addr  = trim($_POST['address'] ?? '');
  $isadm = isset($_POST['is_admin']) ? 1 : 0;

  if(!$name) $errors[]='Δώσε όνομα.';
  if(!$email || !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Έγκυρο email.';
  if(strlen($pass) < 6) $errors[]='Κωδικός τουλάχιστον 6 χαρακτήρες.';

  if(!$errors){
    try{
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $st = $pdo->prepare("INSERT INTO users (name,email,password,is_admin,phone,address) VALUES (?,?,?,?,?,?)");
      $st->execute([$name,$email,$hash,$isadm,$phone,$addr]);
      $success='Ο χρήστης δημιουργήθηκε.';
    }catch(Exception $e){
      $errors[]='Το email χρησιμοποιείται ήδη.';
    }
  }
}

/* Επεξεργασία στοιχείων (ΟΧΙ email/password) */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['form']??'')==='edit') {
  $id    = (int)($_POST['id'] ?? 0);
  $name  = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $addr  = trim($_POST['address'] ?? '');
  $isadm = isset($_POST['is_admin']) ? 1 : 0;

  if($id<=0) $errors[]='Άκυρο αίτημα.';
  if(!$name) $errors[]='Δώσε όνομα.';

  if(!$errors){
    $st = $pdo->prepare("UPDATE users SET name=?, phone=?, address=?, is_admin=? WHERE id=?");
    $st->execute([$name,$phone,$addr,$isadm,$id]);
    $success='Ο χρήστης ενημερώθηκε.';
    // αν τροποποιείς τον τρέχον admin εσύ → ανανέωσε και το session name/is_admin
    if(isset($_SESSION['user_id']) && $_SESSION['user_id']==$id){
      $_SESSION['name']=$name; $_SESSION['is_admin']=$isadm;
    }
  }
}

/* Διαγραφή χρήστη (όχι τον εαυτό σου) */
if (($_GET['action']??'')==='delete'){
  $id=(int)($_GET['id']??0);
  if($id && $id != $_SESSION['user_id']){
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    header('Location: users_manage.php?ok=1'); exit;
  }
}

/* Δεδομένα */
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$rows = $pdo->query("SELECT id,name,email,is_admin,phone,address FROM users ORDER BY id DESC")->fetchAll();

include __DIR__.'/../header.php';
?>
<h2 class="mb-3">Χρήστες <span class="badge bg-dark"><?= $totalUsers ?></span></h2>

<?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
<?php foreach($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; ?>
<?php if(($_GET['ok']??'')==='1'): ?><div class="alert alert-success">Η διαγραφή ολοκληρώθηκε.</div><?php endif; ?>

<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title">Νέος χρήστης</h5>
    <form method="post" class="row g-2">
      <input type="hidden" name="form" value="create">
      <div class="col-md-3">
        <label class="form-label">Ονοματεπώνυμο</label>
        <input name="name" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Email (login)</label>
        <input name="email" type="email" class="form-control" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Κωδικός (login)</label>
        <input name="password" type="password" class="form-control" minlength="6" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Τηλέφωνο</label>
        <input name="phone" class="form-control">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin_new">
          <label class="form-check-label" for="is_admin_new">Admin</label>
        </div>
      </div>
      <div class="col-12">
        <label class="form-label">Διεύθυνση</label>
        <textarea name="address" rows="2" class="form-control"></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-person-plus"></i> Δημιουργία</button>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th><th>Όνομα</th><th>Email (login)</th><th>Τηλέφωνο</th><th>Διεύθυνση</th><th>Ρόλος</th><th class="text-end">Ενέργειες</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td>
          <form method="post" class="row g-2">
            <input type="hidden" name="form" value="edit">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <div class="col-md-12">
              <input name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($r['name']) ?>" required>
            </div>
        </td>
        <td><input class="form-control form-control-sm" value="<?= htmlspecialchars($r['email']) ?>" disabled></td>
        <td><input name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($r['phone'] ?? '') ?>"></td>
        <td><input name="address" class="form-control form-control-sm" value="<?= htmlspecialchars($r['address'] ?? '') ?>"></td>
        <td>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_admin" <?= $r['is_admin']?'checked':''; ?>>
            <label class="form-check-label small">Admin</label>
          </div>
        </td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary"><i class="bi bi-save"></i></button>
          <?php if($r['id'] != $_SESSION['user_id']): ?>
            <a class="btn btn-sm btn-outline-danger" href="users_manage.php?action=delete&id=<?= $r['id'] ?>" onclick="return confirm('Διαγραφή χρήστη;');"><i class="bi bi-trash"></i></a>
          <?php endif; ?>
          <a class="btn btn-sm btn-outline-secondary" href="user_sessions.php?user_id=<?= $r['id'] ?>">
            <i class="bi bi-clock-history"></i> Συνδέσεις
          </a>
        </td>
          </form>
      </tr>
    <?php endforeach; if(!$rows) echo '<tr><td colspan="7" class="text-muted">Δεν υπάρχουν χρήστες.</td></tr>'; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__.'/../footer.php'; ?>
