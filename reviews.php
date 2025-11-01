<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db_connect.php';
$pdo = getPDO();

// Βάση URL του project (αν ο φάκελος δεν λέγεται "gymfuel", άλλαξέ το)
$BASE_URL = '/gymfuel/my_files/';

$user     = $_SESSION['user'] ?? null;
$isLogged = !empty($user);
$userId   = $isLogged ? (int)$user['id'] : 0;

// ----------- Υποβολή φόρμας ----------
$msg = null; $err = null;

if ($isLogged && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // πάρε 10 ερωτήσεις 1..5
    $answers = [];
    for ($i=1; $i<=10; $i++) {
        $val = (int)($_POST["q$i"] ?? 0);
        if ($val < 1 || $val > 5) {
            $err = "Η ερώτηση $i πρέπει να είναι αριθμός 1–5.";
            break;
        }
        $answers[$i] = $val;
    }
    $comments = trim((string)($_POST['comments'] ?? ''));

    if (!$err) {
        $sql = "INSERT INTO evaluation
                    (user_id,q1,q2,q3,q4,q5,q6,q7,q8,q9,q10,comments,created_at)
                VALUES
                    (:uid,:q1,:q2,:q3,:q4,:q5,:q6,:q7,:q8,:q9,:q10,:c,NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $userId,
            ':q1'=>$answers[1], ':q2'=>$answers[2], ':q3'=>$answers[3], ':q4'=>$answers[4], ':q5'=>$answers[5],
            ':q6'=>$answers[6], ':q7'=>$answers[7], ':q8'=>$answers[8], ':q9'=>$answers[9], ':q10'=>$answers[10],
            ':c'  => $comments
        ]);
        $msg = "Ευχαριστούμε για την αξιολόγηση!";
    }
}

// Φέρε τις τελευταίες 20 αξιολογήσεις (προβολή από κάτω – προαιρετικά)
$latest = $pdo->query("
    SELECT e.*, COALESCE(u.full_name,u.name,u.email) AS user_name
    FROM evaluation e
    LEFT JOIN users u ON u.id = e.user_id
    ORDER BY e.created_at DESC
    LIMIT 20
")->fetchAll();

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="el">
<head>
  <meta charset="utf-8">
  <title>Αξιολόγηση | GymFuel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:Arial,Helvetica,sans-serif;margin:0;background:#fafafa;color:#222}
    .wrap{max-width:1000px;margin:24px auto;padding:0 16px}
    h1{margin:12px 0}
    form .q{background:#fff;border:1px solid #e5e5e5;padding:12px;border-radius:8px;margin-bottom:10px}
    label{display:block;font-weight:600;margin:0 0 6px}
    input[type=number], textarea{width:100%;padding:8px;border:1px solid #ccc;border-radius:6px}
    button{padding:10px 14px;border:0;border-radius:8px;background:#1f7ae0;color:#fff;cursor:pointer}
    .card{background:#fff;border:1px solid #eee;padding:12px;border-radius:8px;margin-bottom:8px}
    .muted{color:#666}
    .msg{padding:10px;border-radius:6px;margin:10px 0}
    .ok{background:#e9f7ef;border:1px solid #bde5c8}
    .err{background:#fdecea;border:1px solid #f5c2c0}
    .cta{display:inline-block;margin:8px 0;padding:8px 12px;background:#222;color:#fff;border-radius:6px;text-decoration:none}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Αξιολόγηση ιστοσελίδας</h1>

    <?php if ($msg): ?>
      <div class="msg ok"><?= h($msg) ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
      <div class="msg err"><?= h($err) ?></div>
    <?php endif; ?>

    <?php if (!$isLogged): ?>
      <p>Για να συμπληρώσεις την αξιολόγηση πρέπει να συνδεθείς.</p>
      <a class="cta" href="<?= $BASE_URL ?>login.php">Σύνδεση</a>
    <?php else: ?>
      <form method="post" autocomplete="off" novalidate>
        <?php
          $questions = [
            1 => 'Πόσο εύκολη ήταν η πλοήγηση στο site;',
            2 => 'Πόσο ικανοποιημένος/η είσαι από την ταχύτητα φόρτωσης;',
            3 => 'Πόσο κατανοητές είναι οι πληροφορίες προϊόντων;',
            4 => 'Πόσο εύκολη ήταν η διαδικασία αναζήτησης;',
            5 => 'Πόσο εύχρηστο είναι το καλάθι/checkout;',
            6 => 'Πόσο ικανοποιημένος/η είσαι από το οπτικό design;',
            7 => 'Πόσο σαφείς βρίσκεις τις κατηγορίες/μενού;',
            8 => 'Πόσο πιθανό είναι να προτείνεις το site σε φίλο;',
            9 => 'Πόσο ικανοποιημένος/η είσαι από την ασφάλεια/εμπιστοσύνη;',
            10=> 'Πόσο συνολικά ικανοποιημένος/η είσαι από το site;'
          ];
          foreach ($questions as $i=>$q):
        ?>
        <div class="q">
          <label for="q<?= $i ?>"><?= h($q) ?></label>
          <input type="number" id="q<?= $i ?>" name="q<?= $i ?>" min="1" max="5" required placeholder="1 έως 5">
        </div>
        <?php endforeach; ?>

        <div class="q">
          <label for="comments">Σχόλια (προαιρετικά)</label>
          <textarea id="comments" name="comments" rows="3" placeholder="Τι θα βελτίωνες;"></textarea>
        </div>

        <button type="submit">Υποβολή αξιολόγησης</button>
      </form>
    <?php endif; ?>

    <hr>
    <h2>Πρόσφατες αξιολογήσεις (τελευταίες 20)</h2>
    <?php if (!$latest): ?>
      <p class="muted">Δεν υπάρχουν ακόμα αξιολογήσεις.</p>
    <?php else: foreach ($latest as $r): ?>
      <div class="card">
        <div><strong><?= h($r['user_name'] ?? 'Χρήστης') ?></strong> — <span class="muted"><?= h($r['created_at']) ?></span></div>
        <div class="muted">Σκορ: 
          <?php
            $sum = (int)$r['q1']+(int)$r['q2']+(int)$r['q3']+(int)$r['q4']+(int)$r['q5']+(int)$r['q6']+(int)$r['q7']+(int)$r['q8']+(int)$r['q9']+(int)$r['q10'];
            echo $sum . ' / 50';
          ?>
        </div>
        <?php if (!empty($r['comments'])): ?>
          <div><?= nl2br(h($r['comments'])) ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
</body>
</html>
