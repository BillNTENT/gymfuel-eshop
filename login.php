<?php
require '../db.php';
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=$_POST['email']??''; $pass=$_POST['password']??'';
  $stmt=$pdo->prepare('SELECT * FROM users WHERE email=? AND is_admin=1'); $stmt->execute([$email]);
  $u=$stmt->fetch();
  if($u && password_verify($pass,$u['password'])){
    $_SESSION['user_id']=$u['id']; $_SESSION['name']=$u['name']; $_SESSION['is_admin']=1;
    header('Location: index.php'); exit;
  } else $errors[]='Λάθος στοιχεία admin.';
}
include '../header.php';
?>
<h2>Admin Σύνδεση</h2>
<?php foreach($errors as $e) echo "<div class='alert alert-danger'>$e</div>";?>
<form method="post" class="row g-3 col-md-6">
  <div class="col-12"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
  <div class="col-12"><label class="form-label">Κωδικός</label><input name="password" type="password" class="form-control" required></div>
  <div class="col-12"><button class="btn btn-dark">Σύνδεση</button></div>
</form>
<?php include '../footer.php'; ?>