<?php
// api/get_profile.php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$student_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, contact_info FROM Users WHERE id = ?");
    $stmt->execute([$student_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        header("Content-Type: application/json");
        echo json_encode($user);
    } else {
        header("Content-Type: application/json");
        echo json_encode(['error' => 'User not found.']);
    }

} catch (PDOException $e) {
    header("Content-Type: application/json");
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>