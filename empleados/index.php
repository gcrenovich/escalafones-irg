<?php
include '../includes/db.php';
include '../includes/head.php';
include '../includes/menu.php';

$result = $conn->query("SELECT * FROM empleados ORDER BY legajo ASC");
?>

<h2>Gestión de Empleados</h2>
<a href="add.php">➕ Agregar Empleado</a> | 
<a href="import.php">📂 Importar CSV</a>
<table border="1" cellpadding="5">
  <tr>
    <th>Legajo</th>
    <th>Nombre</th>
    <th>Categoría</th>
    <th>Fecha Ingreso</th>
    <th>Días actuales</th>
    <th>Días totales</th>
    <th>Acciones</th>
  </tr>
  <?php while($row = $result->fetch_assoc()) { ?>
  <tr>
    <td><?= $row['legajo'] ?></td>
    <td><?= $row['nombre'] ?></td>
    <td><?= $row['categoria'] ?></td>
    <td><?= $row['fecha_ingreso'] ?></td>
    <td><?= $row['dias_actuales'] ?></td>
    <td><?= $row['dias_totales'] ?></td>
    <td>
      <a href="edit.php?legajo=<?= $row['legajo'] ?>">✏️ Editar</a> | 
      <a href="delete.php?legajo=<?= $row['legajo'] ?>" onclick="return confirm('¿Eliminar empleado?')">🗑️ Eliminar</a>
    </td>
  </tr>
  <?php } ?>
</table>
