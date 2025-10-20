<?php


$host = "localhost";
$user = "root";
$pass = "";
$dbname = "escalafones_irg";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
