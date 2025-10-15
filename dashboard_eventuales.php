<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

echo "<h2>Dashboard - Eventuales (últimos 3 años)</h2>";

// Años a mostrar dinámicamente
$year_actual = date('Y');
$year2 = $year_actual - 1;
$year3 = $year_actual - 2;

// Consulta que calcula días por año y total
$q = "
    SELECT 
        e.legajo, 
        e.nombre,
        SUM(CASE WHEN YEAR(r.fecha) = $year3 THEN r.dias_calculados ELSE 0 END) AS dias_$year3,
        SUM(CASE WHEN YEAR(r.fecha) = $year2 THEN r.dias_calculados ELSE 0 END) AS dias_$year2,
        SUM(CASE WHEN YEAR(r.fecha) = $year_actual THEN r.dias_calculados ELSE 0 END) AS dias_$year_actual,
        IFNULL(SUM(r.dias_calculados),0) AS total_3años,
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
    ORDER BY total_3años DESC
";
$res = $conn->query($q);
?>

<table class="table" border="1" cellspacing="0" cellpadding="5">
    <tr style="background:#ddd">
        <th>Legajo</th>
        <th>Nombre</th>
        <th><?=$year3?></th>
        <th><?=$year2?></th>
        <th><?=$year_actual?></th>
        <th>Total (3 años)</th>
        <th>Estado</th>
    </tr>
    <?php while($row = $res->fetch_assoc()): ?>
        <?php
            $color = 'green';
            if ($row['estado'] === 'RIESGO') $color = 'red';
            elseif ($row['estado'] === 'ALERTA') $color = 'orange';
        ?>
        <tr style="color:<?=$color?>">
            <td><?=htmlspecialchars($row['legajo'])?></td>
            <td><?=htmlspecialchars($row['nombre'])?></td>
            <td><?=round($row["dias_$year3"],2)?></td>
            <td><?=round($row["dias_$year2"],2)?></td>
            <td><?=round($row["dias_$year_actual"],2)?></td>
            <td><b><?=round($row['total_3años'],2)?></b></td>
            <td><b><?=htmlspecialchars($row['estado'])?></b></td>
        </tr>
    <?php endwhile; ?>
</table>

<a href="index.php" class="btn">⬅️ Volver al Dashboard Principal</a>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
