<?php
require __DIR__.'/../db.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { header('Location: ../login.php'); exit; }

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// λίστα χρηστών για φίλτρο
$users = $pdo->query("SELECT id,name,email FROM users ORDER BY name")->fetchAll();

// query sessions
$params = [];
$sql = "SELECT us.*, u.name, u.email
        FROM user_sessions us
        LEFT JOIN users u ON u.id=us.user_id";
if($user_id){
  $sql .= " WHERE us.user_id = ?";
  $params[] = $user_id;
}
$sql .= " ORDER BY us.id DESC LIMIT 500";

$st = $pdo->prepare($sql); $st->execute($params); $rows = $st->fetchAll();

include __DIR__.'/../header.php';
?>
<h2 class="mb-3">Ιστορικό Συνδέσεων</h2>

<form class="row g-2 mb-3" method="get">
  <div class="col-md-6">
    <label class="form-label">Χρήστης</label>
    <select name="user_id" class="form-select" onchange="this.form.submit()">
      <option value="0">— Όλοι —</option>
      <?php foreach($users as $u): ?>
        <option value="<?=$u['id']?>" <?= $user_id==$u['id']?'selected':'' ?>>
          <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th><th>Χρήστης</th><th>Email</th><th>Login</th><th>Logout</th>
        <th class="text-end">Διάρκεια</th><th>IP</th><th>User Agent</th>
      </tr>
    </thead>
    <tbody>
      <?php
      function human_duration($sec){ if($sec===null) return '—';
        $h=floor($sec/3600); $m=floor(($sec%3600)/60); $s=$sec%60; return sprintf('%02d:%02d:%02d',$h,$m,$s); }
      foreach($rows as $r): ?>
        <tr>
          <td><?= $r['id'] ?></td>
          <td><?= htmlspecialchars($r['name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['email'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['login_at']) ?></td>
          <td><?= $r['logout_at'] ? htmlspecialchars($r['logout_at']) : '<span class="badge bg-info">ενεργό</span>' ?></td>
          <td class="text-end"><?= human_duration($r['duration_seconds']) ?></td>
          <td><?= htmlspecialchars($r['ip'] ?? '') ?></td>
          <td style="max-width:360px"><div class="small text-muted"><?= htmlspecialchars($r['user_agent'] ?? '') ?></div></td>
        </tr>
      <?php endforeach; if(!$rows) echo '<tr><td colspan="8" class="text-muted">Δεν βρέθηκαν εγγραφές.</td></tr>'; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__.'/../footer.php'; ?>
