<?php
// registros/index.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['username'])) { header('Location: /escalafones-irg/login.php'); exit; }

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$q = "SELECT r.id, r.legajo, e.nombre, r.fecha, r.horas, r.dias_calculados, r.concepto
      FROM registros r
      LEFT JOIN empleados e ON e.legajo = r.legajo
      ORDER BY r.fecha DESC, r.id DESC
      LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($q);
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$res = $stmt->get_result();

// contar total para paginación
$total = $mysqli->query("SELECT COUNT(*) as c FROM registros")->fetch_assoc()['c'];
$totalPages = ceil($total / $perPage);
?>

<h2>Registros cargados</h2>
<p><a href="import.php">Importar nuevos</a></p>

<table class="table">
<tr>
  <th>ID</th><th>Legajo</th><th>Nombre</th><th>Fecha</th>
  <th>Horas</th><th>Días calculados</th><th>Concepto</th>
</tr>
<?php while($r = $res->fetch_assoc()): ?>
<tr>
  <td><?=$r['id']?></td>
  <td><?=htmlspecialchars($r['legajo'])?></td>
  <td><?=htmlspecialchars($r['nombre'])?></td>
  <td><?=htmlspecialchars($r['fecha'])?></td>
  <td><?=$r['horas']?></td>
  <td><?=round($r['dias_calculados'],2)?></td>
  <td><?=htmlspecialchars($r['concepto'])?></td>
</tr>
<?php endwhile; ?>
</table>

<div>
  <?php if($page > 1): ?>
    <a href="?page=<?=$page-1?>">Anterior</a>
  <?php endif; ?>
  Página <?=$page?> de <?=$totalPages?>
  <?php if($page < $totalPages): ?>
    <a href="?page=<?=$page+1?>">Siguiente</a>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
