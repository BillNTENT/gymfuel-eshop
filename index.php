<?php include 'db.php'; include 'header.php'; ?>

<div class="mb-4 rounded-3" style="background:url('assets/hero_home.jpg') center/cover no-repeat;">
  <div class="container py-5 text-white">
    <h1 class="display-5 fw-bold">Καλώς ήρθες στο GymFuel</h1>
    <p class="col-md-8 fs-5">Συμπληρώματα διατροφής, έτοιμα γεύματα & εξοπλισμός γυμναστικής.</p>
    <a class="btn btn-primary btn-lg" href="products.php">Δες προϊόντα</a>
  </div>
</div>

<h3 class="mb-3">Δημοφιλείς Κατηγορίες</h3>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card card-product">
      <img src="assets/cat_loss_weight.jpg" class="card-img-top" alt="">
      <div class="card-body"><h5>Απώλεια Βάρους</h5>
        <p class="text-secondary small">L-carnitine, fat burners, προγράμματα διατροφής.</p></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-product">
      <img src="assets/cat_muscle_gain.jpg" class="card-img-top" alt="">
      <div class="card-body"><h5>Μυϊκή Μάζα</h5>
        <p class="text-secondary small">Whey, creatine, mass gainers, προπονητικά.</p></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-product">
      <img src="assets/cat_equipment.jpg" class="card-img-top" alt="">
      <div class="card-body"><h5>Εξοπλισμός</h5>
        <p class="text-secondary small">Λάστιχα, kettlebells, στρώματα γυμναστικής.</p></div>
    </div>
  </div>
</div>

<h3 class="mt-4">Διάλεξε στόχο</h3>
<div class="d-flex gap-2 flex-wrap">
  <a class="btn btn-outline-dark" href="products.php?cat=1"><i class="bi bi-lightning-charge"></i> Μυϊκή μάζα</a>
  <a class="btn btn-outline-dark" href="products.php?cat=2"><i class="bi bi-flower1"></i> Ισορροπημένη διατροφή</a>
  <a class="btn btn-outline-dark" href="products.php?cat=3"><i class="bi bi-basket3"></i> Οικιακός εξοπλισμός</a>
</div>

<?php include 'footer.php'; ?>
