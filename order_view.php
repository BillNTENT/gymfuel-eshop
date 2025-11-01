<?php
require __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Επιτρέπεται μόνο σε admin
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
  header('Location: ../login.php');
  exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Φέρε την παραγγελία
$sql = "SELECT o.*, u.name AS customer_name, u.email
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        WHERE o.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
  die("Η παραγγελία δεν βρέθηκε.");
}

// Φέρε τα προϊόντα της παραγγελίας
$sql = "SELECT oi.quantity, oi.price, p.name
        FROM order_items oi
        LEFT JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

include __DIR__ . '/../header.php';
?>

<h2 class="mb-4">Λεπτομέρειες Παραγγελίας #<?= $order['id'] ?></h2>

<div class="mb-4">
  <p><strong>Ημερομηνία:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
  <p><strong>Κατάσταση:</strong> <?= htmlspecialchars($order['status']) ?></p>
  <p><strong>Πελάτης:</strong> <?= htmlspecialchars($order['customer_name'] ?? '—') ?> (<?= htmlspecialchars($order['email'] ?? '—') ?>)</p>
  <p><strong>Σύνολο:</strong> €<?= number_format($order['total'], 2) ?></p>
</div>

<h4>Προϊόντα στην παραγγελία</h4>
<?php if (!$items): ?>
  <p class="text-muted">Δεν υπάρχουν προϊόντα σε αυτή την παραγγελία.</p>
<?php else: ?>
  <table class="table table-bordered align-middle">
    <thead>
      <tr>
        <th>Προϊόν</th>
        <th class="text-center">Ποσότητα</th>
        <th class="text-end">Τιμή</th>
        <th class="text-end">Σύνολο</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td class="text-center"><?= $item['quantity'] ?></td>
          <td class="text-end">€<?= number_format($item['price'], 2) ?></td>
          <td class="text-end">€<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<a href="orders_manage.php" class="btn btn-secondary mt-3">&larr; Επιστροφή στις παραγγελίες</a>

<?php include __DIR__ . '/../footer.php'; ?>
