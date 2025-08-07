<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="el">
<head><meta charset="UTF-8"><title>Διαχείριση Διπλωματικών</title></head>
<body>
    <h2>Λίστα Διπλωματικών</h2>
    <p>Εδώ εμφανίζεται λίστα διπλωματικών εργασιών με δυνατότητα φιλτραρίσματος.</p>
</body></html>
