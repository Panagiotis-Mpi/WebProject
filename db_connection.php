<?php
// db_connection.php

$host = "localhost";       
$db   = "diplomatiki";     
$user = "root";           
$pass = "";             

$conn = new mysqli($host, $user, $pass, $db);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Σφάλμα σύνδεσης στη βάση: " . $conn->connect_error]));
}

// Ρύθμιση χαρακτήρων UTF-8 για σωστή εμφάνιση ελληνικών
$conn->set_charset("utf8");
?>
