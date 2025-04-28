<?php
session_start();
$bejelentkezve = isset($_SESSION['user']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kezdőlap</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function navigateTo(url) {
            window.location.href = url;
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Kezdőlap</h1>

    <?php if ($bejelentkezve): ?>
        <p>Üdvözlet, <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong>!</p>
        <button class="btn standard" onclick="navigateTo('standard.php')">Menetrendek és késés bejelentése</button>
        <?php if ($isAdmin): ?>
            <button class="btn admin" onclick="navigateTo('admin.php')">Adminisztráció</button>
        <?php endif; ?>
        <button class="btn logout" onclick="navigateTo('logout.php')">Kijelentkezés</button>
    <?php else: ?>
        <button class="btn login" onclick="navigateTo('login.php')">Bejelentkezés</button>
        <button class="btn register" onclick="navigateTo('register.php')">Regisztráció</button>
        <button class="btn guest" onclick="navigateTo('standard.php')">Vendégként belépés</button>
    <?php endif; ?>
</div>
</body>
</html>