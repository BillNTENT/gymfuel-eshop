<?php
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$q           = trim($_GET['q'] ?? '');
$category_id = isset($_GET['cat']) && $_GET['cat'] !== '' ? (int)$_GET['cat'] : null;
$min_rating  = isset($_GET['min_rating']) && $_GET['min_rating'] !== '' ? (float)$_GET['min_rating'] : null;

$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 9;
$offset   = ($page - 1) * $per_page;

$cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$where  = [];
$params = [];
if ($q !== '') {
    $where[] = "(p.name LIKE :q OR p.description LIKE :q)";
    $params[':q'] = "%{$q}%";
}
if ($category_id) {
    $where[] = "p.category_id = :cat";
    $params[':cat'] = $category_id;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stCount = $pdo->prepare("SELECT COUNT(*) FROM products p {$whereSql}");
$stCount->execute($params);
$total_rows  = (int)$stCount->fetchColumn();
$total_pages = max(1, (int)ceil($total_rows / $per_page));

$sql = "
SELECT 
  p.id, p.name, p.description, p.price, p.image,
  c.name AS category_name,
  ROUND(COALESCE(AVG(CASE WHEN r.is_hidden = 0 THEN r.rating END), 0), 1) AS avg_rating,
  COUNT(CASE WHEN r.is_hidden = 0 THEN r.id END) AS review_count
FROM products p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN reviews r     ON r.product_id = p.id
{$whereSql}
GROUP BY p.id
";
if ($min_rating !== null) {
    $sql .= " HAVING avg_rating >= :min_rating";
    $params[':min_rating'] = $min_rating;
}
$sql .= " ORDER BY p.id DESC LIMIT :offset, :per_page";

$st = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    if ($k === ':cat') $st->bindValue($k, (int)$v, PDO::PARAM_INT);
    else               $st->bindValue($k, $v, PDO::PARAM_STR);
}
$st->bindValue(':offset', $offset, PDO::PARAM_INT);
$st->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$st->execute();
$products = $st->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>
<h2 class="mb-3">Προϊόντα</h2>

<form class="row g-2 align-items-center mb-3" method="get" action="products.php">
  <div class="col-md-6">
    <input type="text" class="form-control" name="q" placeholder="Αναζήτηση..." value="<?= htmlspecialchars($q) ?>">
  </div>
  <div class="col-md-3">
    <select name="cat" class="form-select">
      <option value="">Όλες οι κατηγορίες</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= ($category_id === (int)$c['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <select name="min_rating" class="form-select">
      <option value="">Από αξιολόγηση...</option>
      <?php foreach ([5,4.5,4,3.5,3,2.5,2,1.5,1] as $r): ?>
        <option value="<?= $r ?>" <?= ($min_rating!==null && (float)$min_rating===$r) ? 'selected' : '' ?>>≥ <?= $r ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-1 d-grid">
    <button class="btn btn-primary">Φίλτρο</button>
  </div>
</form>

<?php if (!$products): ?>
  <div class="alert alert-info">Δεν βρέθηκαν προϊόντα με τα συγκεκριμένα φίλτρα.</div>
<?php endif; ?>

<div class="row g-4">
<?php foreach ($products as $p): 
  $img = gf_normalize_image($p['image'] ?? '', (int)$p['id']);
  $avg = (float)$p['avg_rating']; $cnt = (int)$p['review_count'];
  $full  = (int)floor($avg);
  $half  = ($avg - $full >= 0.5) ? 1 : 0;
  $empty = 5 - $full - $half;
?>
  <div class="col-12 col-sm-6 col-lg-4">
    <div class="card h-100 shadow-sm">
      <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
      <div class="card-body d-flex flex-column">
        <div class="small text-primary mb-1"><?= htmlspecialchars($p['category_name'] ?? '') ?></div>
        <h5 class="card-title mb-1"><?= htmlspecialchars($p['name']) ?></h5>
        <div class="mb-2">
          <span class="text-warning">
            <?= str_repeat('★', $full) ?><?= $half ? '☆' : '' ?><?= str_repeat('☆', $empty) ?>
          </span>
          <span class="text-muted small">(<?= number_format($avg,1) ?> / 5, <?= $cnt ?>)</span>
        </div>
        <p class="text-muted small flex-grow-1">
          <?= htmlspecialchars(mb_strimwidth($p['description'] ?? '', 0, 140, '…', 'UTF-8')) ?>
        </p>
        <div class="d-flex justify-content-between align-items-center">
          <div class="fw-bold">€<?= number_format((float)$p['price'], 2) ?></div>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-primary btn-sm" href="product.php?id=<?= (int)$p['id'] ?>">
              <i class="bi bi-info-circle"></i> Λεπτομέρειες
            </a>

            <!-- Καλάθι -->
            <form method="post" action="cart.php" class="d-inline">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="qty" value="1">
              <button class="btn btn-success btn-sm" type="submit" title="Προσθήκη στο καλάθι">
                <i class="bi bi-cart"></i>
              </button>
            </form>

            <!-- Wishlist -->
            <form method="post" action="wishlist.php" class="d-inline">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-warning btn-sm" type="submit" title="Προσθήκη στη Wishlist">
                <i class="bi bi-heart"></i>
              </button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php if ($total_pages > 1): ?>
<nav class="mt-4">
  <ul class="pagination">
    <?php
      $base = $_GET;
      $base['page'] = 1;                     $firstUrl = 'products.php?' . http_build_query($base);
      $base['page'] = max(1, $page-1);       $prevUrl  = 'products.php?' . http_build_query($base);
      $base['page'] = min($total_pages, $page+1); $nextUrl  = 'products.php?' . http_build_query($base);
      $base['page'] = $total_pages;          $lastUrl  = 'products.php?' . http_build_query($base);
    ?>
    <li class="page-item <?= $page<=1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $firstUrl ?>">« Πρώτη</a></li>
    <li class="page-item <?= $page<=1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $prevUrl ?>">‹ Προηγ.</a></li>
    <li class="page-item disabled"><span class="page-link">Σελίδα <?= $page ?> / <?= $total_pages ?></span></li>
    <li class="page-item <?= $page>=$total_pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= $nextUrl ?>">Επόμενη ›</a></li>
    <li class="page-item <?= $page>=$total_pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= $lastUrl ?>">Τελευταία »</a></li>
  </ul>
</nav>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
