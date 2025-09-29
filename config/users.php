<?php
// config/users.php
require_once __DIR__ . '/db.php';

function getUserByUsername($username) {
    global $mysqli;
    $sql = "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1";
    $stm = $mysqli->prepare($sql);
    $stm->bind_param('s', $username);
    $stm->execute();
    $res = $stm->get_result();
    return $res->fetch_assoc();
}

function createUser($username, $password, $role = 'admin') {
    global $mysqli;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, role) VALUES (?,?,?)";
    $stm = $mysqli->prepare($sql);
    return $stm->bind_param('sss', $username, $hash, $role) ? $stm->execute() : false;
}