<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Si no está logueado, redirigir
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escalafones IRG - Panel Principal</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="container">
        <h1>Bienvenido, <?= htmlspecialchars($username) ?> 👋</h1>
        <p>Rol: <strong><?= htmlspecialchars($role) ?></strong></p>

        <div class="menu-grid">
            <a href="empleados/index.php" class="menu-card">
                <h3>👨‍💼 Empleados</h3>
                <p>Gestión de empleados y categorías.</p>
            </a>

            <a href="registros/import.php" class="menu-card">
                <h3>📥 Registros</h3>
                <p>Importar y procesar horas y días.</p>
            </a>

            <a href="imports/import.php" class="menu-card">
                <h3>📂 Importaciones</h3>
                <p>Subir documentos XLS.</p>
            </a>

            <a href="reportes/export_csv.php" class="menu-card">
                <h3>📊 Reportes</h3>
                <p>Generar informes y exportar datos.</p>
            </a>

            <a href="calculo/index.php" class="menu-card">
                <h3>🧮 Cálculo</h3>
                <p>Escalafones y control de alertas.</p>
            </a>
        </div>
    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
