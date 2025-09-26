<?php
// config/users.php
// Gestión de usuarios del sistema (para login y control de permisos)

include("db.php");

/**
 * Obtener usuario por nombre de usuario
 */
function getUserByUsername($username) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Crear un nuevo usuario
 */
function createUser($username, $password, $role = 'admin') {
    global $mysqli;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hash, $role);
    return $stmt->execute();
}

/**
 * Verificar login
 */
function checkLogin($username, $password) {
    $user = getUserByUsername($username);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}
