<?php
// dashboard.php - Πλήρως λειτουργικός πίνακας καθηγητή

// Σύνδεση με βάση
try {
    $conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Σφάλμα σύνδεσης: " . $e->getMessage());
}

// Ορισμός professor_id (πχ από session)
session_start();
$professor_id = $_SESSION['user_id'] ?? 1; // Πχ 1 για δοκιμή

// Έλεγχος για εκκρεμείς προσκλήσεις
$pending_count = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM CommitteeMembers 
                          WHERE (professor_id1 = ? OR professor_id2 = ?)
                          AND status = 'invited'");
    $stmt->execute([$professor_id, $professor_id]);
    $pending_count = $stmt->fetch()['count'];
} catch (PDOException $e) {
    error_log("Σφάλμα βάσης: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Πίνακας Καθηγητή</title>
    <meta charset="UTF-8">
    <style>
        /* ... (ίδιο style με πριν) ... */
        .option a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Πίνακας Καθηγητή 👨‍🏫</h1>
        
        <div class="option">
            <a href="create_topic.php">
                <div class="option-icon">📝</div>
                <div class="option-text">Δημιουργία Θεμάτων</div>
                <input type="checkbox" disabled>
            </a>
        </div>
        
        <div class="option active">
            <a href="assign_topic.php">
                <div class="option-icon">🎓</div>
                <div class="option-text">Ανάθεση Θέματος σε Φοιτητή</div>
                <input type="checkbox" checked disabled>
            </a>
        </div>
        
        <div class="option">
            <a href="manage_theses.php">
                <div class="option-icon">📂</div>
                <div class="option-text">Διαχείριση Διπλωματικών</div>
                <input type="checkbox" disabled>
            </a>
        </div>
        
        <div class="option">
            <a href="committee_invites.php">
                <div class="option-icon">📩</div>
                <div class="option-text">Προσκλήσεις Τριμελούς</div>
                <?php if ($pending_count > 0): ?>
                    <span class="option-badge"><?= $pending_count ?></span>
                <?php endif; ?>
                <input type="checkbox" disabled>
            </a>
        </div>
        
        <div class="option">
            <a href="statistics.php">
                <div class="option-icon">📊</div>
                <div class="option-text">Προβολή Στατιστικών</div>
                <input type="checkbox" disabled>
            </a>
        </div>
        
        <div class="option active">
            <a href="theses_list.php">
                <div class="option-icon">📋</div>
                <div class="option-text">Προβολή Λίστας Διπλωματικών</div>
                <input type="checkbox" checked disabled>
            </a>
        </div>
        
        <a href="logout.php" class="logout-btn">Αποσύνδεση 🔒</a>
    </div>
</body>
</html>