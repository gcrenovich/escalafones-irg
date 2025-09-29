<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/footer.php';

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
    <td><?= htmlspecialchars($row['legajo']) ?></td>
    <td><?= htmlspecialchars($row['nombre']) ?></td>
    <td><?= htmlspecialchars($row['categoria']) ?></td>
    <td><?= htmlspecialchars($row['fecha_ingreso']) ?></td>
    <td><?= round($row['dias_actuales'], 2) ?></td>
    <td><?= round($row['dias_totales'], 2) ?></td>
    <td>
      <a href="edit.php?legajo=<?= urlencode($row['legajo']) ?>">✏️ Editar</a> | 
      <a href="delete.php?legajo=<?= urlencode($row['legajo']) ?>" onclick="return confirm('¿Eliminar empleado?')">🗑️ Eliminar</a>
    </td>
  </tr>
  <?php } ?>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
