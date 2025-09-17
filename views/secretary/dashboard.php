<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: ../login.php");
    exit();
}
?>

<h2>Πίνακας Γραμματείας</h2>

<ul>
    <li><a href="view_theses.php">📂 Προβολή Ενεργών & Υπό Εξέταση Διπλωματικών</a></li>
    <li><a href="upload_users.php">📤 Μαζικό Ανέβασμα Φοιτητών/Καθηγητών (JSON)</a></li>
    <li><a href="update_thesis.php">📝 Ενημέρωση Κατάστασης Διπλωματικής</a></li>
</ul>

<a href="../logout.php">Αποσύνδεση</a>
