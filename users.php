<?php
require __DIR__ . '/../db.php';
require __DIR__ . '/../functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || (int)($_SESSION['is_admin'] ?? 0) !== 1) {
  header('Location: ../login.php'); exit;
}

$msg = '';
$err = '';

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';
try {
  if ($action === 'create') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if ($name==='' || $email==='' || $pass==='') throw new Exception('Συμπλήρωσε όλα τα πεδία.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Άκυρο email.');

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name,email,password,is_admin) VALUES (:n,:e,:p,:a)");
    $stmt->execute([':n'=>$name, ':e'=>$email, ':p'=>$hash, ':a'=>$is_admin]);
    $msg = 'Ο χρήστης δημιουργήθηκε.';
  }

  if ($action === 'update') {
    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    $pass  = $_POST['password'] ?? '';

    if ($id<=0) throw new Exception('Άκυρο ID.');
    if ($name==='' || $email==='') throw new Exception('Συμπλήρωσε όνομα και email.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Άκυρο email.');

    if ($pass !== '') {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $sql = "UPDATE users SET name=:n, email=:e, password=:p, is_admin=:a WHERE id=:id";
      $params = [':n'=>$name, ':e'=>$email, ':p'=>$hash, ':a'=>$is_admin, ':id'=>$id];
    } else {
      $sql = "UPDATE users SET name=:n, email=:e, is_admin=:a WHERE id=:id";
      $params = [':n'=>$name, ':e'=>$email, ':a'=>$is_admin, ':id'=>$id];
    }
    $pdo->prepare($sql)->execute($params);
    $msg = 'Τα στοιχεία ενημερώθηκαν.';
  }

  if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id<=0) throw new Exception('Άκυρο ID.');
    if ($id == (int)$_SESSION['user_id']) throw new Exception('Δεν μπορείς να διαγράψεις τον εαυτό σου.');
    $pdo->prepare("DELETE FROM users WHERE id=:id")->execute([':id'=>$id]);
    $msg = 'Ο χρήστης διαγράφηκε.';
  }
} catch (Throwable $e) {
  $err = $e->getMessage();
}

// Load list
$users = $pdo->query("SELECT id, name, email, is_admin FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Load one for edit
$edit = null;
if (isset($_GET['edit'])) {
  $eid = (int)$_GET['edit'];
  $st = $pdo->prepare("SELECT id, name, email, is_admin FROM users WHERE id=:id");
  $st->execute([':id'=>$eid]);
  $edit = $st->fetch(PDO::FETCH_ASSOC);
}

include __DIR__ . '/../header.php';
?>
<h3 class="mb-3">Διαχείριση χρηστών</h3>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title"><?= $edit ? 'Επεξεργασία χρήστη' : 'Νέος χρήστης' ?></h5>
        <form method="post">
          <?php if ($edit): ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
          <?php else: ?>
            <input type="hidden" name="action" value="create">
          <?php endif; ?>

          <div class="mb-2">
            <label class="form-label">Ονοματεπώνυμο</label>
            <input class="form-control" name="name" required value="<?= htmlspecialchars($edit['name'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" required value="<?= htmlspecialchars($edit['email'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label"><?= $edit ? 'Νέος' : '' ?> Κωδικός (προαιρετικός)</label>
            <input type="password" class="form-control" name="password" <?= $edit?'':'required' ?>>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin" <?= (isset($edit['is_admin']) && (int)$edit['is_admin']===1)?'checked':'' ?>>
            <label class="form-check-label" for="is_admin">Διαχειριστής</label>
          </div>
          <div class="d-grid">
            <button class="btn btn-primary"><?= $edit ? 'Αποθήκευση' : 'Δημιουργία' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Χρήστες</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>#</th><th>Όνομα</th><th>Email</th><th>Ρόλος</th><th class="text-end">Ενέργειες</th></tr></thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?= (int)$u['id'] ?></td>
                  <td><?= htmlspecialchars($u['name']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= ((int)$u['is_admin']===1)?'Admin':'User' ?></td>
                  <td class="text-end">
                    <a class="btn btn-outline-primary btn-sm" href="?edit=<?= (int)$u['id'] ?>">Επεξεργασία</a>
                    <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                      <form method="post" class="d-inline" onsubmit="return confirm('Διαγραφή χρήστη;');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm">Διαγραφή</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$users): ?>
                <tr><td colspan="5" class="text-muted">Δεν υπάρχουν χρήστες.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
