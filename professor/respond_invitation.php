<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit;
}

$inv_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;
$professor_id = $_SESSION['user_id'];

if ($inv_id && in_array($action, ['accept', 'reject'])) {
    $status = $action === 'accept' ? 'accepted' : 'rejected';
    
    $stmt = $pdo->prepare("UPDATE CommitteeMembers 
        SET status = ? 
        WHERE id = ? AND (professor_id1 = ? OR professor_id2 = ?)");
    $stmt->execute([$status, $inv_id, $professor_id, $professor_id]);
    
    header("Location: invitations.php");
    exit;
}
