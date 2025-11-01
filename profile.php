<?php
// my_files/profile.php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

/** Helper: έλεγχος αν υπάρχει στήλη στον τρέχοντα schema */
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

/** Επιλογή στήλης ημερομηνίας παραγγελίας */
$orderDateCol = null;
if (columnExists($pdo, 'orders', 'order_date')) {
    $orderDateCol = 'order_date';
} elseif (columnExists($pdo, 'orders', 'created_at')) {
    $orderDateCol = 'created_at';
}

// Φέρε στοιχεία χρήστη
$stUser = $pdo->prepare("SELECT id, name, email, address FROM users WHERE id = ?");
$stUser->execute([$user_id]);
$user = $stUser->fetch();
if (!$user) {
    echo "<div class='container mt-4 alert alert-danger'>Ο χρήστης δεν βρέθηκε.</div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

/** -------- ΕΠΕΞΕΡΓΑΣΙΑ RATING -------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['product_id'])) {
    $rating     = (int)$_POST['rating'];
    $product_id = (int)$_POST['product_id'];

    // Επιτρέπουμε rating μόνο αν το έχει αγοράσει
    $check = $pdo->prepare("
        SELECT COUNT(*)
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? AND oi.product_id = ?
    ");
    $check->execute([$user_id, $product_id]);
    $hasBought = (int)$check->fetchColumn();

    if ($hasBought > 0 && $rating >= 1 && $rating <= 5) {
        // αν δεν υπάρχει ο πίνακας ratings, θα πρέπει να τον δημιουργήσεις (δες σημείωση στο τέλος)
        $ins = $pdo->prepare("
            INSERT INTO ratings (user_id, product_id, rating)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating)
        ");
        try {
            $ins->execute([$user_id, $product_id, $rating]);
            $msg = "Η αξιολόγηση καταχωρήθηκε!";
        } catch (Throwable $e) {
            $msg = "Σφάλμα κατά την καταχώρηση αξιολόγησης: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $msg = "Δεν μπορείτε να αξιολογήσετε προϊόν που δεν έχετε αγοράσει.";
    }
}

/** -------- ΙΣΤΟΡΙΚΟ ΠΑΡΑΓΓΕΛΙΩΝ -------- */
/* Δυναμικά φτιάχνουμε το SELECT για την ημερομηνία */
$dateSelect = $orderDateCol ? ("o.`{$orderDateCol}` AS order_datetime") : ("NULL AS order_datetime");

$sqlOrders = "
    SELECT 
        o.id AS order_id,
        {$dateSelect},
        p.id AS product_id,
        p.name AS product_name,
        oi.quantity
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p     ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY " . ($orderDateCol ? "o.`{$orderDateCol}` DESC" : "o.id DESC");

$stOrders = $pdo->prepare($sqlOrders);
$stOrders->execute([$user_id]);
$orders = $stOrders->fetchAll();
?>
<main class="container mt-4">
    <h2>Το Προφίλ μου</h2>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-1"><strong>Όνομα:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p class="mb-0"><strong>Διεύθυνση:</strong> <?= htmlspecialchars($user['address']) ?></p>
        </div>
        <div class="card-footer bg-light">
            <a class="btn btn-primary" href="edit_profile.php">Επεξεργασία Προφίλ</a>
        </div>
    </div>

    <h3>Ιστορικό Παραγγελιών</h3>
    <?php if ($orders): ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ημερομηνία</th>
                        <th>Προϊόν</th>
                        <th>Ποσ.</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $row): ?>
                    <tr>
                        <td>#<?= (int)$row['order_id'] ?></td>
                        <td><?= $row['order_datetime'] ? htmlspecialchars($row['order_datetime']) : '—' ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= (int)$row['quantity'] ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="product_id" value="<?= (int)$row['product_id'] ?>">
                                <select name="rating" class="form-select form-select-sm" required>
                                    <option value="">—</option>
                                    <?php for ($i=1; $i<=5; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> ★</option>
                                    <?php endfor; ?>
                                </select>
                                <button class="btn btn-sm btn-success" type="submit">OK</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>Δεν βρέθηκαν παραγγελίες.</p>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
