<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../../db_connection.php';

// Έλεγχος ρόλου
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary'){
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Μη εξουσιοδοτημένη πρόσβαση"]);
    exit;
}

// Ανάγνωση JSON
$data = json_decode(file_get_contents("php://input"), true);
if(!$data){
    echo json_encode(["success"=>false,"message"=>"Το αρχείο JSON είναι άδειο ή μη έγκυρο"]);
    exit;
}

// Προετοιμασία statement
$stmt = $conn->prepare("
    INSERT INTO Users (email, password, first_name, last_name, role, am, phone)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        first_name=VALUES(first_name),
        last_name=VALUES(last_name),
        role=VALUES(role),
        am=VALUES(am),
        phone=VALUES(phone)
");

$inserted = 0;
$updated = 0;

function handleUsers($users, $role, $stmt, &$inserted, &$updated){
    foreach($users as $u){
        // Απορρίπτουμε οποιονδήποτε με role 'secretary'
        if(isset($u['role']) && $u['role'] === 'secretary'){
            continue;
        }

        $email = $u['email'] ?? null;
        $password = isset($u['password']) ? password_hash($u['password'], PASSWORD_BCRYPT) : null;
        $first_name = $u['first_name'] ?? null;
        $last_name = $u['last_name'] ?? null;
        $am = $role === 'student' ? ($u['am'] ?? null) : null;
        $phone = $u['phone'] ?? null;

        if(!$email || !$password || !$first_name || !$last_name){
            continue;
        }

        $stmt->bind_param("sssssss", $email, $password, $first_name, $last_name, $role, $am, $phone);

        if($stmt->execute()){
            if($stmt->affected_rows === 1){
                $inserted++;
            } elseif($stmt->affected_rows === 2){
                $updated++;
            }
        }
    }
}

if(isset($data['students'])){
    handleUsers($data['students'], 'student', $stmt, $inserted, $updated);
}
if(isset($data['professors'])){
    handleUsers($data['professors'], 'professor', $stmt, $inserted, $updated);
}


$stmt->close();
$conn->close();

echo json_encode([
    "success"=>true,
    "message"=>"Ολοκληρώθηκε: $inserted νέες εγγραφές, $updated ενημερώσεις."
]);
