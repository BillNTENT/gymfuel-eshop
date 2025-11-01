<?php
require __DIR__.'/../db.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) { header('Location: ../login.php'); exit; }

$errors=[]; $success=null;

if ($_SERVER['REQUEST_METHOD']==='POST'){
  $action = $_POST['action'] ?? '';
  if($action==='create'){
    $name = trim($_POST['name'] ?? '');
    if(!$name) $errors[]='Δώσε όνομα κατηγορίας.';
    else{
      $st=$pdo->prepare("INSERT INTO categories (name) VALUES (?)");
      $st->execute([$name]);
      $success='Η κατηγορία δημιουργήθηκε.';
    }
  }
  if($action==='rename'){
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if($id && $name){
      $pdo->prepare("UPDATE categories SET name=? WHERE id=?")->execute([$name,$id]);
      $success='Η κατηγορία μετονομάστηκε.';
    }
  }
}

if (($_GET['action']??'')==='delete'){
  $id=intval($_GET['id']??0);
  // Προσοχή: αν υπάρχουν προϊόντα που δείχνουν εδώ, θα αποτύχει (FK). Εναλλακτικά θέσε NULL.
  try{
    $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
    header('Location: categories_manage.php?ok=1'); exit;
  }catch(Exception $e){
    header('Location: categories_manage.php?err=fk'); exit;
  }
}

$rows=$pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

include __DIR__.'/../header.php';
?>
<h2 class="mb-3">Διαχείριση Κατηγοριών</h2>

<?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
<?php foreach($errors as $e): ?><div class="alert alert-danger"><?=htmlspecialchars($e)?></div><?php endforeach; ?>
<?php if(($_GET['ok']??'')==='1'): ?><div class="alert alert-success">Η διαγραφή ολοκληρώθηκε.</div><?php endif; ?>
<?php if(($_GET['err']??'')==='fk'): ?><div class="alert alert-warning">Υπάρχουν προϊόντα που ανήκουν σε αυτή την κατηγορία.</div><?php endif; ?>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title">Νέα κατηγορία</h5>
    <form method="post" class="row g-2">
      <input type="hidden" name="action" value="create">
      <div class="col-md-6"><input name="name" class="form-control" placeholder="Όνομα" required></div>
      <div class="col-md-3"><button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Προσθήκη</button></div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-striped align-middle">
    <thead><tr><th>#</th><th>Όνομα</th><th class="text-end">Ενέργειες</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?=$r['id']?></td>
          <td>
            <form method="post" class="d-flex gap-2">
              <input type="hidden" name="action" value="rename">
              <input type="hidden" name="id" value="<?=$r['id']?>">
              <input name="name" class="form-control" value="<?=htmlspecialchars($r['name'])?>">
              <button class="btn btn-sm btn-outline-primary"><i class="bi bi-save"></i></button>
            </form>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-danger" href="categories_manage.php?action=delete&id=<?=$r['id']?>" onclick="return confirm('Διαγραφή κατηγορίας;');"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__.'/../footer.php'; ?>
