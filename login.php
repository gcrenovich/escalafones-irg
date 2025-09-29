<?php
// login.php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/users.php';

// logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// redirect if logged
if (isset($_SESSION['username'])) {
    header('Location: index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($username === '' || $password === '') $error = 'Ingrese usuario y contraseña.';
    else {

        // --- Usuario de prueba para testeo ---
        if ($username === 'admi' && $password === 'admin') {
            $_SESSION['user_id'] = 0; // id ficticio
            $_SESSION['username'] = 'admi';
            $_SESSION['role'] = 'admin';
            header('Location: index.php');
            exit;
        }

        // --- Validación normal contra la base de datos ---
        $user = getUserByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Login - Escalafones IRG</title>
<link rel="stylesheet" href="/escalafones-irg/public/css/styles.css">
</head>
<body>
<div class="login-container">
  <h2>Ingresar</h2>
  <?php if($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post">
    <label>Usuario<br><input name="username" required></label><br>
    <label>Contraseña<br><input name="password" type="password" required></label><br><br>
    <button type="submit">Entrar</button>
  </form>
</div>
</body>
</html>

