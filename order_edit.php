<?php
// admin/order_edit.php
session_start();

// Εδώ έλεγξε ότι ο χρήστης είναι admin
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/header.php';

// Helper: έλεγχος αν υπάρχει στήλη
function columnExists(PDO $pdo, string $table, string $col): bool {
    $sql = "SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?";
    $st = $pdo->prepare($sql);
    $st->execute([$table, $col]);
    return (bool)$st->fetchColumn();
}

// CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    echo "<div class='container mt-4 alert alert-warning'>Λάθος παραγγελία.</div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$statusColExists = columnExists($pdo, 'orders', 'status');

// Ενέργειες POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        echo "<div class='container mt-4 alert alert-danger'>CSRF token άκυρο.</div>";
    } else {

        // Διαγραφή ολόκληρης παραγγελίας
        if (isset($_POST['delete_order'])) {
            $pdo->beginTransaction();
            try {
                $st1 = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                $st1->execute([$order_id]);

                $st2 = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $st2->execute([$order_id]);

                $pdo->commit();
                header("Location: orders.php?msg=" . urlencode("Η παραγγελία #{$order_id} διαγράφηκε."));
                exit;
            } catch (Throwable $e) {
                $pdo->rollBack();
                echo "<div class='container mt-4 alert alert-danger'>Σφάλμα διαγραφής: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }

        // Διαγραφή μεμονωμένου item
        if (isset($_POST['delete_item_id'])) {
            $item_id = (int)$_POST['delete_item_id'];
            try {
                $st = $pdo->prepare("DELETE FROM order_items WHERE id = ? AND order_id = ?");
                $st->execute([$item_id, $order_id]);
                header("Location: order_edit.php?id={$order_id}&msg=" . urlencode("Η γραμμή παραγγελίας διαγράφηκε."));
                exit;
            } catch (Throwable $e) {
                echo "<div class='container mt-4 alert alert-danger'>Σφάλμα: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }

        // Ενημέρωση status (αν υπάρχει στήλη)
        if ($statusColExists && isset($_POST['new_status'])) {
            $new_status = trim($_POST['new_status']);
            try {
                $st = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $st->execute([$new_status, $order_id]);
                header("Location: order_edit.php?id={$order_id}&msg=" . urlencode("Το status ενημερώθηκε."));
                exit;
            } catch (Throwable $e) {
                echo "<div class='container mt-4 alert alert-danger'>Σφάλμα ενημέρωσης: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}

// Φόρτωση κεφαλίδας παραγγελίας
// Προαιρετικά δυναμική στήλη ημερομηνίας
$orderDateCol = columnExists($pdo, 'orders', 'order_date') ? 'order_date' :
               (columnExists($pdo, 'orders', 'created_at') ? 'created_at' : null);

$dateSel = $orderDateCol ? "o.`{$orderDateCol}` AS order_datetime" : "NULL AS order_datetime";

$sqlHead = "
    SELECT o.id, o.user_id, " . ($statusColExists ? "o.status," : "") . " {$dateSel}, u.name AS user_name, u.email
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    WHERE o.id = ?
";
$sth = $pdo->prepare($sqlHead);
$sth->execute([$order_id]);
$order = $sth->fetch();

if (!$order) {
    echo "<div class='container mt-4 alert alert-warning'>Η παραγγελία δεν βρέθηκε.</div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

// Γραμμές παραγγελίας
$stItems = $pdo->prepare("
    SELECT oi.id, oi.product_id, oi.quantity, p.name AS product_name
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
");
$stItems->execute([$order_id]);
$items = $stItems->fetchAll();

?>
<div class="container mt-4">
    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between">
        <h3>Παραγγελία #<?= (int)$order['id']; ?></h3>
        <a href="orders.php" class="btn btn-secondary">Πίσω στη λίστα</a>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <p class="mb-1"><strong>Πελάτης:</strong> <?= htmlspecialchars($order['user_name'] ?? ('User #' . (int)$order['user_id'])) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? '-') ?></p>
            <p class="mb-1"><strong>Ημερομηνία:</strong> <?= $order['order_datetime'] ? htmlspecialchars($order['order_datetime']) : '—' ?></p>

            <?php if ($statusColExists): ?>
                <form method="post" class="row g-2 align-items-end mt-2">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>">
                    <div class="col-auto">
                        <label class="form-label">Κατάσταση</label>
                        <select name="new_status" class="form-select">
                            <?php
                            $current = (string)($order['status'] ?? '');
                            $opts = ['pending' => 'Σε εκκρεμότητα', 'paid' => 'Πληρωμένη', 'shipped' => 'Απεστάλη', 'cancelled' => 'Ακυρωμένη'];
                            foreach ($opts as $val => $label) {
                                $sel = ($current === $val) ? 'selected' : '';
                                echo "<option value=\"{$val}\" {$sel}>{$label}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Αποθήκευση</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <h5 class="mt-4">Προϊόντα Παραγγελίας</h5>
    <?php if ($items): ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Προϊόν</th>
                    <th>Ποσότητα</th>
                    <th style="width:130px;">Ενέργειες</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= (int)$it['id'] ?></td>
                        <td><?= htmlspecialchars($it['product_name']) ?> (ID: <?= (int)$it['product_id'] ?>)</td>
                        <td><?= (int)$it['quantity'] ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Διαγραφή αυτής της γραμμής;');" class="d-inline">
                                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                <input type="hidden" name="delete_item_id" value="<?= (int)$it['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger">Διαγραφή</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Δεν υπάρχουν γραμμές στη συγκεκριμένη παραγγελία.</p>
    <?php endif; ?>

    <div class="mt-4">
        <form method="post" onsubmit="return confirm('Οριστική διαγραφή ΟΛΟΚΛΗΡΗΣ της παραγγελίας;');">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <button name="delete_order" value="1" class="btn btn-danger">
                Διαγραφή παραγγελίας
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
