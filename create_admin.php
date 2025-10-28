<?php
require 'config/db.php';

$username = "admin"; // el nombre de usuario que quieras
$password = "123456"; // la contraseña que quieras
$rol = "admin";

// Encriptar contraseña
$hashed = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (username, password, rol) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $hashed, $rol);

if ($stmt->execute()) {
    echo "Usuario creado correctamente.";
} else {
    echo "Error: " . $stmt->error;
}
