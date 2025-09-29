<?php
include '../includes/db.php';
$legajo = $_GET['legajo'];
$conn->query("DELETE FROM empleados WHERE legajo='$legajo'");
header("Location: index.php");
