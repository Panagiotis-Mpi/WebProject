<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="el">
<head><meta charset="UTF-8"><title>Διαχείριση Διπλωματικής</title></head>
<body>
    <h2>Διαχείριση Διπλωματικής</h2>
    <p>Εδώ εμφανίζονται οι λειτουργίες ανάλογα με την κατάσταση της διπλωματικής (υπό ανάθεση, υπό εξέταση, περατωμένη).</p>
</body></html>
