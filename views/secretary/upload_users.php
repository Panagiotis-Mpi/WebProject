<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header("Location: ../login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
    $file = $_FILES['json_file']['tmp_name'];
    $data = json_decode(file_get_contents($file), true);

    if (!$data) {
        $message = "Μη έγκυρο JSON.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO Users (email,password,first_name,last_name,role,am) VALUES (?,?,?,?,?,?)");

        foreach ($data as $user) {
            try {
                $stmt->execute([
                    $user['email'],
                    $user['password'],
                    $user['first_name'],
                    $user['last_name'],
                    $user['role'],
                    $user['role'] === 'student' ? $user['am'] : null
                ]);
            } catch (PDOException $e) {
                $message .= "Σφάλμα για χρήστη ".$user['email'].": ".$e->getMessage()."<br>";
            }
        }
        if (!$message) $message = "Οι χρήστες καταχωρήθηκαν επιτυχώς.";
    }
}
?>
<h1>Μαζικό Ανέβασμα Χρηστών</h1>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="json_file" accept=".json" required>
    <button type="submit">Ανέβασμα</button>
</form>
<p><?= $message ?></p>
<p><a href="dashboard.php">← Επιστροφή</a></p>
