<?php
require __DIR__.'/db.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$uid = $_SESSION['user_id'];

// καθάρισε ληγμένες κρατήσεις
$pdo->exec("DELETE FROM cart_items WHERE reserved_until IS NOT NULL AND reserved_until < NOW()");

// Φέρε ενεργά items καλαθιού
$sql = "SELECT ci.product_id, ci.qty, ci.price, p.name
        FROM cart_items ci
        JOIN products p ON p.id=ci.product_id
        WHERE ci.user_id=? AND (ci.reserved_until IS NULL OR ci.reserved_until >= NOW())";
$st = $pdo->prepare($sql); $st->execute([$uid]); $items = $st->fetchAll();

if (!$items) {
  include __DIR__.'/header.php';
  echo '<div class="alert alert-info">Δεν υπάρχουν προϊόντα στο καλάθι ή έληξε η δέσμευση. <a href="products.php">Επιστροφή στα Προϊόντα</a></div>';
  include __DIR__.'/footer.php'; exit;
}

// Υπολογισμός συνόλου
$total = 0; foreach($items as $i){ $total += $i['qty'] * (float)$i['price']; }

// Default στοιχεία από χρήστη
$u = $pdo->prepare("SELECT name, email FROM users WHERE id=?"); $u->execute([$uid]); $user = $u->fetch();
$full_name = $user['name'] ?? '';
$email = $user['email'] ?? '';

$errors=[]; $success=false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $full_name = trim($_POST['full_name'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $address   = trim($_POST['address'] ?? '');

  if(!$full_name) $errors[]='Γράψε ονοματεπώνυμο.';
  if(!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[]='Δώσε έγκυρο email.';
  if(!$address) $errors[]='Γράψε διεύθυνση παράδοσης/τιμολόγησης.';

  if(!$errors){
    // Δημιουργία παραγγελίας
    $insO = $pdo->prepare("INSERT INTO orders (user_id, total, status, full_name, email, address)
                           VALUES (?, ?, 'paid_virtual', ?, ?, ?)");
    $insO->execute([$uid, $total, $full_name, $email, $address]);
    $orderId = $pdo->lastInsertId();

    // Γραμμές παραγγελίας
    $insI = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
    foreach($items as $i){ $insI->execute([$orderId, $i['product_id'], $i['qty'], $i['price']]); }

    // Άδειασμα καλαθιού
    $pdo->prepare("DELETE FROM cart_items WHERE user_id=?")->execute([$uid]);

    $success = true;
  }
}

include __DIR__.'/header.php';
?>
<h2>Ολοκλήρωση Αγοράς</h2>

<?php if($success): ?>
  <div class="alert alert-success">
    <i class="bi bi-check-circle"></i>
    Η πληρωμή ολοκληρώθηκε (εικονική). Η παραγγελία σου καταχωρήθηκε επιτυχώς.
  </div>
  <p>Δες το ιστορικό σου στη σελίδα <a href="orders.php">Παραγγελίες</a>.</p>
<?php else: ?>
  <?php foreach($errors as $e): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <div class="row g-4">
    <div class="col-md-7">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-person-vcard"></i> Στοιχεία πελάτη</h5>
          <form method="post" class="row g-3">
            <div class="col-12">
              <label class="form-label">Ονοματεπώνυμο</label>
              <input name="full_name" class="form-control" value="<?= htmlspecialchars($full_name) ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label">Email</label>
              <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label">Διεύθυνση</label>
              <textarea name="address" class="form-control" rows="3" required placeholder="Οδός, αριθμός, πόλη, ΤΚ"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Τρόπος Πληρωμής</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" checked disabled>
                <label class="form-check-label">Εικονική πληρωμή (demo)</label>
              </div>
            </div>
            <div class="col-12">
              <button class="btn btn-success"><i class="bi bi-credit-card"></i> Πληρωμή & Καταχώρηση</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title"><i class="bi bi-basket"></i> Σύνοψη Καλαθιού</h5>
          <ul class="list-group list-group-flush">
            <?php foreach($items as $i): ?>
              <li class="list-group-item d-flex justify-content-between">
                <div>
                  <div class="fw-semibold"><?= htmlspecialchars($i['name']) ?></div>
                  <div class="small text-secondary">x<?= (int)$i['qty'] ?> × €<?= number_format($i['price'],2) ?></div>
                </div>
                <div>€<?= number_format($i['qty']*(float)$i['price'],2) ?></div>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="d-flex justify-content-between mt-3">
            <strong>Σύνολο</strong>
            <strong>€<?= number_format($total,2) ?></strong>
          </div>
        </div>
      </div>
      <p class="text-muted mt-2"><i class="bi bi-hourglass-split"></i> Η κράτηση ισχύει 24 ώρες από την τελευταία ενημέρωση του καλαθιού.</p>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__.'/footer.php'; ?>
