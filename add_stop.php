<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['stop_name'], $_POST['x_coord'], $_POST['y_coord'])) {
    $stopName = trim($_POST['stop_name']);
    $xCoord = intval($_POST['x_coord']);
    $yCoord = intval($_POST['y_coord']);

    if (!empty($stopName) && is_numeric($xCoord) && is_numeric($yCoord)) {
        $stmt = $conn->prepare("INSERT INTO megallo (megallo_nev, x, y) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $stopName, $xCoord, $yCoord);

        if ($stmt->execute()) {
            $successMessage = "A megálló sikeresen hozzáadva!";
        } else {
            $errorMessage = "Hiba történt a megálló hozzáadása során: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $errorMessage = "Minden mezőt helyesen kell kitölteni!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Megálló hozzáadása</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="container">
    <h1>Megálló hozzáadása</h1>
    <?php if (!empty($successMessage)): ?>
        <p class="success" style="color: green;"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <p class="error" style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <form action="add_stop.php" method="post">
        <label for="stop-name">Megálló neve:</label>
        <input type="text" id="stop-name" name="stop_name" placeholder="Megálló neve" required>

        <label for="x-coord">X koordináta:</label>
        <input type="number" id="x-coord" name="x_coord" placeholder="X koordináta" required>

        <label for="y-coord">Y koordináta:</label>
        <input type="number" id="y-coord" name="y_coord" placeholder="Y koordináta" required>

        <button type="submit">Hozzáadás</button>
    </form>

    <button class="btn back" onclick="window.location.href='admin.php'">Vissza</button>
</div>
</body>
</html>