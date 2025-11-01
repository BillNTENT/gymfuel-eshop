<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/** PHP 7 συμβατότητα για str_starts_with */
if (!function_exists('str_starts_with')) {
  function str_starts_with($haystack, $needle) {
    return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
  }
}

/** Ασφαλής ανάγνωση ονόματος χρήστη από διάφορα πιθανά session keys */
function gf_session_user_name(): ?string {
  return $_SESSION['user_name']
      ?? ($_SESSION['user']['name'] ?? ($_SESSION['name'] ?? null));
}

/** Επιστρέφει image URL από ΒΔ ή βρίσκει fallback με βάση το id */
function gf_normalize_image(?string $dbImage, int $id): string {
  $dbImage = trim((string)$dbImage);
  if ($dbImage !== '') {
    $lower = strtolower($dbImage);
    if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://') ||
        str_starts_with($lower, 'assets/') || str_starts_with($lower, '/')) {
      return $dbImage;
    }
    return 'assets/' . ltrim($dbImage, '/');
  }
  return gf_find_product_image_by_id($id);
}

/** Fallback εύρεσης εικόνας σύμφωνα με pattern αρχείων */
function gf_find_product_image_by_id(int $id): string {
  $dir = __DIR__ . '/assets';
  $base = 'assets';
  $id2 = str_pad((string)$id, 2, '0', STR_PAD_LEFT);

  $patterns = [
    "{$dir}/prod_{$id2}_*.jpg","{$dir}/prod_{$id2}_*.jpeg","{$dir}/prod_{$id2}_*.png","{$dir}/prod_{$id2}_*.webp",
    "{$dir}/prod_{$id}_*.jpg","{$dir}/prod_{$id}_*.jpeg","{$dir}/prod_{$id}_*.png","{$dir}/prod_{$id}_*.webp",
  ];
  foreach ($patterns as $pat) { $m = glob($pat); if ($m && file_exists($m[0])) return $base.'/'.basename($m[0]); }
  foreach (['jpg','jpeg','png','webp'] as $ext) { $c="{$dir}/prod_{$id2}.{$ext}"; if (file_exists($c)) return $base.'/'.basename($c); }
  return $base.'/no-image.png';
}
