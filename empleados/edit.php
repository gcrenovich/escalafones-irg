<?php
include '../config/db.php';

$legajo = $_GET['legajo'];
$result = $conn->query("SELECT * FROM empleados WHERE legajo='$legajo'");
$emp = $result->fetch_assoc();

if ($_POST) {
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $fecha = $_POST['fecha_ingreso'];
    $dias_actuales = $_POST['dias_actuales'];
    $dias_totales = $_POST['dias_totales'];

    $sql = "UPDATE empleados 
            SET nombre='$nombre', categoria='$categoria', fecha_ingreso='$fecha',
                dias_actuales='$dias_actuales', dias_totales='$dias_totales'
            WHERE legajo='$legajo'";
    $conn->query($sql);
    header("Location: index.php");
}
?>

<h2>Editar Empleado</h2>
<form method="post">
  Nombre: <input type="text" name="nombre" value="<?= $emp['nombre'] ?>"><br>
  Categoría: 
  <select name="categoria">
    <option value="eventual" <?= $emp['categoria']=="eventual"?"selected":"" ?>>Eventual</option>
    <option value="transitorio" <?= $emp['categoria']=="transitorio"?"selected":"" ?>>Transitorio</option>
  </select><br>
  Fecha Ingreso: <input type="date" name="fecha_ingreso" value="<?= $emp['fecha_ingreso'] ?>"><br>
  Días actuales: <input type="number" name="dias_actuales" value="<?= $emp['dias_actuales'] ?>"><br>
  Días totales: <input type="number" name="dias_totales" value="<?= $emp['dias_totales'] ?>"><br>
  <input type="submit" value="Actualizar">
</form>
