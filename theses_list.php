<?php
// theses_list.php - Λίστα διπλωματικών

try {
    $conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 'root', '');
    $professor_id = $_SESSION['user_id'] ?? 1; // Πχ 1 για δοκιμή

    $stmt = $conn->prepare("
        SELECT t.title, th.status, th.assignment_date, 
               u.first_name, u.last_name, u.am
        FROM Theses th
        JOIN Topics t ON th.topic_id = t.id
        JOIN Users u ON th.student_id = u.id
        WHERE th.supervisor_id = ?
        ORDER BY th.status, th.assignment_date DESC
    ");
    $stmt->execute([$professor_id]);
    $theses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Σφάλμα βάσης: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Λίστα Διπλωματικών</title>
    <meta charset="UTF-8">
    <style>
        /* ... (ίδιο style με πριν) ... */
    </style>
</head>
<body>
    <h1>Λίστα Διπλωματικών 📋</h1>
    
    <?php if (!empty($theses)): ?>
        <?php foreach ($theses as $thesis): ?>
            <div class="thesis-card">
                <h3><?= htmlspecialchars($thesis['title']) ?></h3>
                <p>Φοιτητής: <?= htmlspecialchars($thesis['first_name'].' '.$thesis['last_name']) ?> (ΑΜ: <?= $thesis['am'] ?>)</p>
                <p>Κατάσταση: <span class="status-badge <?= $thesis['status'] ?>"><?= $thesis['status'] ?></span></p>
                <p>Ανάθεση: <?= date('d/m/Y', strtotime($thesis['assignment_date'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Δεν βρέθηκαν διπλωματικές.</p>
    <?php endif; ?>
    
    <a href="dashboard.php" class="back-btn">Πίσω στον Πίνακα</a>
</body>
</html>