<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Adatbázis kapcsolódás
$servername = "localhost";
$username = "root";
$password = "";
$database = "tomegkozlekedes2";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Adatbázis kapcsolódási hiba: " . $conn->connect_error);
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['route_name'])) {
    $routeName = trim($_POST['route_name']);

    if (!empty($routeName)) {
        $stmt = $conn->prepare("DELETE FROM jarat WHERE jaratszam = ?");
        $stmt->bind_param("s", $routeName);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $successMessage = "A járat sikeresen törölve!";
            } else {
                $errorMessage = "Nincs ilyen nevű járat az adatbázisban.";
            }
        } else {
            $errorMessage = "Hiba történt a járat törlése során: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $errorMessage = "A járat nevét kötelező megadni!";
    }
}

$routes = [];
$result = $conn->query("SELECT DISTINCT jaratszam FROM jarat");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $routes[] = $row['jaratszam'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Járat törlése</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="container">
    <h1>Járat törlése</h1>

    <?php if (!empty($successMessage)): ?>
        <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <form action="delete_route.php" method="post">
        <label for="route-name">Válaszd ki a törlendő járatot:</label>
        <select id="route-name" name="route_name" required>
            <option value="" disabled selected>Válassz egy járatot</option>
            <?php foreach ($routes as $route): ?>
                <option value="<?php echo htmlspecialchars($route); ?>">
                    <?php echo htmlspecialchars($route); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="delete">Törlés</button>
    </form>

    <button class="btn back" onclick="window.location.href='admin.php'">Vissza</button>
</div>
</body>
</html>