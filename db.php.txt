php
$host = 'localhost';            γιατί δουλεύεις τοπικά
$db   = 'diplomatiki';          το όνομα της βάσης που έκανες import
$user = 'root';                 default χρήστης στο XAMPP
$pass = '';                     συνήθως είναι κενό στο XAMPP (εκτός αν έχεις βάλει password)
$charset = 'utf8mb4';

$dsn = mysqlhost=$host;dbname=$db;charset=$charset;

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDOATTR_ERRMODE            = PDOERRMODE_EXCEPTION,
        PDOATTR_DEFAULT_FETCH_MODE = PDOFETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die(Σφάλμα σύνδεσης με βάση  . $e-getMessage());
}

