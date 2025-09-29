<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['username'])) { header('Location: /escalafones-irg/login.php'); exit; }

$q = "SELECT legajo, nombre, categoria, fecha_ingreso, dias_actuales, dias_totales, escalafon FROM empleados ORDER BY legajo ASC";
$res = $mysqli->query($q);
?>

<h2>Empleados</h2>
<p><a class="btn" href="form.php">Nuevo</a> <a class="btn" href="import.php">Importar</a></p>

<table class="table">
<tr><th>Legajo</th><th>Nombre</th><th>Categoría</th><th>F. ingreso</th><th>Días actuales</th><th>Días totales</th><th>Escalafón</th><th>Acciones</th></tr>
<?php while($row = $res->fetch_assoc()): ?>
<tr>
  <td><?=htmlspecialchars($row['legajo'])?></td>
  <td><?=htmlspecialchars($row['nombre'])?></td>
  <td><?=htmlspecialchars($row['categoria'])?></td>
  <td><?=htmlspecialchars($row['fecha_ingreso'])?></td>
  <td><?=round($row['dias_actuales'],2)?></td>
  <td><?=round($row['dias_totales'],2)?></td>
  <td><?=intval($row['escalafon'])?></td>
  <td><a href="form.php?legajo=<?=urlencode($row['legajo'])?>">Editar</a></td>
</tr>
<?php endwhile; ?>
</table>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
