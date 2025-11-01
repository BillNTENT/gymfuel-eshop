<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ενημέρωση προφίλ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, address = ? WHERE id = ?");
    $stmt->execute([$name, $email, $address, $user_id]);

    header("Location: profile.php");
    exit;
}

// Φόρτωση τρεχόντων στοιχείων
$stmt = $pdo->prepare("SELECT name, email, address FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<main class="container mt-4">
    <h2>Επεξεργασία Προφίλ</h2>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Όνομα</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Διεύθυνση</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
        </div>
        <button type="submit" class="btn btn-success">Αποθήκευση</button>
        <a href="profile.php" class="btn btn-secondary">Άκυρο</a>
    </form>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
