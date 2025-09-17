<?php
// api/get_thesis_details_secretary_api.php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary' || !isset($_GET['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized or missing thesis ID.']);
    exit();
}

$thesis_id = $_GET['id'];

try {
    // Βασικές πληροφορίες
    $stmt = $pdo->prepare("
        SELECT t.id, t.status, t.assignment_date, tp.title, tp.summary,
               stu.first_name as student_first, stu.last_name as student_last,
               sup.first_name as sup_first, sup.last_name as sup_last
        FROM Theses t
        JOIN Topics tp ON t.topic_id = tp.id
        JOIN Users stu ON t.student_id = stu.id
        JOIN Users sup ON t.supervisor_id = sup.id
        WHERE t.id = ?
    ");
    $stmt->execute([$thesis_id]);
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thesis) {
        http_response_code(404);
        echo json_encode(['error' => 'Thesis not found.']);
        exit();
    }

    // Υπολογισμός χρόνου από την ανάθεση
    $time_since_assignment = 'N/A';
    if ($thesis['assignment_date']) {
        $now = new DateTime();
        $assigned_date = new DateTime($thesis['assignment_date']);
        $interval = $now->diff($assigned_date);
        $time_since_assignment = $interval->format('%a ημέρες, %h ώρες, %i λεπτά');
    }

    $thesis['time_since_assignment'] = $time_since_assignment;

    // Πληροφορίες τριμελούς επιτροπής
    $committee = [];
    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, cm.role
        FROM CommitteeMembers cm
        JOIN Users u ON cm.professor_id = u.id
        WHERE cm.thesis_id = ?
    ");
    $stmt->execute([$thesis_id]);
    $committee_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Εδώ προσθέτουμε και τον επιβλέποντα, ο οποίος δεν είναι στον πίνακα CommitteeMembers
    $committee[] = ['first_name' => $thesis['sup_first'], 'last_name' => $thesis['sup_last'], 'role' => 'supervisor'];

    // Προσθέτουμε τα υπόλοιπα μέλη
    foreach ($committee_members as $member) {
        $committee[] = ['first_name' => $member['first_name'], 'last_name' => $member['last_name'], 'role' => 'member'];
    }

    $response = [
        'thesis' => $thesis,
        'committee' => $committee
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>