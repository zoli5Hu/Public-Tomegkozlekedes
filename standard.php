<?php
session_start();
include 'db.php';

$bejelentkezett = isset($_SESSION['user']) && !isset($_SESSION['guest']);

$kivalasztottJarat = null;

if (isset($_GET['jaratszam'])) {
    $kivalasztottJarat = $_GET['jaratszam'];

    $stmt = $conn->prepare("
        WITH calculated_distances AS (
            SELECT 
                j.jaratszam,
                j.idopont,
                m.megallo_nev,
                m.x,
                m.y,
                LAG(m.x) OVER (PARTITION BY j.jaratszam ORDER BY j.idopont) AS pre_x,
                LAG(m.y) OVER (PARTITION BY j.jaratszam ORDER BY j.idopont) AS pre_y,
                -- Megállók közötti távolság kiszámítása
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
            WHERE 
                j.jaratszam = ?
        ),
        cumulative_sum AS (
            SELECT 
                *, 
                -- Összegzett távolság számítása
                SUM(megallokkozott) OVER (PARTITION BY jaratszam ORDER BY idopont) AS osszeg
            FROM 
                calculated_distances
        )
        SELECT 
            ROW_NUMBER() OVER (PARTITION BY jaratszam ORDER BY idopont) AS sorszam,
            jaratszam,
            idopont,
            megallo_nev,
            x AS x_koordinata,
            y AS y_koordinata,
            osszeg
        FROM 
            cumulative_sum
        ORDER BY 
            idopont;
    ");
    $stmt->bind_param("s", $kivalasztottJarat);
    $stmt->execute();
    $eredmenyek = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elérhető Járatok</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="standdard.css">
</head>
<body>
<div class="container">
    <h1>Járatok Listája</h1>
    <div class="page active">
        <div class="jaratok-container">
            <?php
            $sql = "SELECT DISTINCT `jaratszam` FROM `jarat` ORDER BY CAST(SUBSTRING(`jaratszam`, 2) AS UNSIGNED) ASC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $jaratszam = htmlspecialchars($row['jaratszam']);
                    echo '<a href="?jaratszam=' . $jaratszam . '" class="jarat-box">' . $jaratszam . '</a>';
                }
            } else {
                echo '<p>Nincs elérhető járat az adatbázisban.</p>';
            }
            ?>
        </div>
    </div>

    <?php if ($kivalasztottJarat): ?>
        <p id="uzenet">a kiválasztott járatszám: <?php echo htmlspecialchars($kivalasztottJarat); ?></p>
        <div id="eredmeny">
            <h2>A kiválasztott járat adatai:</h2>
            <table border="1">
                <thead>
                <tr>
                    <th>Sorszám</th>
                    <th>Járatszám</th>
                    <th>Időpont</th>
                    <th>Megállónév</th>
                    <th>eddig ennyi utat kell megtenni</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $all_path = 0;
                $szamer = 0;
                while ($sor = $eredmenyek->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sor['sorszam']); ?></td>
                        <td><?php echo htmlspecialchars($sor['jaratszam']); ?></td>
                        <td><?php echo htmlspecialchars($sor['idopont']); ?></td>
                        <td><?php
                            $szamer++;
                            if($szamer ==1) {

                                echo htmlspecialchars($sor['megallo_nev']. " kezdő  megálló");
                            }else{
                                if($eredmenyek->num_rows ==$sor['sorszam'] ){
                                echo htmlspecialchars($sor['megallo_nev']. " végállomás  megálló");
                                }else{
                                    echo htmlspecialchars($sor['megallo_nev']);

                                }

                            }

                            ?></td>
                        <td><?php echo number_format($sor['osszeg'], 2) ." km"; ?></td> <!-- Összeg -->

                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <button class="btn back" onclick="window.location.href='index.php'">Vissza a főoldalra</button>
    <?php if ($bejelentkezett): ?>
        <button class="btn report" onclick="window.location.href='report.php'">Bejelentés</button>
    <?php endif; ?>
</div>
</body>
</html>