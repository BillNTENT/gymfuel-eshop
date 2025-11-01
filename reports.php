<?php
require __DIR__ . '/../db.php';
require __DIR__ . '/../functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id']) || (int)($_SESSION['is_admin'] ?? 0) !== 1) {
  header('Location: ../login.php'); exit;
}

// Ασφαλή counts (αν δεν υπάρχουν κάποιοι πίνακες, πέφτει σε 0)
function safe_count(PDO $pdo, $sql) {
  try { return (int)$pdo->query($sql)->fetchColumn(); }
  catch(Throwable $e) { return 0; }
}

$users      = safe_count($pdo, "SELECT COUNT(*) FROM users");
$admins     = safe_count($pdo, "SELECT COUNT(*) FROM users WHERE is_admin=1");
$products   = safe_count($pdo, "SELECT COUNT(*) FROM products");
$categories = safe_count($pdo, "SELECT COUNT(*) FROM categories");
$reviews    = safe_count($pdo, "SELECT COUNT(*) FROM reviews");
$wish       = safe_count($pdo, "SELECT COUNT(*) FROM wishlist");
$cart       = safe_count($pdo, "SELECT COUNT(*) FROM cart_items");
$sessions   = safe_count($pdo, "SELECT COUNT(*) FROM user_sessions");

include __DIR__ . '/../header.php';
?>
<h3 class="mb-3">Αναφορές</h3>
<p class="text-muted">Στιγμιότυπο βασικών μετρικών του καταστήματος.</p>

<div class="row g-3">
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small">Χρήστες</div>
        <div class="display-6"><?= $users ?></div>
        <div class="text-muted small">Admins: <?= $admins ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small">Προϊόντα</div>
        <div class="display-6"><?= $products ?></div>
        <div class="text-muted small">Κατηγορίες: <?= $categories ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small">Αξιολογήσεις</div>
        <div class="display-6"><?= $reviews ?></div>
        <div class="text-muted small">Wishlist items: <?= $wish ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small">Καλάθι / Sessions</div>
        <div class="display-6"><?= $cart ?></div>
        <div class="text-muted small">Συνδέσεις: <?= $sessions ?></div>
      </div>
    </div>
  </div>
</div>

<hr class="my-4">

<h5>Τελευταίες 10 συνδέσεις</h5>
<?php
try {
  $q = $pdo->query("
    SELECT s.login_at, s.logout_at, u.name, u.email
    FROM user_sessions s 
    JOIN users u ON u.id = s.user_id
    ORDER BY s.id DESC LIMIT 10
  ");
  $rows = $q->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $rows = []; }
?>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead><tr><th>Χρήστης</th><th>Email</th><th>Είσοδος</th><th>Έξοδος</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td><?= htmlspecialchars($r['login_at']) ?></td>
          <td><?= htmlspecialchars($r['logout_at'] ?? '-') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?>
        <tr><td colspan="4" class="text-muted">Δεν υπάρχουν δεδομένα.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
