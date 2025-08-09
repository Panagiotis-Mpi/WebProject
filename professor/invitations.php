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
<head><meta charset="UTF-8"><title>Προσκλήσεις Τριμελούς</title></head>
<body>
    <h2>Προσκλήσεις για Επιτροπή</h2>
    <p>Εδώ θα φαίνονται οι προσκλήσεις συμμετοχής σε τριμελή.</p>
</body></html>
