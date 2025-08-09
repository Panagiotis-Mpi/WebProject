<?php
// manage_theses.php - Λίστα διπλωματικών για διαχείριση

// Σύνδεση χωρίς config.php
$conn = new PDO('mysql:host=localhost;dbname=diplomatiki;charset=utf8mb4', 'root', '');
$professor_id = 1; // Αλλάξτε με το πραγματικό ID

// Λήψη διπλωματικών
$stmt = $conn->prepare("
    SELECT t.title, th.id, th.status, 
           u.first_name, u.last_name, u.am,
           th.assignment_date
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
    <title>Διαχείριση Διπλωματικών</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .thesis { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
        }
        .pending { background: #FFF3CD; color: #856404; }
        .active { background: #D4EDDA; color: #155724; }
        .under_review { background: #CCE5FF; color: #004085; }
        .completed { background: #E2E3E5; color: #383D41; }
        .action-btn {
            padding: 5px 10px;
            margin-right: 5px;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Διαχείριση Διπλωματικών 📚</h1>

    <?php foreach ($theses as $thesis): ?>
        <div class="thesis">
            <h3><?= htmlspecialchars($thesis['title']) ?></h3>
            <p>Φοιτητής: <?= htmlspecialchars($thesis['first_name'].' '.$thesis['last_name']) ?> (ΑΜ: <?= $thesis['am'] ?>)</p>
            <p>Κατάσταση: <span class="status-badge <?= $thesis['status'] ?>"><?= $thesis['status'] ?></span></p>
            <p>Ανάθεση: <?= date('d/m/Y', strtotime($thesis['assignment_date'])) ?></p>
            
            <div style="margin-top: 10px;">
                <?php if ($thesis['status'] == 'active'): ?>
                    <button class="action-btn" style="background: #17A2B8; color: white;"
                            onclick="changeStatus(<?= $thesis['id'] ?>, 'under_review')">
                        Προώθηση για Αξιολόγηση
                    </button>
                <?php endif; ?>
                
                <button class="action-btn" style="background: #6C757D; color: white;"
                        onclick="viewDetails(<?= $thesis['id'] ?>)">
                    Λεπτομέρειες
                </button>
            </div>
        </div>
    <?php endforeach; ?>

    <script>
    function changeStatus(thesisId, newStatus) {
        if (confirm('Είστε βέβαιος για την αλλαγή κατάστασης;')) {
            window.location.href = `change_status.php?id=${thesisId}&status=${newStatus}`;
        }
    }
    
    function viewDetails(thesisId) {
        window.location.href = `thesis_details.php?id=${thesisId}`;
    }
    </script>
</body>
</html>