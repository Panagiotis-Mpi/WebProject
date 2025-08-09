<?php
// view_topic.php - Αυτόνομη σελίδα προβολής θεμάτων
// Σύνδεση με βάση δεδομένων (χωρίς config.php)
$conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 
               'root', 
               '', 
               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Λήψη όλων των θεμάτων
$stmt = $conn->query("SELECT id, title, summary FROM Topics");
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Διαθέσιμα Θέματα Διπλωματικών</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .topic { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin: 10px 0;
            border-radius: 5px;
        }
        h1 { color: #333; }
    </style>
</head>
<body>

<h1>Διαθέσιμα Θέματα Διπλωματικών</h1>

<?php foreach ($topics as $topic): ?>
    <div class="topic">
        <h3><?= htmlspecialchars($topic['title']) ?></h3>
        <p><?= nl2br(htmlspecialchars($topic['summary'] ?? 'Χωρίς περιγραφή')) ?></p>
    </div>
<?php endforeach; ?>

<!-- Κουμπί επιστροφής -->
<button onclick="window.location.href='dashboard.php'">Πίσω στο Dashboard</button>

</body>
</html>