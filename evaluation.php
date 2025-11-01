<?php
require __DIR__.'/db.php';
if (session_status()===PHP_SESSION_NONE) session_start();

$items = [
  1 => 'Θα χρησιμοποιούσα συχνά αυτό το ηλεκτρονικό κατάστημα.',
  2 => 'Το κατάστημα είναι πιο περίπλοκο απ’ όσο θα έπρεπε.',
  3 => 'Το βρήκα εύκολο στη χρήση.',
  4 => 'Χρειάζομαι βοήθεια από τεχνικό άτομο για να το χρησιμοποιήσω.',
  5 => 'Οι λειτουργίες είναι καλά ενσωματωμένες.',
  6 => 'Υπάρχουν πολλές ασυνέπειες.',
  7 => 'Οι περισσότεροι θα μάθαιναν να το χρησιμοποιούν γρήγορα.',
  8 => 'Το βρήκα δυσκίνητο/δύσχρηστο.',
  9 => 'Αισθάνθηκα σίγουρος/η χρησιμοποιώντας το.',
 10 => 'Χρειάζομαι να μάθω πολλά πριν το χρησιμοποιήσω σωστά.',
];

$errors=[]; $done=false; $susScore=null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $vals=[];
  for($i=1;$i<=10;$i++){
    $v = isset($_POST["q$i"]) ? (int)$_POST["q$i"] : 0;
    if($v<1 || $v>5){ $errors[]="Ερώτηση $i: επίλεξε τιμή 1–5."; }
    $vals[$i]=$v;
  }
  $comments = trim($_POST['comments'] ?? '');

  if(!$errors){
    $uid = $_SESSION['user_id'] ?? null;
    $ip  = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua  = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    $sql = "INSERT INTO evaluation (user_id,q1,q2,q3,q4,q5,q6,q7,q8,q9,q10,comments,ip,user_agent)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $pdo->prepare($sql)->execute([
      $uid, $vals[1],$vals[2],$vals[3],$vals[4],$vals[5],$vals[6],$vals[7],$vals[8],$vals[9],$vals[10],
      $comments ?: null, $ip, $ua
    ]);

    // SUS scoring: odd=(v-1), even=(5-v); sum*2.5
    $sum=0;
    for($i=1;$i<=10;$i++){
      $v=$vals[$i];
      $sum += ($i%2==1) ? ($v-1) : (5-$v);
    }
    $susScore = round($sum*2.5,1);
    $done=true;
  }
}

include __DIR__.'/header.php';
?>
<h2>Αξιολόγηση Χρήσης (SUS)</h2>
<p class="text-muted">Παρακαλούμε αξιολόγησε την εμπειρία σου στο GymFuel. Κλίμακα: 1 (διαφωνώ απόλυτα) – 5 (συμφωνώ απόλυτα).</p>

<?php foreach($errors as $e): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
<?php endforeach; ?>

<?php if($done): ?>
  <div class="alert alert-success">
    Ευχαριστούμε! Η αξιολόγησή σου καταχωρήθηκε.
    <br>Το προσωπικό σου σκορ SUS: <strong><?= $susScore ?></strong> / 100
  </div>
  <a class="btn btn-primary" href="products.php">Συνέχεια στις αγορές</a>
<?php else: ?>
  <form method="post" class="mt-3">
    <?php for($i=1;$i<=10;$i++): ?>
      <div class="card mb-2">
        <div class="card-body">
          <div class="fw-semibold mb-2"><?= $i ?>. <?= htmlspecialchars($items[$i]) ?></div>
          <div class="d-flex flex-wrap gap-3">
            <?php for($v=1;$v<=5;$v++): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="q<?= $i ?>" id="q<?= $i ?>_<?= $v ?>" value="<?= $v ?>" required>
                <label class="form-check-label" for="q<?= $i ?>_<?= $v ?>"><?= $v ?></label>
              </div>
            <?php endfor; ?>
          </div>
          <div class="small text-muted mt-1">1=Διαφωνώ απόλυτα … 5=Συμφωνώ απόλυτα</div>
        </div>
      </div>
    <?php endfor; ?>

    <div class="mb-3">
      <label class="form-label">Σχόλιο (προαιρετικό)</label>
      <textarea name="comments" class="form-control" rows="3" maxlength="1000" placeholder="Τι θα βελτίωνες;"></textarea>
    </div>

    <button class="btn btn-success"><i class="bi bi-send"></i> Υποβολή αξιολόγησης</button>
  </form>
<?php endif; ?>

<?php include __DIR__.'/footer.php'; ?>
