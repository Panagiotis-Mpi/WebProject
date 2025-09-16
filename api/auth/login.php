<?php
header('Content-Type: application/json');
require '../../db_connection.php'; // προσαρμογή σύμφωνα με το δέντρο: api/auth -> ../../

session_start();

// Ελέγχουμε ότι είναι POST
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Παίρνουμε email & password
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if(empty($email) || empty($password)){
    echo json_encode([
        'success' => false,
        'message' => 'Συμπληρώστε email και password'
    ]);
    exit;
}

// Σύνδεση με βάση
$stmt = $conn->prepare("SELECT * FROM Users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode([
        'success' => false,
        'message' => 'Λάθος email ή password'
    ]);
    exit;
}

$user = $result->fetch_assoc();

if($password !== $user['password']){
    echo json_encode([
        'success' => false,
        'message' => 'Λάθος email ή password'
    ]);
    exit;
}

// Επιτυχής login
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

echo json_encode([
    'success' => true,
    'role' => $user['role'],
    'message' => 'Login επιτυχές'
]);
exit;
?>
