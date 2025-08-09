<?php
// professor_theses.php - Προβολή διπλωματικών που επιβλέπει ο καθηγητής

// Σύνδεση (χωρίς config.php)
$conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 'root', '');

// Υποθέτουμε professor_id=1
$professor_id = 1;
$stmt = $conn->prepare("
    SELECT t.title, th.status, th.assignment_date, 
           u.first_name, u.last_name, u.am
    FROM Theses th
    JOIN Topics t ON th.topic_id = t.id
    JOIN Users u ON th.student_id = u.id
    WHERE th.supervisor_id = ?
");
$stmt->execute([$professor_id]);
$theses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Διαχείριση Διπλωματικών</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .thesis { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
        .status { font-weight: bold; }
        .active { color: green; }
        .completed { color: blue; }
    </style>
</head>
<body>
    <h1>Διπλωματικές που Επιβλέπω</h1>
    <?php foreach ($theses as $thesis): ?>
        <div class="thesis">
            <h3><?= htmlspecialchars($thesis['title']) ?></h3>
            <p>Φοιτητής: <?= htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name']) ?> (ΑΜ: <?= $thesis['am'] ?>)</p>
            <p>Κατάσταση: <span class="status <?= $thesis['status'] ?>"><?= $thesis['status'] ?></span></p>
            <p>Ανάθεση: <?= date('d/m/Y', strtotime($thesis['assignment_date'])) ?></p>
        </div>
    <?php endforeach; ?>
    <button onclick="window.history.back()">Πίσω</button>
</body>
</html>