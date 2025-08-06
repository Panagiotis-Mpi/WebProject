<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user['password'] === $password) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        switch ($user['role']) {
            case 'student':
                header("Location: student/dashboard.php");
                break;
            case 'professor':
                header("Location: professor/dashboard.php");
                break;
            case 'secretary':
                header("Location: secretary/dashboard.php");
                break;
            default:
                $error = "Άγνωστος ρόλος.";
        }
        exit;
    } else {
        $error = "Λάθος email ή κωδικός.";
    }
}
?>

<h2>Σύνδεση</h2>
<?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
<form method="post">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Κωδικός" required><br>
    <button type="submit">Είσοδος</button>
</form>
