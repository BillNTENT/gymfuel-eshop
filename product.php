<?php
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: products.php'); exit; }

/* Προϊόν + στατιστικά αξιολόγησης */
$sql = "
SELECT p.id, p.name, p.description, p.price, p.image,
       c.name AS category_name,
       ROUND(COALESCE(AVG(CASE WHEN r.is_hidden=0 THEN r.rating END),0),1) AS avg_rating,
       COUNT(CASE WHEN r.is_hidden=0 THEN r.id END) AS review_count
FROM products p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN reviews r     ON r.product_id = p.id
WHERE p.id = :id
GROUP BY p.id
";
$st = $pdo->prepare($sql);
$st->execute([':id'=>$id]);
$p = $st->fetch(PDO::FETCH_ASSOC);
if (!$p) { header('Location: products.php'); exit; }

$img = gf_normalize_image($p['image'] ?? '', (int)$p['id']);

/* Ορατές αξιολογήσεις */
$rst = $pdo->prepare("
  SELECT u.name, rv.rating, rv.comment, rv.created_at
  FROM reviews rv
  LEFT JOIN users u ON u.id = rv.user_id
  WHERE rv.product_id = :id AND rv.is_hidden = 0
  ORDER BY rv.created_at DESC
");
$rst->execute([':id'=>$id]);
$reviews = $rst->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>
<div class="row g-4">
  <div class="col-md-6">
    <img src="<?= htmlspecialchars($img) ?>" class="img-fluid rounded shadow-sm" alt="<?= htmlspecialchars($p['name']) ?>">
  </div>
  <div class="col-md-6">
    <div class="small text-primary mb-1"><?= htmlspecialchars($p['category_name'] ?? '') ?></div>
    <h3 class="mb-2"><?= htmlspecialchars($p['name']) ?></h3>
    <div class="mb-2 text-muted">
      <strong><?= number_format((float)$p['avg_rating'],1) ?>/5</strong> (<?= (int)$p['review_count'] ?>)
    </div>
    <p class="lead">€<?= number_format((float)$p['price'],2) ?></p>
    <p><?= nl2br(htmlspecialchars($p['description'] ?? '')) ?></p>

    <div class="d-flex gap-2 mt-3">
      <!-- Καλάθι -->
      <form method="post" action="cart.php" class="d-inline">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <input type="hidden" name="qty" value="1">
        <button class="btn btn-success" type="submit">
          <i class="bi bi-cart"></i> Προσθήκη στο καλάθι
        </button>
      </form>

      <!-- Wishlist -->
      <form method="post" action="wishlist.php" class="d-inline">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <button class="btn btn-outline-danger" type="submit">
          <i class="bi bi-heart"></i> Wishlist
        </button>
      </form>

      <a class="btn btn-outline-secondary" href="products.php">↩ Πίσω στα προϊόντα</a>
    </div>
  </div>
</div>

<hr class="my-4">

<h5>Αξιολογήσεις</h5>
<?php if (!$reviews): ?>
  <div class="text-muted">Δεν υπάρχουν ακόμη αξιολογήσεις.</div>
<?php else: foreach ($reviews as $r): ?>
  <div class="border rounded p-3 mb-2">
    <div class="small text-muted">
      <strong><?= htmlspecialchars($r['name'] ?? 'Χρήστης') ?></strong> •
      <?= (int)$r['rating'] ?>/5 •
      <?= htmlspecialchars($r['created_at']) ?>
    </div>
    <div><?= nl2br(htmlspecialchars($r['comment'] ?? '')) ?></div>
  </div>
<?php endforeach; endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
