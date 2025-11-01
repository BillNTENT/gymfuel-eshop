<?php
/**
 * admin/profile.php
 *
 * Μικρό wrapper ώστε τα admin links προς /admin/profile.php
 * να σε ανακατευθύνουν στο πραγματικό προφίλ χρήστη (/my_files/profile.php).
 * Περιλαμβάνει έλεγχο σύνδεσης και ασφαλή fallback όταν έχουν σταλεί headers.
 */

declare(strict_types=1);

// Ξεκίνα session με ασφάλεια
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Προαιρετικό: φόρτωσε ρυθμίσεις/DB αν τις χρειαστείς στο μέλλον
$configPath = dirname(__DIR__) . '/config.php';
if (is_file($configPath)) {
    require_once $configPath; // δεν απαιτείται για την απλή ανακατεύθυνση, αλλά δεν βλάπτει
}

// Αν δεν είναι συνδεδεμένος χρήστης, στείλ’ τον στη σελίδα σύνδεσης (προσαρμόσε αν έχεις άλλο path)
if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    $loginUrl = '../login.php';
    if (!headers_sent()) {
        header('Location: ' . $loginUrl);
        exit;
    }
    // Fallback αν έχουν σταλεί headers
    echo '<!doctype html><html lang="el"><meta charset="utf-8"><title>Σύνδεση απαιτείται</title>';
    echo '<p>Πρέπει να συνδεθείς για να δεις το προφίλ σου.</p>';
    echo '<p><a href="' . htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') . '">Μετάβαση στη σύνδεση</a></p>';
    echo '</html>';
    exit;
}

// Στόχος: το πραγματικό προφίλ χρήστη εκτός admin
$profileUrl = '../profile.php';

// Κάνε ανακατεύθυνση (προτιμάται)
if (!headers_sent()) {
    header('Location: ' . $profileUrl);
    exit;
}

// Fallback αν έχουν σταλεί ήδη headers
?>
<!doctype html>
<html lang="el">
<head>
  <meta charset="utf-8">
  <title>Το προφίλ μου</title>
  <meta http-equiv="refresh" content="0; url=<?php echo htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:2rem;color:#222}
    a{color:#0a7; text-decoration:none}
    a:hover{text-decoration:underline}
  </style>
</head>
<body>
  <p>Μεταφορά στο προφίλ… Αν δεν γίνει αυτόματα, πάτησε εδώ:
     <a href="<?php echo htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8'); ?>">Το προφίλ μου</a></p>
</body>
</html>
