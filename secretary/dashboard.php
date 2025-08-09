<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: ../login.php");
    exit;
}
?>

<h2>Πίνακας Ελέγχου Γραμματείας</h2>

<ul>
    <li><a href="view_theses.php">Προβολή Διπλωματικών</a></li>
    <li><a href="import_data.php">Εισαγωγή Δεδομένων</a></li>
    <li><a href="update_status.php">Ενημέρωση Κατάστασης</a></li>
    <li><a href="../logout.php">Αποσύνδεση</a></li>
</ul>
