<?php
// config/db.php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "escalafones_irg";

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    error_log("DB connect error: " . $mysqli->connect_error);
    die("Error de conexión a la base de datos.");
}
$mysqli->set_charset("utf8mb4");
