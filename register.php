<?php
require 'db.php';

$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['email'], $_POST['password'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = "user";

    $stmt = $conn->prepare("SELECT * FROM felhasznalo WHERE nev = ? OR email = ?");
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO felhasznalo (nev, email, jelszo, szerep) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        if ($stmt->execute()) {
            $successMessage = "Sikeres regisztráció, " . htmlspecialchars($name) . "!";
        } else {
            $errorMessage = "Hiba történt a regisztráció során.";
        }
    } else {
        $errorMessage = "A megadott név vagy email már foglalt!";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Regisztráció</h1>
    <?php if (!empty($successMessage)): ?>
        <p class="success" style="color: green;"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php elseif (!empty($errorMessage)): ?>
        <p class="error" style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    <form action="register.php" method="post">
        <label for="register-name">Felhasználónév</label>
        <input type="text" id="register-name" name="name" placeholder="Felhasználónév" required>
        <label for="register-email">E-mail</label>
        <input type="email" id="register-email" name="email" placeholder="E-mail" required>
        <label for="register-password">Jelszó</label>
        <input type="password" id="register-password" name="password" placeholder="Jelszó" required>
        <button type="submit" class="btn register">Regisztráció</button>
    </form>
    <button class="btn back" onclick="window.location.href='index.php'">Vissza</button>
</div>
</body>
</html>