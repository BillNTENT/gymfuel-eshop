<?php
require __DIR__ . '/db.php';
require __DIR__ . '/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();
include __DIR__ . '/header.php';
?>
<h2 class="mb-3">Αξιολόγηση Καταστήματος</h2>
<p class="text-muted">Πες μας πώς σου φαίνεται το GymFuel (demo φόρμα).</p>

<form class="card p-3 shadow-sm" method="post" action="">
  <div class="mb-3">
    <label class="form-label">Βαθμολογία (1–5)</label>
    <select class="form-select" name="score">
      <?php for($i=5;$i>=1;$i--): ?>
        <option value="<?= $i ?>"><?= $i ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">Σχόλιο</label>
    <textarea class="form-control" name="comment" rows="3" placeholder="Γράψε τη γνώμη σου..."></textarea>
  </div>
  <button class="btn btn-primary">Υποβολή</button>
</form>

<?php if ($_SERVER['REQUEST_METHOD']==='POST'): ?>
  <div class="alert alert-success mt-3">Ευχαριστούμε για την αξιολόγηση! (εικονική αποθήκευση)</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
