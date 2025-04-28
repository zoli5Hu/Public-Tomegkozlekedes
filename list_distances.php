<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

$distances = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = "
        WITH calculated_distances AS (
            SELECT 
                j.jaratszam,
                j.idopont,
                m.megallo_nev,
                m.x,
                m.y,
                LAG(m.x) OVER (PARTITION BY j.jaratszam ORDER BY j.idopont) AS pre_x,
                LAG(m.y) OVER (PARTITION BY j.jaratszam ORDER BY j.idopont) AS pre_y,
                CASE 
                    WHEN LAG(m.x) OVER (PARTITION BY j.jaratszam ORDER BY j.idopont) IS NULL THEN 0
                    ELSE SQRT(
                        POW(m.x - LAG(m.x) OVER (PARTITION BY j.jaratszam ORDER BY j.idopont), 2) +
                        POW(m.y - LAG(m.y) OVER (PARTITION BY j.jaratszam ORDER BY j.idopont), 2)
                    )
                END AS megallokkozott
            FROM 
                jarat j
            JOIN 
                megallo m ON j.megallo_nev = m.megallo_nev
        ),
        cumulative_sum AS (
            SELECT 
                jaratszam, 
                SUM(megallokkozott) OVER (PARTITION BY jaratszam ORDER BY idopont) AS osszeg
            FROM 
                calculated_distances
        ),
        result AS (
            SELECT 
                jaratszam, 
                MAX(osszeg) AS teljes_tav
            FROM 
                cumulative_sum
            GROUP BY 
                jaratszam
        )
        SELECT * 
        FROM 
            result
        ORDER BY jaratszam;
    ";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $distances[] = $row;
        }
    } else {
        $error = "Nem található adat a táblázatban.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Járatok távolsága</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="container">
    <h1>Járatok teljes távolsága</h1>

    <form method="POST" action="list_distances.php">
        <button type="submit" class="btn list">Távolságok listázása</button>
    </form>

    <?php if (!empty($distances)): ?>
        <table>
            <thead>
            <tr>
                <th>Járatszám</th>
                <th>Teljes távolság (km)</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $all = 0;
            foreach ($distances as $distance): ?>
                <tr>
                    <td><?php echo htmlspecialchars($distance['jaratszam']); ?></td>
                    <td><?php
                        $all += number_format($distance['teljes_tav'], 2);
                        echo number_format($distance['teljes_tav'], 2); ?> km</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total-distance">
            <h2>Járatok összesített távolsága:</h2>
            <p><?php echo number_format($all, 2); ?> km</p>
        </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p>Nincs elérhető adat a táblázatban.</p>
    <?php endif; ?>

    <button class="btn back" onclick="window.location.href='admin.php'">Vissza az adminfelülethez</button>
</div>
</body>
</html>