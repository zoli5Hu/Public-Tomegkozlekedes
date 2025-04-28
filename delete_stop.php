<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['stop_name'])) {
    $stopName = trim($_POST['stop_name']);

    if (!empty($stopName)) {
        $stmt = $conn->prepare("DELETE FROM megallo WHERE megallo_nev = ?");
        $stmt->bind_param("s", $stopName);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $successMessage = "A megálló sikeresen törölve!";
            } else {
                $errorMessage = "Nincs ilyen nevű megálló az adatbázisban.";
            }
        } else {
            $errorMessage = "Hiba történt a megálló törlése során: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $errorMessage = "A megálló nevét kötelező megadni!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Megálló törlése</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="container">
    <h1>Megálló törlése</h1>

    <?php if (!empty($successMessage)): ?>
        <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <form action="delete_stop.php" method="post">
        <label for="stop-name">Megálló neve:</label>
        <input type="text" id="stop-name" name="stop_name" placeholder="Megálló neve" required>
        <button type="submit">Törlés</button>
    </form>

    <button class="btn back" onclick="window.location.href='admin.php'">Vissza</button>
</div>
</body>
</html>