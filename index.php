
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// ---- FILTRO POR FECHAS ----
$desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
$rango = isset($_GET['rango']) ? (int)$_GET['rango'] : 0; // 30, 60, 90 días

if ($rango > 0) {
    $desde = date('Y-m-d', strtotime("-$rango days"));
    $hasta = date('Y-m-d');
}

$condicionFechas = "";
if ($desde && $hasta) {
    $condicionFechas = "AND e.legajo IN (
        SELECT DISTINCT legajo FROM registros
        WHERE fecha BETWEEN '$desde' AND '$hasta'
    )";
} elseif ($desde) {
    $condicionFechas = "AND e.legajo IN (
        SELECT DISTINCT legajo FROM registros
        WHERE fecha >= '$desde'
    )";
} elseif ($hasta) {
    $condicionFechas = "AND e.legajo IN (
        SELECT DISTINCT legajo FROM registros
        WHERE fecha <= '$hasta'
    )";
}

/**
 * Actualiza escalafón y días actuales en base a los días totales.
 */
$conn->query("
    UPDATE empleados
    SET 
        escalafon = FLOOR(dias_totales / 270),
        dias_actuales = dias_totales % 270
");

// ---- EMPLEADOS ORDENADOS ----
$q = "SELECT e.legajo, e.nombre, e.categoria, e.fecha_ingreso, 
             e.dias_actuales, e.dias_totales, e.escalafon
      FROM empleados e
      WHERE 1=1 $condicionFechas
      ORDER BY (270 - dias_actuales) ASC, dias_actuales DESC
      LIMIT 200";
$res = $conn->query($q);
?>

<h2>Dashboard</h2>

<!-- FILTRO POR RANGO DE FECHAS -->
<form method="GET" style="margin-bottom:20px;">
    <label>Desde:</label>
    <input type="date" name="desde" value="<?=htmlspecialchars($desde)?>">
    <label>Hasta:</label>
    <input type="date" name="hasta" value="<?=htmlspecialchars($hasta)?>">
    <button type="submit">Filtrar</button>
    <a href="index.php" class="btn btn-secondary">Limpiar</a>
</form>

<!-- FILTRO RÁPIDO -->
<div style="margin-bottom:20px;">
    <b>Rangos rápidos:</b>
    <a href="index.php?rango=30" class="btn btn-sm btn-info">Últimos 30 días</a>
    <a href="index.php?rango=60" class="btn btn-sm btn-info">Últimos 60 días</a>
    <a href="index.php?rango=90" class="btn btn-sm btn-info">Últimos 90 días</a>
</div>

<!-- ENLACE AL DASHBOARD ESPECÍFICO DE EVENTUALES -->
<div style="margin-top:24px;">
    <h3>Eventuales — informe específico (últimos 3 años)</h3>
    <p>
        Para ver el detalle por año y las alertas (NORMAL / ALERTA / RIESGO), accede al dashboard específico:
    </p>
    <p>
        <a href="dashboard_eventuales.php" class="btn btn-primary">📊 Ver Dashboard de Eventuales (últimos 3 años)</a>
    </p>
</div>

<h3>Próximos a 270 días</h3>
<table class="table" id="tablaEmpleados">
<tr>
  <th>Legajo</th>
  <th>Nombre</th>
  <th>Categoría</th>
  <th>Días Actuales</th>
  <th>Días Restantes</th>
  <th>Escalafón</th>
  <th>Alerta</th>
</tr>
<?php while($row = $res->fetch_assoc()): 
    $rest = 270 - (int)$row['dias_actuales'];
    $alert = '';
    if ($rest <= 15 && $rest > 0) $alert = '⚠️ Próximo (<=15d)';
    if ((int)$row['dias_actuales'] === 0 && (int)$row['escalafon'] > 0) $alert = '✅ Subió escalafón';
?>
<tr>
  <td><?=htmlspecialchars($row['legajo'])?></td>
  <td><?=htmlspecialchars($row['nombre'])?></td>
  <td><?=htmlspecialchars($row['categoria'])?></td>
  <td><?=intval($row['dias_actuales'])?></td>
  <td><?=$rest?></td>
  <td><?=intval($row['escalafon'])?></td>
  <td><?=$alert?></td>
</tr>
<?php endwhile; ?>
</table>

<script>
// === ORDENAMIENTO DE TABLAS POR COLUMNA ===
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("tablaEmpleados");
    const headers = table.querySelectorAll("th");
    let sortDirection = 1;

    headers.forEach((header, index) => {
        header.style.cursor = "pointer";
        header.addEventListener("click", () => {
            const rows = Array.from(table.querySelectorAll("tr:nth-child(n+2)"));
            const isNumeric = !isNaN(rows[0].children[index].innerText.trim());
            rows.sort((a, b) => {
                let A = a.children[index].innerText.trim();
                let B = b.children[index].innerText.trim();
                if (isNumeric) {
                    A = parseFloat(A) || 0;
                    B = parseFloat(B) || 0;
                }
                return (A > B ? 1 : A < B ? -1 : 0) * sortDirection;
            });
            sortDirection *= -1;
            rows.forEach(r => table.appendChild(r));
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
