<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

echo "<h2>Dashboard - Eventuales (últimos 3 años)</h2>";

// Años dinámicos
$year_actual = date('Y');
$year2 = $year_actual - 1;
$year3 = $year_actual - 2;

// Consulta de días por año y total
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

<!-- BOTONES SUPERIORES -->
<div style="margin-bottom: 20px;">
    <a href="index.php" class="btn">⬅️ Volver al Dashboard Principal</a>
    <button id="btnExportar" class="btn btn-secondary">📤 Exportar CSV</button>
</div>

<table class="table" border="1" cellspacing="0" cellpadding="5" id="tablaEventuales">
    <tr style="background:#ddd; cursor:pointer;">
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

<!-- JS: ORDENAR Y EXPORTAR -->
<script>
// === ORDENAR TABLA ===
document.addEventListener("DOMContentLoaded", function() {
    const table = document.getElementById("tablaEventuales");
    const headers = table.querySelectorAll("th");
    let sortDirection = 1;

    headers.forEach((header, index) => {
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

    // === EXPORTAR A CSV ===
    document.getElementById("btnExportar").addEventListener("click", () => {
        let csv = [];
        const rows = table.querySelectorAll("tr");
        rows.forEach(row => {
            const cols = row.querySelectorAll("th, td");
            const values = Array.from(cols).map(col => `"${col.innerText.replace(/"/g, '""')}"`);
            csv.push(values.join(";"));
        });

        const blob = new Blob([csv.join("\n")], { type: "text/csv;charset=utf-8;" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "eventuales_3anios.csv";
        a.click();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>