<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="main-header">
    <div class="logo">Escalafones IRG</div>
    <nav>
        <ul>
            <li><a href="/index.php">Inicio</a></li>
            <li><a href="/empleados/index.php">Empleados</a></li>
            <li><a href="/registros/import.php">Registros</a></li>
            <li><a href="/reportes/export_csv.php">Reportes</a></li>
            <li><a href="/calculo/index.php">Cálculo</a></li>
            <li><a href="/logout.php" class="logout">Salir</a></li>
        </ul>
    </nav>
</header>
