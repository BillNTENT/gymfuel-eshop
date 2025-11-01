<?php
require '../db.php';
if(!isset($_SESSION['is_admin'])||!$_SESSION['is_admin']){ header('Location: login.php'); exit; }
$action=$_POST['action']??($_GET['action']??'');
if($action==='create'){
  $pdo->prepare('INSERT INTO products(name,description,price,category_id) VALUES(?,?,?,?)')
      ->execute([$_POST['name'],$_POST['description'],$_POST['price'],$_POST['category_id']]);
}
if($action==='delete'){
  $pdo->prepare('DELETE FROM products WHERE id=?')->execute([intval($_GET['id']??0)]);
}
include '../header.php';
$cats=$pdo->query('SELECT id,name FROM categories')->fetchAll();
$prods=$pdo->query('SELECT p.*,c.name AS cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC')->fetchAll();
?>
<h2>Διαχείριση Προϊόντων</h2>
<form method="post" class="row g-2 mb-4">
  <input type="hidden" name="action" value="create">
  <div class="col-md-3"><input name="name" class="form-control" placeholder="Όνομα" required></div>
  <div class="col-md-3"><input name="price" type="number" step="0.01" class="form-control" placeholder="Τιμή" required></div>
  <div class="col-md-3">
    <select name="category_id" class="form-select">
      <?php foreach($cats as $c): ?><option value="<?=$c['id']?>"><?=$c['name']?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-12"><textarea name="description" class="form-control" placeholder="Περιγραφή" rows="2"></textarea></div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Προσθήκη</button></div>
</form>
<table class="table table-striped">
  <tr><th>ID</th><th>Όνομα</th><th>Τιμή</th><th>Κατηγορία</th><th></th></tr>
  <?php foreach($prods as $p): ?>
  <tr>
    <td><?=$p['id']?></td><td><?=$p['name']?></td><td>€<?=number_format($p['price'],2)?></td><td><?=$p['cat_name']?></td>
    <td><a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?=$p['id']?>" onclick="return confirm('Διαγραφή;')">Διαγραφή</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php include '../footer.php'; ?>