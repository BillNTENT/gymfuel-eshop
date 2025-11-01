<?php
require __DIR__.'/../db.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { header('Location: ../login.php'); exit; }

// Actions: hide/unhide/delete review, clear reports
$act = $_GET['action'] ?? '';
$rid = intval($_GET['id'] ?? 0);

if ($act==='hide' && $rid){ $pdo->prepare("UPDATE reviews SET is_hidden=1 WHERE id=?")->execute([$rid]); header('Location: reviews_moderation.php'); exit; }
if ($act==='unhide' && $rid){ $pdo->prepare("UPDATE reviews SET is_hidden=0 WHERE id=?")->execute([$rid]); header('Location: reviews_moderation.php'); exit; }
if ($act==='delete' && $rid){ $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$rid]); header('Location: reviews_moderation.php'); exit; }
if ($act==='clear_reports' && $rid){ $pdo->prepare("DELETE FROM review_reports WHERE review_id=?")->execute([$rid]); header('Location: reviews_moderation.php'); exit; }

// Fetch reviews with counts
$rows = $pdo->query("
  SELECT r.id, r.product_id, r.user_id, r.rating, r.comment, r.is_hidden, r.updated_at,
         u.name AS user_name, p.name AS product_name,
         (SELECT COUNT(*) FROM review_likes rl WHERE rl.review_id=r.id) AS likes,
         (SELECT COUNT(*) FROM review_reports rr WHERE rr.review_id=r.id) AS reports
  FROM reviews r
  LEFT JOIN users u ON u.id=r.user_id
  LEFT JOIN products p ON p.id=r.product_id
  ORDER BY r.updated_at DESC
")->fetchAll();

include __DIR__.'/../header.php';
?>
<h2 class="mb-3">Moderation Αξιολογήσεων</h2>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Προϊόν</th>
        <th>Χρήστης</th>
        <th>Βαθμ.</th>
        <th>Σχόλιο</th>
        <th>Likes</th>
        <th>Reports</th>
        <th>Ορατό</th>
        <th class="text-end">Ενέργειες</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=$r['id']?></td>
          <td><?=htmlspecialchars($r['product_name'] ?? '—')?> (ID: <?=$r['product_id']?>)</td>
          <td><?=htmlspecialchars($r['user_name'] ?? '—')?> (ID: <?=$r['user_id']?>)</td>
          <td><?= (int)$r['rating'] ?>/5</td>
          <td style="max-width:360px"><div class="small"><?= nl2br(htmlspecialchars($r['comment'])) ?></div></td>
          <td><?=$r['likes']?></td>
          <td><?=$r['reports']?></td>
          <td><?= $r['is_hidden'] ? '<span class="badge bg-secondary">Κρυφό</span>' : '<span class="badge bg-success">Ορατό</span>' ?></td>
          <td class="text-end">
            <?php if($r['is_hidden']): ?>
              <a class="btn btn-sm btn-outline-success" href="reviews_moderation.php?action=unhide&id=<?=$r['id']?>">Επαν.</a>
            <?php else: ?>
              <a class="btn btn-sm btn-outline-warning" href="reviews_moderation.php?action=hide&id=<?=$r['id']?>">Απόκρ.</a>
            <?php endif; ?>
            <a class="btn btn-sm btn-outline-secondary" href="reviews_moderation.php?action=clear_reports&id=<?=$r['id']?>">Καθ. reports</a>
            <a class="btn btn-sm btn-outline-danger" href="reviews_moderation.php?action=delete&id=<?=$r['id']?>" onclick="return confirm('Διαγραφή review;');"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; if(!$rows) echo '<tr><td colspan="9" class="text-muted">Δεν υπάρχουν αξιολογήσεις.</td></tr>'; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__.'/../footer.php'; ?>
