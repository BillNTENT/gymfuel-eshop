<?php
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = (int)$_SESSION['user_id'];

$msg=''; $err='';

/* Ενέργειες φόρμας */
if ($_SERVER['REQUEST_METHOD']==='POST') {
  try {
    $act = $_POST['action'] ?? '';
    if ($act === 'add') {
      $pid = (int)($_POST['product_id'] ?? 0);
      if ($pid<=0) throw new Exception('Άκυρο προϊόν.');
      $pdo->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (:u,:p)")
          ->execute([':u'=>$user_id, ':p'=>$pid]);
      $msg = 'Το προϊόν προστέθηκε στη Wishlist.';
    } elseif ($act === 'remove') {
      $wid = (int)($_POST['wishlist_id'] ?? 0); // id από τον πίνακα wishlist
      if ($wid<=0) throw new Exception('Άκυρο στοιχείο.');
      $pdo->prepare("DELETE FROM wishlist WHERE id=:id AND user_id=:u")
          ->execute([':id'=>$wid, ':u'=>$user_id]);
      $msg = 'Το προϊόν αφαιρέθηκε από τη Wishlist.';
    } elseif ($act === 'clear') {
      $pdo->prepare("DELETE FROM wishlist WHERE user_id=:u")->execute([':u'=>$user_id]);
      $msg = 'Η Wishlist άδειασε.';
    }
  } catch (Throwable $e) {
    $err = 'Σφάλμα: '.$e->getMessage();
  }
}

/* Λίστα αγαπημένων */
$st = $pdo->prepare("
  SELECT w.id AS wishlist_id, p.id AS product_id, p.name, p.price, p.image
  FROM wishlist w
  JOIN products p ON p.id = w.product_id
  WHERE w.user_id = :u
  ORDER BY w.id DESC
");
$st->execute([':u'=>$user_id]);
$items = $st->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>
<h2 class="mb-3">Η Wishlist μου</h2>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<?php if (!$items): ?>
  <div class="alert alert-info">Η Wishlist είναι άδεια.</div>
  <a class="btn btn-primary" href="products.php">Δες προϊόντα</a>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($items as $it):
      $img = gf_normalize_image($it['image'] ?? '', (int)$it['product_id']);
    ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm">
        <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($it['name']) ?>">
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?= htmlspecialchars($it['name']) ?></h5>
          <p class="fw-bold">€<?= number_format((float)$it['price'],2) ?></p>

          <div class="mt-auto d-flex gap-2">
            <!-- Προσθήκη στο καλάθι -->
            <form method="post" action="cart.php" class="d-inline">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$it['product_id'] ?>">
              <input type="hidden" name="qty" value="1">
              <button class="btn btn-success btn-sm" type="submit">
                <i class="bi bi-cart"></i> Καλάθι
              </button>
            </form>

            <!-- Αφαίρεση από Wishlist -->
            <form method="post" action="wishlist.php" class="d-inline" onsubmit="return confirm('Αφαίρεση από wishlist;');">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="wishlist_id" value="<?= (int)$it['wishlist_id'] ?>">
              <button class="btn btn-outline-danger btn-sm" type="submit">
                <i class="bi bi-x-circle"></i> Αφαίρεση
              </button>
            </form>

            <!-- Λεπτομέρειες -->
            <a class="btn btn-outline-primary btn-sm" href="product.php?id=<?= (int)$it['product_id'] ?>">
              <i class="bi bi-info-circle"></i> Λεπτομέρειες
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <form method="post" class="mt-3" onsubmit="return confirm('Σίγουρα να αδειάσει η wishlist;');">
    <input type="hidden" name="action" value="clear">
    <button class="btn btn-outline-secondary btn-sm">Άδειασμα Wishlist</button>
  </form>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
