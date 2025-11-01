<?php
require 'db.php'; if(!isset($_SESSION['user_id'])){ header('Location: login.php'); exit; }
include 'header.php';
$uid=$_SESSION['user_id'];
$orders=$pdo->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC');
$orders->execute([$uid]); $orders=$orders->fetchAll();
?>
<h2>Ιστορικό Παραγγελιών</h2>
<?php if(!$orders): ?><p>Δεν έχεις παραγγελίες ακόμα.</p><?php endif; ?>
<?php foreach($orders as $o): ?>
  <div class="card mb-3"><div class="card-body">
    <div class="d-flex justify-content-between">
      <div><strong>#<?=$o['id']?></strong> • Κατάσταση: <?=$o['status']?> • Σύνολο: €<?=number_format($o['total'],2)?></div>
      <div>Ημ/νία: <?=$o['created_at']?></div>
    </div>
    <?php
      $it=$pdo->prepare('SELECT oi.quantity,oi.price,p.name FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE order_id=?');
      $it->execute([$o['id']]);
    ?>
    <ul class="mt-2">
      <?php foreach($it as $row): ?>
        <li><?=$row['name']?> × <?=$row['quantity']?> — €<?=number_format($row['price'],2)?></li>
      <?php endforeach; ?>
    </ul>
  </div></div>
<?php endforeach; ?>
<?php include 'footer.php'; ?>