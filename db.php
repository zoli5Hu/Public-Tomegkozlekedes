<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "tomegkozlekedes2";


$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Adatbázis kapcsolódási hiba: " . $conn->connect_error);
}
?>