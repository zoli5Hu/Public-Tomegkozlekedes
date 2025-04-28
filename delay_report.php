<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$delayMessage = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'db.php';

    $jaratszam = $_POST['jaratszam'];
    $kesesIdo = $_POST['keses_ideje'];

    $stmt = $conn->prepare("INSERT INTO kesesek (jaratszam, keses_ido) VALUES (?, ?)");
    $stmt->bind_param("si", $jaratszam, $kesesIdo);

    if ($stmt->execute()) {
        $delayMessage = "Késési adatok sikeresen rögzítve!";
    } else {
        $delayMessage = "Hiba történt a késés rögzítése során.";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Késések bejelentése</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Késések bejelentése</h1>
    <?php if (!empty($delayMessage)): ?>
        <p class="<?php echo strpos($delayMessage, 'sikeresen') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($delayMessage); ?>
        </p>
    <?php endif; ?>
    <form action="delay_report.php" method="post">
        <label for="jaratszam">Járatszám:</label>
        <input type="text" id="jaratszam" name="jaratszam" placeholder="Pl. B3" required>

        <label for="keses_ideje">Késés ideje (perc):</label>
        <input type="number" id="keses_ideje" name="keses_ideje" placeholder="Pl. 10" required>

        <button type="submit" class="btn special">Bejelentés</button>
    </form>
    <button class="btn back" onclick="window.location.href='guest.php'">Vissza a járatokhoz</button>
</div>
</body>
</html><?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$delayMessage = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require 'db.php';

    $jaratszam = $_POST['jaratszam'];
    $kesesIdo = $_POST['keses_ideje'];

    $stmt = $conn->prepare("INSERT INTO kesesek (jaratszam, keses_ido) VALUES (?, ?)");
    $stmt->bind_param("si", $jaratszam, $kesesIdo);

    if ($stmt->execute()) {
        $delayMessage = "Késési adatok sikeresen rögzítve!";
    } else {
        $delayMessage = "Hiba történt a késés rögzítése során.";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Késések bejelentése</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Késések bejelentése</h1>
    <?php if (!empty($delayMessage)): ?>
        <p class="<?php echo strpos($delayMessage, 'sikeresen') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($delayMessage); ?>
        </p>
    <?php endif; ?>
    <form action="delay_report.php" method="post">
        <label for="jaratszam">Járatszám:</label>
        <input type="text" id="jaratszam" name="jaratszam" placeholder="Pl. B3" required>

        <label for="keses_ideje">Késés ideje (perc):</label>
        <input type="number" id="keses_ideje" name="keses_ideje" placeholder="Pl. 10" required>

        <button type="submit" class="btn special">Bejelentés</button>
    </form>
    <button class="btn back" onclick="window.location.href='guest.php'">Vissza a járatokhoz</button>
</div>
</body>
</html>