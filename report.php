<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    die("Csak bejelentkezett felhasználók számára érhető el a bejelentés funkció!");
}

$felhasznaloNev = $_SESSION['user'];
$jaratok = [];
$idopontok = [];
$kivalasztottJarat = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kivalasztottJarat = $_POST['jaratszam'] ?? null;
    $kivalasztottIdopont = $_POST['idopont'] ?? null;

    if ($kivalasztottJarat && $kivalasztottIdopont) {
        $stmt = $conn->prepare("INSERT INTO bejelentes (jaratszam, idopont, felhasznalo_nev) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $kivalasztottJarat, $kivalasztottIdopont, $felhasznaloNev);
        if ($stmt->execute()) {
            $uzenet = "A bejelentés sikeres volt!";
        } else {
            $uzenet = "Már rögzítettél ennek a járatnak az időpontjára késést tudomásul vettük ";
        }
    } else {
        $uzenet = "Kérjük, válasszon egy járatot és egy időpontot!";
    }
} elseif (isset($_GET['jaratszam'])) {
    $kivalasztottJarat = $_GET['jaratszam'];
    $stmt = $conn->prepare("SELECT idopont FROM jarat WHERE jaratszam = ? ORDER BY idopont");
    $stmt->bind_param("s", $kivalasztottJarat);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $idopontok[] = $row['idopont'];
    }
}

$result = $conn->query("SELECT DISTINCT jaratszam FROM jarat ORDER BY jaratszam");
while ($row = $result->fetch_assoc()) {
    $jaratok[] = $row['jaratszam'];
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentés</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function handleJaratChange(selectElement) {
            const selectedValue = selectElement.value;
            if (selectedValue) {
                window.location.href = `report.php?jaratszam=${selectedValue}`;
            }
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 1.8em;
        }

        p {
            font-size: 1.1em;
            line-height: 1.5;
            color: #555;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 1.1em;
            color: #333;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            color: #333;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        select:focus {
            border-color: #007BFF;
            outline: none;
            background-color: #ffffff;
        }

        button {
            display: block;
            width: 100%;
            padding: 12px;
            font-size: 1em;
            color: #fff;
            background: #007BFF;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        .btn.back {
            background: #6c757d;
            margin-top: 10px;
        }

        .btn.back:hover {
            background: #5a6268;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f5f5f5;
            color: green;
            text-align: center;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Bejelentés</h1>

    <?php if (!empty($uzenet)): ?>
        <p class="message"><?php echo htmlspecialchars($uzenet); ?></p>
    <?php endif; ?>

    <form action="report.php" method="post">
        <label for="jaratszam">Válasszon járatot:</label>
        <select name="jaratszam" id="jaratszam" onchange="handleJaratChange(this)">
            <option value="">-- Válasszon egy járatot --</option>
            <?php foreach ($jaratok as $jarat): ?>
                <option value="<?php echo htmlspecialchars($jarat); ?>" <?php echo ($jarat === $kivalasztottJarat) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($jarat); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (!empty($idopontok)): ?>
            <label for="idopont">Válasszon időpontot:</label>
            <select name="idopont" id="idopont">
                <option value="">-- Válasszon egy időpontot --</option>
                <?php foreach ($idopontok as $idopont): ?>
                    <option value="<?php echo htmlspecialchars($idopont); ?>">
                        <?php echo htmlspecialchars($idopont); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <p>Kérjük, előbb válasszon egy járatot!</p>
        <?php endif; ?>

        <button type="submit">Beküldés</button>
    </form>

    <button class="btn back" onclick="window.location.href='index.php'">Vissza a főoldalra</button>
</div>
</body>
</html>