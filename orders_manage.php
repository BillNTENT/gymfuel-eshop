<?php
require __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Επιτρέπεται μόνο σε admin
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
  header('Location: ../login.php');
  exit;
}

// Φέρε όλες τις παραγγελίες
$sql = "SELECT o.id, o.created_at, o.total, o.status, u.name AS customer_name, u.email
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        ORDER BY o.id DESC";
$orders = $pdo->query($sql)->fetchAll();

include __DIR__ . '/../header.php';
?>

<h2 class="mb-4">Διαχείριση Παραγγελιών</h2>

<?php if (!$orders): ?>
  <p class="text-muted">Δεν υπάρχουν καταχωρημένες παραγγελίες.</p>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Ημερομηνία</th>
          <th>Πελάτης</th>
          <th>Email</th>
          <th class="text-end">Σύνολο</th>
          <th>Κατάσταση</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= $o['id'] ?></td>
            <td><?= htmlspecialchars($o['created_at']) ?></td>
            <td><?= htmlspecialchars($o['customer_name'] ?? '—') ?></td>
            <td><?= htmlspecialchars($o['email'] ?? '—') ?></td>
            <td class="text-end">€<?= number_format($o['total'], 2) ?></td>
            <td>
              <span class="badge bg-<?= ($o['status'] === 'paid_virtual' ? 'success' : 'secondary') ?>">
                <?= htmlspecialchars($o['status']) ?>
              </span>
            </td>
            <td class="text-end">
              <a href="order_view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye"></i> Προβολή
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>
