<?php
require __DIR__ . '/db.php';

$who   = $pdo->query("SELECT DATABASE()")->fetchColumn();
$total = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

echo "<pre>";
echo "DATABASE() = {$who}\n";
echo "products.count = {$total}\n\n";
foreach ($pdo->query("SELECT id, name FROM products ORDER BY id DESC") as $row) {
  echo $row['id'] . " - " . $row['name'] . "\n";
}
echo "</pre>";
