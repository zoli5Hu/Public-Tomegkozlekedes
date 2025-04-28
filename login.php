<?php
session_start();
require 'db.php';

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT nev, jelszo, szerep FROM felhasznalo WHERE nev = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if ($password == $user['jelszo']) {
            $_SESSION['user'] = $user['nev'];
            $_SESSION['role'] = $user['szerep'];

            header("Location: index.php");
            exit;
        } else {
            $errorMessage = "Hibás jelszó!";
        }
    } else {
        $errorMessage = "Hibás felhasználónév vagy e-mail!";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Bejelentkezés</h1>
    <?php if (!empty($errorMessage)): ?>
        <p class="error" style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    <form action="login.php" method="post">
        <label for="login-username">Felhasználónév vagy E-mail</label>
        <input type="text" id="login-username" name="username" placeholder="Felhasználónév vagy e-mail" required>
        <label for="login-password">Jelszó</label>
        <input type="password" id="login-password" name="password" placeholder="Jelszó" required>
        <button type="submit" class="btn login">Bejelentkezés</button>
    </form>
    <button class="btn back" onclick="window.location.href='index.php'">Vissza</button>
</div>
</body>
</html>