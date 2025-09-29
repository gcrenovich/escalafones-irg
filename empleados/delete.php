<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/footer.php';
$legajo = $_GET['legajo'];
$conn->query("DELETE FROM empleados WHERE legajo='$legajo'");
header("Location: index.php");
