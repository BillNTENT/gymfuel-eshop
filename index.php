<?php
require __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || (int)($_SESSION['is_admin'] ?? 0) !== 1) { header('Location: ../login.php'); exit; }

$userName = $_SESSION['user_name'] ?? ($_SESSION['user']['name'] ?? ($_SESSION['name'] ?? 'Διαχειριστή'));
include __DIR__ . '/../header.php';
?>
<h3 class="mb-3">Καλώς ήρθες, <?= htmlspecialchars($userName) ?></h3>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title">Χρήστες</h5>
        <p class="card-text">Προβολή/τροποποίηση χρηστών, ρόλων και ιστορικού σύνδεσης.</p>
        <a href="users.php" class="btn btn-primary btn-sm">Διαχείριση χρηστών</a>
        <a href="user_sessions.php" class="btn btn-outline-primary btn-sm">Ιστορικό συνδέσεων</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title">Προϊόντα</h5>
        <p class="card-text">Προσθήκη/επεξεργασία προϊόντων και κατηγοριών.</p>
        <a href="products.php" class="btn btn-primary btn-sm">Διαχείριση προϊόντων</a>
        <a href="categories.php" class="btn btn-outline-primary btn-sm">Κατηγορίες</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h5 class="card-title">Παραγγελίες</h5>
        <p class="card-text">Παρακολούθηση παραγγελιών, κατάσταση & ιστορικό.</p>
        <a href="orders.php" class="btn btn-primary btn-sm">Παραγγελίες</a>
        <a href="reports.php" class="btn btn-outline-primary btn-sm">Αναφορές</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
