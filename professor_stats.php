<?php
// professor_stats.php - Διορθωμένη έκδοση

// 1. Σύνδεση με βάση (αλλάξτε τα στοιχεία αν χρειάζεται)
$conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 'root', '');

// 2. Βασικά στατιστικά - ΔΙΟΡΘΩΜΕΝΟ SQL (χωρίς σχόλιο μέσα)
$professor_id = 1; // ID καθηγητή - αλλάξτε αν χρειάζεται
$stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(status = 'completed') as completed,
        SUM(status = 'active') as active
    FROM Theses
    WHERE supervisor_id = $professor_id
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Στατιστικά</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            line-height: 1.6;
        }
        .stat-box {
            background: #f0f8ff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <h1>📊 Στατιστικά Καθηγητή</h1>
    
    <div class="stat-box">
        <h3>Συνολικές διπλωματικές: <?= $stats['total'] ?? 0 ?></h3>
    </div>
    
    <div class="stat-box">
        <h3>Ολοκληρωμένες: <?= $stats['completed'] ?? 0 ?></h3>
    </div>
    
    <div class="stat-box">
        <h3>Σε εξέλιξη: <?= $stats['active'] ?? 0 ?></h3>
    </div>
    
    <p><a href="dashboard.php">← Πίσω</a></p>
</body>
</html>