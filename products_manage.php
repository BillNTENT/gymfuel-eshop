<?php
require __DIR__.'/../db.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { header('Location: ../login.php'); exit; }

// Fetch categories for select
$cats = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_KEY_PAIR);

// Handle actions: save (create/update), delete
$errors=[]; $success=null;

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='save') {
  $id   = intval($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $price= floatval($_POST['price'] ?? 0);
  $cat  = intval($_POST['category_id'] ?? 0);
  $desc = trim($_POST['description'] ?? '');

  if(!$name) $errors[]='Δώσε όνομα.';
  if($price<=0) $errors[]='Δώσε θετική τιμή.';
  if(!$cat) $errors[]='Διάλεξε κατηγορία.';

  if(!$errors){
    if($id>0){
      $st=$pdo->prepare("UPDATE products SET name=?, price=?, category_id=?, description=? WHERE id=?");
      $st->execute([$name,$price,$cat,$desc,$id]);
      $success='Το προϊόν ενημερώθηκε.';
    }else{
      $st=$pdo->prepare("INSERT INTO products (name, price, category_id, description) VALUES (?,?,?,?)");
      $st->execute([$name,$price,$cat,$desc]);
      $success='Το προϊόν δημιουργήθηκε.';
    }
  }
}

if (($_GET['action']??'')==='delete'){
  $id=intval($_GET['id']??0);
  $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
  header('Location: products_manage.php?ok=1'); exit;
}

// Load products
$rows = $pdo->query("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC")->fetchAll();

include __DIR__.'/../header.php';
?>
<h2 class="mb-3">Διαχείριση Προϊόντων</h2>

<?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
<?php foreach($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; ?>
<?php if(($_GET['ok']??'')==='1'): ?><div class="alert alert-success">Η διαγραφή ολοκληρώθηκε.</div><?php endif; ?>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title">Προσθήκη / Επεξεργασία</h5>
    <?php
      // Αν πατήθηκε "edit", φόρτωσε τα πεδία
      $editId = intval($_GET['edit'] ?? 0);
      $edit = ['id'=>0,'name'=>'','price'=>'','category_id'=>0,'description'=>''];
      if($editId){
        $st=$pdo->prepare("SELECT * FROM products WHERE id=?"); $st->execute([$editId]); $edit=$st->fetch() ?: $edit;
      }
    ?>
    <form method="post" class="row g-2">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
      <div class="col-md-4">
        <label class="form-label">Όνομα</label>
        <input name="name" class="form-control" value="<?= htmlspecialchars($edit['name']) ?>" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Τιμή (€)</label>
        <input name="price" type="number" step="0.01" min="0" class="form-control" value="<?= htmlspecialchars((string)$edit['price']) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Κατηγορία</label>
        <select name="category_id" class="form-select" required>
          <option value="">— Επιλογή —</option>
          <?php foreach($cats as $cid=>$cname): ?>
            <option value="<?=$cid?>" <?= $edit['category_id']==$cid?'selected':''?>><?=htmlspecialchars($cname)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Περιγραφή</label>
        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit['description']) ?></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-save"></i> Αποθήκευση</button>
        <?php if($editId): ?>
          <a class="btn btn-outline-secondary" href="products_manage.php">Ακύρωση</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead><tr>
      <th>#</th><th>Όνομα</th><th>Κατηγορία</th><th class="text-end">Τιμή</th><th></th>
    </tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=$r['id']?></td>
          <td><?=htmlspecialchars($r['name'])?></td>
          <td><?=htmlspecialchars($r['cat_name'])?></td>
          <td class="text-end">€<?=number_format((float)$r['price'],2)?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="products_manage.php?edit=<?=$r['id']?>"><i class="bi bi-pencil"></i></a>
            <a class="btn btn-sm btn-outline-danger" href="products_manage.php?action=delete&id=<?=$r['id']?>" onclick="return confirm('Διαγραφή προϊόντος;');"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__.'/../footer.php'; ?>
