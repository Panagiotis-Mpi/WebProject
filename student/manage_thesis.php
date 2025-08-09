<?php
// manage_thesis.php - Αυτόνομη σελίδα διαχείρισης διπλωματικής

// 1. Σύνδεση με βάση (χωρίς config.php)
try {
    $conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 
                   'root', 
                   '', 
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Σφάλμα σύνδεσης: " . $e->getMessage());
}

// 2. Λήψη δεδομένων διπλωματικής (υποθέτουμε student_id=11 για παράδειγμα)
$student_id = 11; // Αλλάξτε με το ID του φοιτητή σας

$stmt = $conn->prepare("
    SELECT t.title, th.status, th.assignment_date, 
           u.first_name, u.last_name 
    FROM Theses th
    JOIN Topics t ON th.topic_id = t.id
    JOIN Users u ON th.supervisor_id = u.id
    WHERE th.student_id = ?
");
$stmt->execute([$student_id]);
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Διαχείριση Διπλωματικής</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .thesis-card { 
            border: 1px solid #ddd; 
            padding: 20px; 
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 { color: #2c3e50; }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
        .pending { background: #f39c12; color: white; }
        .active { background: #2ecc71; color: white; }
        .under_review { background: #3498db; color: white; }
        .completed { background: #9b59b6; color: white; }
    </style>
</head>
<body>

<h1>Διαχείριση Διπλωματικής Εργασίας</h1>

<?php if ($thesis): ?>
    <div class="thesis-card">
        <h2><?= htmlspecialchars($thesis['title']) ?></h2>
        
        <p><strong>Κατάσταση:</strong> 
            <span class="status <?= $thesis['status'] ?>">
                <?= ucfirst(str_replace('_', ' ', $thesis['status'])) ?>
            </span>
        </p>
        
        <p><strong>Ημερομηνία Ανάθεσης:</strong> 
            <?= date('d/m/Y', strtotime($thesis['assignment_date'])) ?>
        </p>
        
        <p><strong>Επιβλέπων:</strong> 
            <?= htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name']) ?>
        </p>
        
        <h3>Διαθέσιμες Ενέργειες:</h3>
        <ul>
            <li><a href="upload_thesis.php">Ανέβασμα Αρχείου</a></li>
            <li><a href="view_thesis.php">Προβολή Υποβληθείσας Διπλωματικής</a></li>
        </ul>
    </div>
<?php else: ?>
    <p>Δεν έχετε ανατεθειμένη διπλωματική εργασία.</p>
<?php endif; ?>

<!-- Κουμπί επιστροφής -->
<button onclick="window.location.href='dashboard.php'" 
        style="padding: 10px 15px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">
    Πίσω στο Dashboard
</button>

</body>
</html>