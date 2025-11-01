<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../db_connect.php';
$pdo = getPDO();

// Βεβαιώσου ότι ο admin είναι συνδεδεμένος
$user = $_SESSION['user'] ?? null;
if (empty($user) || ($user['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Φέρνουμε όλες τις αξιολογήσεις (ή τις τελευταίες 500 για απόδοση)
$sql = "
SELECT e.*, COALESCE(u.full_name,u.name,u.email) AS user_name
FROM evaluation e
LEFT JOIN users u ON u.id = e.user_id
ORDER BY e.created_at DESC
LIMIT 500
";
$rows = $pdo->query($sql)->fetchAll();

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="el">
<head>
  <meta charset="utf-8">
  <title>Admin · Αξιολογήσεις</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#f7f7f7;color:#222}
    header{background:#0f1720;color:#fff;padding:12px 16px}
    .wrap{max-width:1200px;margin:0 auto}
    h1{margin:0}
    .table{width:100%;border-collapse:collapse;background:#fff;margin:16px 0;border:1px solid #e5e5e5}
    .table th,.table td{padding:10px;border-bottom:1px solid #eee;vertical-align:top}
    .muted{color:#666}
    .badge{display:inline-block;padding:2px 8px;border-radius:10px;background:#eef;border:1px solid #dde}
    .sum{font-weight:700}
  </style>
</head>
<body>
  <header>
    <div class="wrap">
      <h1>Αξιολογήσεις χρηστών</h1>
      <div class="muted">Μέχρι 500 πιο πρόσφατες</div>
    </div>
  </header>

  <div class="wrap">
    <?php if (!$rows): ?>
      <p class="muted">Δεν υπάρχουν αξιολογήσεις.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Χρήστης</th>
            <th>Ημ/νία</th>
            <th>Q1–Q10</th>
            <th>Σύνολο</th>
            <th>Σχόλια</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r):
            $sum = (int)$r['q1']+(int)$r['q2']+(int)$r['q3']+(int)$r['q4']+(int)$r['q5']+
                   (int)$r['q6']+(int)$r['q7']+(int)$r['q8']+(int)$r['q9']+(int)$r['q10'];
          ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?= h($r['user_name'] ?? '—') ?></td>
              <td class="muted"><?= h($r['created_at']) ?></td>
              <td class="muted">
                Q1:<?= (int)$r['q1'] ?>,
                Q2:<?= (int)$r['q2'] ?>,
                Q3:<?= (int)$r['q3'] ?>,
                Q4:<?= (int)$r['q4'] ?>,
                Q5:<?= (int)$r['q5'] ?>,
                Q6:<?= (int)$r['q6'] ?>,
                Q7:<?= (int)$r['q7'] ?>,
                Q8:<?= (int)$r['q8'] ?>,
                Q9:<?= (int)$r['q9'] ?>,
                Q10:<?= (int)$r['q10'] ?>
              </td>
              <td><span class="badge sum"><?= $sum ?>/50</span></td>
              <td><?= nl2br(h($r['comments'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
