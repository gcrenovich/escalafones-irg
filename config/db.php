<?php


$host = "sql307.infinityfree.com";
$user = "if0_41248892";
$pass = "j1KvhEdorT17";
$dbname = "if0_41248892_escalafones_irg";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
