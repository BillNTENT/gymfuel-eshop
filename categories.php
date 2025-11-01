<?php
require __DIR__ . '/../db.php';
require __DIR__ . '/../functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || (int)($_SESSION['is_admin'] ?? 0) !== 1) {
  header('Location: ../login.php'); exit;
}

$msg=''; $err='';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
  if ($action === 'create') {
    $name = trim($_POST['name'] ?? '');
    if ($name==='') throw new Exception('Δώσε όνομα κατηγορίας.');
    $pdo->prepare("INSERT INTO categories (name) VALUES (:n)")->execute([':n'=>$name]);
    $msg='Η κατηγορία δημιουργήθηκε.';
  }
  if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($id<=0 || $name==='') throw new Exception('Άκυρα στοιχεία.');
    $pdo->prepare("UPDATE categories SET name=:n WHERE id=:id")->execute([':n'=>$name, ':id'=>$id]);
    $msg='Η κατηγορία ενημερώθηκε.';
  }
  if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id<=0) throw new Exception('Άκυρο ID.');
    // προαιρετικά: έλεγχος αν έχει προϊόντα
    $cnt = (int)$pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id=:id")->execute([':id'=>$id]) ?: 0;
    $stc = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id=:id");
    $stc->execute([':id'=>$id]);
    $cnt = (int)$stc->fetchColumn();
    if ($cnt>0) throw new Exception('Υπάρχουν προϊόντα στην κατηγορία (μετακίνησέ τα πριν τη διαγραφή).');
    $pdo->prepare("DELETE FROM categories WHERE id=:id")->execute([':id'=>$id]);
    $msg='Η κατηγορία διαγράφηκε.';
  }
} catch (Throwable $e) {
  $err = $e->getMessage();
}

$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$edit = null;
if (isset($_GET['edit'])) {
  $eid = (int)$_GET['edit'];
  $st = $pdo->prepare("SELECT id, name FROM categories WHERE id=:id");
  $st->execute([':id'=>$eid]);
  $edit = $st->fetch(PDO::FETCH_ASSOC);
}

include __DIR__ . '/../header.php';
?>
<h3 class="mb-3">Κατηγορίες</h3>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title"><?= $edit?'Επεξεργασία':'Νέα' ?> κατηγορία</h5>
        <form method="post">
          <?php if ($edit): ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
          <?php else: ?>
            <input type="hidden" name="action" value="create">
          <?php endif; ?>
          <div class="mb-2">
            <label class="form-label">Όνομα</label>
            <input class="form-control" name="name" required value="<?= htmlspecialchars($edit['name'] ?? '') ?>">
          </div>
          <div class="d-grid">
            <button class="btn btn-primary"><?= $edit?'Αποθήκευση':'Δημιουργία' ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Λίστα κατηγοριών</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>#</th><th>Όνομα</th><th class="text-end">Ενέργειες</th></tr></thead>
            <tbody>
              <?php foreach($cats as $c): ?>
                <tr>
                  <td><?= (int)$c['id'] ?></td>
                  <td><?= htmlspecialchars($c['name']) ?></td>
                  <td class="text-end">
                    <a class="btn btn-outline-primary btn-sm" href="?edit=<?= (int)$c['id'] ?>">Επεξεργασία</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Διαγραφή κατηγορίας;');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button class="btn btn-outline-danger btn-sm">Διαγραφή</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$cats): ?>
                <tr><td colspan="3" class="text-muted">Δεν υπάρχουν κατηγορίες.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
