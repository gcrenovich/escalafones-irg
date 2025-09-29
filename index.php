<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Empleados ordenados por proximidad a 270
$q = "SELECT legajo, nombre, categoria, fecha_ingreso, dias_actuales, dias_totales, escalafon
      FROM empleados
      ORDER BY (270 - dias_actuales) ASC, dias_actuales DESC
      LIMIT 200";
$res = $conn->query($q);

// Eventuales: ventana 3 años
$q2 = "SELECT e.legajo, e.nombre, IFNULL(SUM(r.dias_calculados),0) AS dias_3años,
       CASE 
            WHEN IFNULL(SUM(r.dias_calculados),0) >= 300 THEN 'RIESGO'
            WHEN IFNULL(SUM(r.dias_calculados),0) >= 285 THEN 'ALERTA'
            ELSE 'NORMAL' 
       END AS estado
       FROM empleados e
       LEFT JOIN registros r 
            ON e.legajo = r.legajo 
            AND r.fecha >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR)
       WHERE e.categoria = 'eventual'
       GROUP BY e.legajo, e.nombre
       HAVING dias_3años > 0
       ORDER BY dias_3años DESC
       LIMIT 100";
$res2 = $conn->query($q2);
?>

<h2>Dashboard</h2>

<h3>Próximos a 270 días</h3>
<table class="table">
<tr>
  <th>Legajo</th><th>Nombre</th><th>Categoría</th>
  <th>Días Actuales</th><th>Días Restantes</th>
  <th>Escalafón</th><th>Alerta</th>
</tr>
<?php while($row = $res->fetch_assoc()): 
    $rest = 270 - (float)$row['dias_actuales'];
    $alert = '';
    if ($rest <= 15 && $rest > 0) $alert = 'Próximo (<=15d)';
    if ($row['dias_actuales'] >= 270) $alert = 'Sube escalafón';
?>
<tr>
  <td><?=htmlspecialchars($row['legajo'])?></td>
  <td><?=htmlspecialchars($row['nombre'])?></td>
  <td><?=htmlspecialchars($row['categoria'])?></td>
  <td><?=round($row['dias_actuales'],2)?></td>
  <td><?=round($rest,2)?></td>
  <td><?=intval($row['escalafon'])?></td>
  <td><?=$alert?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>Eventuales - últimos 3 años (alertas)</h3>
<table class="table">
<tr><th>Legajo</th><th>Nombre</th><th>Días últimos 3 años</th><th>Estado</th></tr>
<?php while($e = $res2->fetch_assoc()): ?>
<tr>
  <td><?=htmlspecialchars($e['legajo'])?></td>
  <td><?=htmlspecialchars($e['nombre'])?></td>
  <td><?=round($e['dias_3años'],2)?></td>
  <td><?=htmlspecialchars($e['estado'])?></td>
</tr>
<?php endwhile; ?>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
