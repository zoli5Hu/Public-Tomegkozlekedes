<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

$delays = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = "
        SELECT jarat.jaratszam, jarat.idopont, COUNT(bejelentes.felhasznalo_nev) AS bejelentesek_szama
        FROM jarat
        LEFT JOIN bejelentes 
        ON jarat.jaratszam = bejelentes.jaratszam AND jarat.idopont = bejelentes.idopont
        GROUP BY jarat.jaratszam, jarat.idopont
        ORDER BY jarat.idopont ASC;
    ";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $delays[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Késések listázása</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="container">
    <h1>Késések listázása</h1>

    <form method="POST" action="list_delays.php">
        <button type="submit" class="btn list">Késések listázása</button>
    </form>

    <?php if (!empty($delays)): ?>
        <table border="1" style="width: 100%; margin-top: 20px;">
            <thead>
            <tr>
                <th>Járatszám</th>
                <th>Időpont</th>
                <th>Bejelentések száma</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($delays as $delay): ?>
                <tr>
                    <td><?php echo htmlspecialchars($delay['jaratszam']); ?></td>
                    <td><?php echo htmlspecialchars($delay['idopont']); ?></td>
                    <td><?php echo htmlspecialchars($delay['bejelentesek_szama']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p>Nincs elérhető adat a késésekhez.</p>
        <?php endif; ?>
    <?php endif; ?>

    <button class="btn back" onclick="window.location.href='admin.php'">Vissza az adminfelülethez</button>
</div>
</body>
</html>