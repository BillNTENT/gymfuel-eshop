<?php
// my_files/cart.php
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
  header('Location: login.php'); exit;
}
$user_id = (int)$_SESSION['user_id'];

$msg = ''; $err = '';

/* --------- Ενέργειες φορμών (πλέον υποστηρίζει ΚΑΙ add) --------- */
if ($_SERVER['REQUEST_METHOD']==='POST') {
  try {
    $act = $_POST['action'] ?? '';

    if ($act === 'add') {
      $pid = (int)($_POST['product_id'] ?? 0);
      $qty = max(1, (int)($_POST['qty'] ?? 1));

      // Πάρε την τιμή από products
      $st = $pdo->prepare("SELECT price FROM products WHERE id=:id");
      $st->execute([':id'=>$pid]);
      $price = (float)$st->fetchColumn();
      if ($price <= 0) throw new Exception('Άκυρο προϊόν ή τιμή.');

      // Upsert + 24ωρη κράτηση
      $pdo->prepare("
        INSERT INTO cart_items (user_id, product_id, qty, price, reserved_until)
        VALUES (:u,:p,:q,:pr, DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty),
                                price = VALUES(price),
                                reserved_until = VALUES(reserved_until)
      ")->execute([':u'=>$user_id, ':p'=>$pid, ':q'=>$qty, ':pr'=>$price]);

      $msg = 'Το προϊόν προστέθηκε στο καλάθι.';
    }

    if ($act === 'update_qty') {
      $id  = (int)($_POST['item_id'] ?? 0);         // cart_items.id
      $qty = max(1, (int)($_POST['qty'] ?? 1));
      $st = $pdo->prepare("UPDATE cart_items SET qty=:q, reserved_until = DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE id=:id AND user_id=:u");
      $st->execute([':q'=>$qty, ':id'=>$id, ':u'=>$user_id]);
      $msg = 'Η ποσότητα ενημερώθηκε.';
    }

    if ($act === 'remove_item') {
      $id  = (int)($_POST['item_id'] ?? 0);
      $pdo->prepare("DELETE FROM cart_items WHERE id=:id AND user_id=:u")->execute([':id'=>$id, ':u'=>$user_id]);
      $msg = 'Το προϊόν αφαιρέθηκε από το καλάθι.';
    }

    if ($act === 'clear_cart') {
      $pdo->prepare("DELETE FROM cart_items WHERE user_id=:u")->execute([':u'=>$user_id]);
      $msg = 'Το καλάθι άδειασε.';
    }

  } catch (Throwable $e) { $err = 'Σφάλμα: '.$e->getMessage(); }
}

/* --------- Φέρε items (μόνο ενεργές κρατήσεις) --------- */
$sql = "
SELECT ci.id, ci.product_id, ci.qty, ci.price, ci.reserved_until,
       p.name, p.image
FROM cart_items ci
JOIN products p ON p.id = ci.product_id
WHERE ci.user_id = :u
  AND (ci.reserved_until IS NULL OR ci.reserved_until > NOW())
ORDER BY ci.id DESC
";
$st = $pdo->prepare($sql);
$st->execute([':u'=>$user_id]);
$items = $st->fetchAll(PDO::FETCH_ASSOC);

/* --------- Υπολογισμοί --------- */
$subtotal = 0.0;
foreach ($items as $it) { $subtotal += ((float)$it['price'] * (int)$it['qty']); }

include __DIR__ . '/header.php';
?>
<h2 class="mb-3">Καλάθι</h2>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<?php if (!$items): ?>
  <div class="alert alert-info">Το καλάθι σου είναι άδειο.</div>
  <a class="btn btn-primary" href="products.php">Συνέχισε τις αγορές</a>
<?php else: ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="list-group shadow-sm">
        <?php foreach ($items as $it):
          $img = gf_normalize_image($it['image'] ?? '', (int)$it['product_id']);
          $line_total = (float)$it['price'] * (int)$it['qty'];
        ?>
        <div class="list-group-item">
          <div class="d-flex align-items-center gap-3">
            <img src="<?= htmlspecialchars($img) ?>" alt="" style="width:80px;height:80px;object-fit:cover" class="rounded">
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= htmlspecialchars($it['name']) ?></div>
              <div class="text-muted small">Τιμή: €<?= number_format((float)$it['price'],2) ?></div>
              <?php if (!empty($it['reserved_until'])): ?>
                <div class="text-muted small">Δεσμευμένο έως: <?= htmlspecialchars($it['reserved_until']) ?></div>
              <?php endif; ?>
              <div class="d-flex align-items-center mt-2 gap-2">
                <form method="post" class="d-inline-flex align-items-center gap-2">
                  <input type="hidden" name="action" value="update_qty">
                  <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>">
                  <input type="number" name="qty" min="1" value="<?= (int)$it['qty'] ?>" class="form-control form-control-sm" style="width:80px">
                  <button class="btn btn-outline-primary btn-sm">Ενημέρωση</button>
                </form>

                <form method="post" class="d-inline" onsubmit="return confirm('Αφαίρεση προϊόντος;');">
                  <input type="hidden" name="action" value="remove_item">
                  <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>">
                  <button class="btn btn-outline-danger btn-sm">Αφαίρεση</button>
                </form>
              </div>
            </div>
            <div class="text-end ms-auto">
              <div class="fw-bold">€<?= number_format($line_total,2) ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <form method="post" class="mt-3" onsubmit="return confirm('Σίγουρα θες να αδειάσει το καλάθι;');">
        <input type="hidden" name="action" value="clear_cart">
        <button class="btn btn-outline-secondary btn-sm">Άδειασμα καλαθιού</button>
      </form>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Σύνοψη</h5>
          <div class="d-flex justify-content-between">
            <span>Υποσύνολο</span>
            <strong>€<?= number_format($subtotal,2) ?></strong>
          </div>
          <hr>
          <a class="btn btn-success w-100" href="checkout.php">Ολοκλήρωση αγοράς (εικονική)</a>
          <p class="text-muted small mt-2">Τα προϊόντα παραμένουν δεσμευμένα για 24 ώρες από την τελευταία ενημέρωση.</p>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
