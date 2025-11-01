<?php
require __DIR__.'/../db.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { header('Location: ../login.php'); exit; }

// CSV export
if(($_GET['export'] ?? '') === 'csv'){
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="evaluation_export.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['id','user_id','created_at','q1','q2','q3','q4','q5','q6','q7','q8','q9','q10','sus_score','comments','ip','user_agent']);
  $rs = $pdo->query("SELECT * FROM evaluation ORDER BY id DESC");
  foreach($rs as $r){
    // SUS
    $sum=0; for($i=1;$i<=10;$i++){ $v=(int)$r["q$i"]; $sum += ($i%2==1) ? ($v-1) : (5-$v); }
    $sus = round($sum*2.5,1);
    fputcsv($out, [
      $r['id'],$r['user_id'],$r['created_at'],
      $r['q1'],$r['q2'],$r['q3'],$r['q4'],$r['q5'],$r['q6'],$r['q7'],$r['q8'],$r['q9'],$r['q10'],
      $sus, $r['comments'], $r['ip'], $r['user_agent']
    ]);
  }
  fclose($out); exit;
}

// Σύνοψη
$all = $pdo->query("SELECT * FROM evaluation ORDER BY id DESC")->fetchAll();
$total = count($all);
$avgSus = null;
if($total>0){
  $susSum=0;
  foreach($all as $r){
    $sum=0; for($i=1;$i<=10;$i++){ $v=(int)$r["q$i"]; $sum += ($i%2==1) ? ($v-1) : (5-$v); }
    $susSum += $sum*2.5;
  }
  $avgSus = round($susSum/$total,1);
}

// Μέσοι όροι ερώτησης
$qAvg = array_fill(1,10,0);
if($total>0){
  foreach($all as $r){ for($i=1;$i<=10;$i++){ $qAvg[$i] += (int)$r["q$i"]; } }
  for($i=1;$i<=10;$i++) $qAvg[$i] = round($qAvg[$i]/$total,2);
}

include __DIR__.'/../header.php';
?>
<h2 class="mb-3">Αποτελέσματα Αξιολόγησης (SUS)</h2>

<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="card"><div class="card-body">
      <div class="text-secondary small">Σύνολο απαντήσεων</div>
      <div class="fs-3 fw-bold"><?= $total ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card"><div class="card-body">
      <div class="text-secondary small">Μέσο SUS</div>
      <div class="fs-3 fw-bold"><?= $avgSus !== null ? $avgSus : '—' ?></div>
    </div></div>
  </div>
  <div class="col-md-6 d-flex align-items-end justify-content-end">
    <a class="btn btn-outline-primary" href="evaluation_results.php?export=csv"><i class="bi bi-download"></i> Εξαγωγή CSV</a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title">Μέσοι όροι ανά ερώτηση</h5>
    <div class="row">
      <?php for($i=1;$i<=10;$i++): ?>
        <div class="col-md-3 mb-2">
          <div class="d-flex justify-content-between"><span>Q<?= $i ?></span><strong><?= $qAvg[$i] ?: '—' ?></strong></div>
          <div class="progress" style="height:8px">
            <div class="progress-bar" role="progressbar" style="width: <?= ($qAvg[$i]/5)*100 ?>%"></div>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<h5>Πρόσφατες 20 απαντήσεις</h5>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead>
      <tr>
        <th>#</th><th>Ημερομηνία</th><th>User</th><th class="text-end">SUS</th><th>Q1–Q10</th><th>Σχόλιο</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $recent = array_slice($all, 0, 20);
      foreach($recent as $r):
        $sum=0; for($i=1;$i<=10;$i++){ $v=(int)$r["q$i"]; $sum += ($i%2==1) ? ($v-1) : (5-$v); }
        $sus = round($sum*2.5,1);
      ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td><?= $r['user_id'] ? ('#'.$r['user_id']) : 'guest' ?></td>
          <td class="text-end"><strong><?= $sus ?></strong></td>
          <td class="small">
            <?php for($i=1;$i<=10;$i++) echo 'Q'.$i.':'.$r["q$i"].' '; ?>
          </td>
          <td class="small"><?= htmlspecialchars($r['comments'] ?? '') ?></td>
        </tr>
      <?php endforeach; if(!$recent) echo '<tr><td colspan="6" class="text-muted">Δεν υπάρχουν καταχωρήσεις.</td></tr>'; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__.'/../footer.php'; ?>
