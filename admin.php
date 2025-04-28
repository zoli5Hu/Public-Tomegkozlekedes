<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$user = isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Ismeretlen felhasználó';
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adminisztráció</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function navigateTo(url) {
            window.location.href = url;
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Adminisztráció</h1>
    <p>Üdvözlünk, <strong><?php echo $user; ?></strong>! Itt végezhetsz adminisztrációs műveleteket.</p>

    <button class="btn add-stop" onclick="navigateTo('add_stop.php')">Megálló hozzáadása</button>
    <button class="btn delete-stop" onclick="navigateTo('delete_stop.php')">Megálló törlése</button>
    <button class="btn add-route" onclick="navigateTo('add_route.php')">Járat hozzáadása</button>
    <button class="btn delete-route" onclick="navigateTo('delete_route.php')">Járat törlése</button>
    <button class="btn list-delays" onclick="navigateTo('list_delays.php')">Késések listázása</button>

    <button class="btn list-distances" onclick="navigateTo('list_distances.php')">Járatok teljes útvonala</button>

    <button class="btn back" onclick="navigateTo('index.php')">Vissza a főoldalra</button>
</div>
</body>
</html>