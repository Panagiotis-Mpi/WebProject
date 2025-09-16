<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'role' => $_SESSION['role'],   // χρησιμοποίησε το ίδιο key με το login.php
        'name' => $_SESSION['name'] ?? ''
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
