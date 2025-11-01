<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Φόρτωση config και δημιουργία $pdo αν δεν υπάρχει
require_once __DIR__ . '/config.php';
if (!isset($pdo) || !($pdo instanceof PDO)) {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        // Προστασία: σε production θα έβαζες φιλικό μήνυμα
        die('DB connection error: ' . htmlspecialchars($e->getMessage()));
    }
}

// Για να εμφανίζεται σωστά το όνομα
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
$isAdmin  = !empty($_SESSION['is_admin']);
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>GymFuel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background-color:#f7f7f9; }
        .navbar-brand { font-weight:700; }
        main.container { padding-top: 1.25rem; padding-bottom: 2rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">GymFuel</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="products.php">Προϊόντα</a></li>
                <li class="nav-item"><a class="nav-link" href="services.php">Υπηρεσίες</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">Σχετικά</a></li>
                <li class="nav-item"><a class="nav-link" href="reviews.php">Αξιολόγηση</a></li>
                <?php if ($isAdmin): ?>
                    <li class="nav-item"><a class="nav-link" href="admin/index.php">Διαχείριση</a></li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="wishlist.php">Wishlist</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Καλάθι</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Το προφίλ μου</a></li>
                    <li class="nav-item">
                        <span class="navbar-text me-2">Logged in ως <?= htmlspecialchars($userName ?? 'Χρήστης'); ?></span>
                    </li>
                    <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Έξοδος</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Σύνδεση</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Εγγραφή</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container">
