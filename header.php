<?php
// admin/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Προαιρετικά: έλεγχος admin (αν δεν τον κάνεις μέσα σε κάθε σελίδα)
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    // header('Location: ../index.php'); exit;
}

?><!doctype html>
<html lang="el">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GymFuel — Διαχείριση</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f7f7f9;}
    .navbar-brand{font-weight:700}
    .container{max-width:1100px}
  </style>
</head>
<body>
<nav class="navbar navbar-dark navbar-expand-lg bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">GymFuel Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navAdmin">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navAdmin">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="orders.php">Παραγγελίες</a></li>
        <li class="nav-item"><a class="nav-link" href="users.php">Χρήστες</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Προϊόντα</a></li>
        <li class="nav-item"><a class="nav-link" href="categories.php">Κατηγορίες</a></li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="../index.php">Προβολή Site</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-warning" href="../logout.php">Έξοδος</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
