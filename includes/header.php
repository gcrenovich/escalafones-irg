<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
function is_logged_in(){ return isset($_SESSION['username']); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Escalafones IRG</title>
  <link rel="stylesheet" href="/escalafones-irg/public/css/styles.css">
</head>
<body>
<header style="padding:10px;background:#f0f0f0;margin-bottom:15px;">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <div><strong>Escalafones IRG</strong></div>
    <div>
      <?php if(is_logged_in()): ?>
        Usuario: <?=htmlspecialchars($_SESSION['username'])?> |
        <a href="/escalafones-irg/login.php?logout=1">Salir</a>
      <?php else: ?>
        <a href="/escalafones-irg/login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main style="padding:0 20px;">
<nav style="margin-bottom:12px;">
  <a href="/escalafones-irg/index.php">Dashboard</a> |
  <a href="/escalafones-irg/empleados/index.php">Empleados</a> |
  <a href="/escalafones-irg/empleados/import.php">Importar Empleados</a> |
  <a href="/escalafones-irg/registros/import.php">Importar Registros</a> |
  <a href="/escalafones-irg/reportes/export_csv.php">Exportar CSV</a>
</nav>
