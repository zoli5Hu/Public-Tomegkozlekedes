<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

$successMessage = "";
$errorMessage = "";

$megallok = [];
$megalloQuery = "SELECT megallo_nev FROM megallo";
$result = $conn->query($megalloQuery);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $megallok[] = $row['megallo_nev'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['jarat_nev'])) {
    $jaratNev = trim($_POST['jarat_nev']);

    $megallokAdatok = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'megallo') === 0 && !empty($value)) {
            $index = explode('_', $key)[1];
            $megallokAdatok[] = [
                'megallo' => $_POST['megallo_' . $index],
                'idopont' => $_POST['idopont_' . $index],
            ];
        }
    }

    $duplicates = [];
    $insertOk = true;
    foreach ($megallokAdatok as $adat) {
        $pair = $adat['megallo'] . $adat['idopont'];
        if (in_array($pair, $duplicates)) {
            $insertOk = false;
            break;
        }
        $duplicates[] = $pair;
    }

    if ($insertOk) {
        $stmt = $conn->prepare("INSERT INTO jarat (jaratszam, idopont, megallo_nev) VALUES (?, ?, ?)");
        $conn->autocommit(false);
        foreach ($megallokAdatok as $adat) {
            $stmt->bind_param("sss", $jaratNev, $adat['idopont'], $adat['megallo']);
            if (!$stmt->execute()) {
                $errorMessage = "Hiba az adatok mentésekor: " . $stmt->error;
                $conn->rollback();
                $insertOk = false;
                break;
            }
        }
        if ($insertOk) {
            $conn->commit();
            $successMessage = "A járat sikeresen hozzáadva!";
        }
        $stmt->close();
    } else {
        $errorMessage = "Minden megállónak egyedi időpontja és neve kell, hogy legyen.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Járat hozzáadása</title>
    <link rel="stylesheet" href="admin.css">
    <script>
        // Dinamikus mezők hozzáadása új megállóknak
        let megalloIndex = 3; // Az új mezők számozá       sát kezdjük a 3. számnál (1 és 2 már alapértelmezett)

        function addMegallo() {
            const container = document.getElementById("megallo-container");

            const megalloDiv = document.createElement("div");
            megalloDiv.classList.add("megallo-group");

            megalloDiv.innerHTML = `
                <label>Új megálló:</label>
                <select name="megallo_${megalloIndex}" required>
                    <option value="">Válasszon megállót</option>
                    <?php foreach ($megallok as $megallo): ?>
                        <option value="<?php echo htmlspecialchars($megallo); ?>"><?php echo htmlspecialchars($megallo); ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Indulási időpont:</label>
                <input type="datetime-local" name="idopont_${megalloIndex}" required>
                <button type="button" class="btn delete-megallo" onclick="removeMegallo(this)">-</button>
            `;
            container.appendChild(megalloDiv);
            megalloIndex++;
        }

        function removeMegallo(button) {
            const megalloDiv = button.parentElement;
            megalloDiv.remove();
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Járat hozzáadása</h1>

    <?php if (!empty($successMessage)): ?>
        <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <form action="add_route.php" method="post">
        <label for="jarat-nev">Járat neve:</label>
        <input type="text" id="jarat-nev" name="jarat_nev" placeholder="Járat neve" required>

        <div id="megallo-container">
            <div class="megallo-group">
                <label>Első megálló:</label>
                <select name="megallo_1" required>
                    <option value="">Válasszon megállót</option>
                    <?php foreach ($megallok as $megallo): ?>
                        <option value="<?php echo htmlspecialchars($megallo); ?>"><?php echo htmlspecialchars($megallo); ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Indulási időpont:</label>
                <input type="datetime-local" name="idopont_1" required>
            </div>

            <div class="megallo-group">
                <label>Második megálló:</label>
                <select name="megallo_2" required>
                    <option value="">Válasszon megállót</option>
                    <?php foreach ($megallok as $megallo): ?>
                        <option value="<?php echo htmlspecialchars($megallo); ?>"><?php echo htmlspecialchars($megallo); ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Indulási időpont:</label>
                <input type="datetime-local" name="idopont_2" required>
            </div>
        </div>

        <button type="button" class="btn add-megallo" onclick="addMegallo()">+</button>
        <button type="submit">Járat hozzáadása</button>
    </form>

    <button class="btn back" onclick="window.location.href='admin.php'">Vissza</button>
</div>
</body>
</html>