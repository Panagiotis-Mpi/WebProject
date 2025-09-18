<?php
// student/dashboard.php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

?>
