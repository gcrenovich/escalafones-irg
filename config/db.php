<?php
$host = "localhost";
$user = "root";   // ajustar si tu usuario es distinto
$pass = "";       // ajustar si tu pass es distinto
$db   = "escalafones_irg";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");
?>
