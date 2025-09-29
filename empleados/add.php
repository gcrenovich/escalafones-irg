<?php
include '../config/db.php';

if ($_POST) {
    $legajo = $_POST['legajo'];
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $fecha = $_POST['fecha_ingreso'];

    $sql = "INSERT INTO empleados (legajo, nombre, categoria, fecha_ingreso) 
            VALUES ('$legajo','$nombre','$categoria','$fecha')";
    $conn->query($sql);
    header("Location: index.php");
}
?>

<h2>Agregar Empleado</h2>
<form method="post">
  Legajo: <input type="text" name="legajo" required><br>
  Nombre: <input type="text" name="nombre" required><br>
  Categoría: 
  <select name="categoria">
    <option value="eventual">Eventual</option>
    <option value="transitorio">Transitorio</option>
  </select><br>
  Fecha Ingreso: <input type="date" name="fecha_ingreso" required><br>
  <input type="submit" value="Guardar">
</form>
