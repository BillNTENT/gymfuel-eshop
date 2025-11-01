<?php
// admin/orders.php
session_start();

// Έλεγχος admin
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/header.php';

// Helper αν υπάρχει στήλη
function colExists(PDO $pdo, string $table, string $col): bool {
    $sql = "SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?";
    $st = $pdo->prepare($sql);
    $st->execute([$table, $col]);
    return (bool)$st->fetchColumn();
}

$orderDateCol = colExists($pdo, 'orders', 'order_date') ? 'order_date' :
               (colExists($pdo, 'orders', 'created_at') ? 'created_at' : null);
$statusCol    = colExists($pdo, 'orders', 'status');

$dateSel = $orderDateCol ? "o.`{$orderDateCol}` AS order_datetime" : "NULL AS order_datetime";

// λίστα
$sql = "
 SELECT o.id, o.user_id, " . ($statusCol ? "o.status," : "") . " {$dateSel},
        u.name AS user_name, u.email,
        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS items_cnt
 FROM orders o
 LEFT JOIN users u ON u.id = o.user_id
 ORDER BY " . ($orderDateCol ? "o.`{$orderDateCol}` DESC" : "o.id DESC");

$st = $pdo->query($sql);
$rows = $st->fetchAll();
?>
<div class="container mt-4">
    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <h3>Παραγγελίες</h3>
    <?php if ($rows): ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Πελάτης</th>
                    <th>Email</th>
                    <th>Τεμ.</th>
                    <th>Ημερομηνία</th>
                    <?php if ($statusCol): ?><th>Κατάσταση</th><?php endif; ?>
                    <th style="width:160px;">Ενέργειες</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= (int)$r['id'] ?></td>
                        <td><?= htmlspecialchars($r['user_name'] ?? ('User #'.$r['user_id'])) ?></td>
                        <td><?= htmlspecialchars($r['email'] ?? '-') ?></td>
                        <td><?= (int)$r['items_cnt'] ?></td>
                        <td><?= $r['order_datetime'] ? htmlspecialchars($r['order_datetime']) : '—' ?></td>
                        <?php if ($statusCol): ?>
                            <td><?= htmlspecialchars((string)($r['status'] ?? '')) ?></td>
                        <?php endif; ?>
                        <td>
                            <a class="btn btn-sm btn-primary" href="order_edit.php?id=<?= (int)$r['id'] ?>">
                                Προβολή/Επεξεργασία
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Δεν υπάρχουν παραγγελίες.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
