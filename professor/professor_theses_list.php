<?php
// professor_theses_list.php - Λίστα διπλωματικών

$conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 'root', '');
$professor_id = 1;

// Λήψη όλων των διπλωματικών
$stmt = $conn->prepare("
    SELECT t.title, th.status, th.assignment_date, 
           u.first_name, u.last_name, u.am,
           TIMESTAMPDIFF(MONTH, th.assignment_date, NOW()) as months_passed
    FROM Theses th
    JOIN Topics t ON th.topic_id = t.id
    JOIN Users u ON th.student_id = u.id
    WHERE th.supervisor_id = ?
    ORDER BY th.status, th.assignment_date DESC
");
$stmt->execute([$professor_id]);
$theses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Λίστα Διπλωματικών</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .thesis-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        .pending { background: #fff3cd; color: #856404; }
        .active { background: #d4edda; color: #155724; }
        .under_review { background: #cce5ff; color: #004085; }
        .completed { background: #e2e3e5; color: #383d41; }
        .back-btn {
            display: inline-block;
            margin: 20px 0;
            padding: 8px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Λίστα Διπλωματικών 📋</h1>
    
    <?php foreach ($theses as $thesis): ?>
        <div class="thesis-card">
            <h3><?= htmlspecialchars($thesis['title']) ?></h3>
            <p>Φοιτητής: <?= htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name']) ?> (ΑΜ: <?= $thesis['am'] ?>)</p>
            <p>Κατάσταση: 
                <span class="status-badge <?= $thesis['status'] ?>">
                    <?= str_replace('_', ' ', $thesis['status']) ?>
                </span>
            </p>
            <p>Ανάθεση: <?= date('d/m/Y', strtotime($thesis['assignment_date'])) ?> (πριν <?= $thesis['months_passed'] ?> μήνες)</p>
        </div>
    <?php endforeach; ?>
    
    <a href="dashboard.php" class="back-btn">Πίσω στον Πίνακα</a>
</body>
</html>