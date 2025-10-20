<?php
// reportes/export_csv.php

// Evitar que se mezclen warnings/notices en la salida CSV
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// Conexión
require_once __DIR__ . '/../config/db.php';

// Seguridad mínima: en producción validar sesión/roles
// if (!isset($_SESSION['username'])) { header('Location: /escalafones-irg/login.php'); exit; }

// Parámetros opcionales
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'empleados'; // empleados | eventuales
$desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';

// Cabeceras para descarga
$ts = date('Ymd_His');
$filename = ($tipo === 'eventuales') ? "eventuales_3anios_{$ts}.csv" : "empleados_{$ts}.csv";
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

// Abrir salida
$out = fopen('php://output','w');
// Escribir BOM para Excel UTF-8
fwrite($out, "\xEF\xBB\xBF");

// Dependiendo del tipo, preparamos la consulta
if ($tipo === 'eventuales') {
    // años dinámicos (3 años incluyendo actual)
    $year_actual = (int)date('Y');
    $year2 = $year_actual - 1;
    $year3 = $year_actual - 2;

    // Cabecera
    fputcsv($out, ['legajo','nombre', (string)$year3, (string)$year2, (string)$year_actual, 'total_3anos','estado'], ';');

    // Query - igual que en dashboard_eventuales
    $sql = "
        SELECT 
            e.legajo, 
            e.nombre,
            SUM(CASE WHEN YEAR(r.fecha) = {$year3} THEN r.dias_calculados ELSE 0 END) AS dias_{$year3},
            SUM(CASE WHEN YEAR(r.fecha) = {$year2} THEN r.dias_calculados ELSE 0 END) AS dias_{$year2},
            SUM(CASE WHEN YEAR(r.fecha) = {$year_actual} THEN r.dias_calculados ELSE 0 END) AS dias_{$year_actual},
            IFNULL(SUM(r.dias_calculados),0) AS total_3anos,
            CASE 
                WHEN IFNULL(SUM(r.dias_calculados),0) >= 300 THEN 'RIESGO'
                WHEN IFNULL(SUM(r.dias_calculados),0) >= 285 THEN 'ALERTA'
                ELSE 'NORMAL' 
            END AS estado
        FROM empleados e
        LEFT JOIN registros r 
            ON e.legajo = r.legajo 
            AND r.fecha >= DATE_SUB(CURDATE(), INTERVAL 3 YEAR)
        WHERE LOWER(e.categoria) = 'eventual'
    ";

    // Si vienen filtros desde/hasta, limitamos registros considerados (opcional)
    if ($desde && $hasta) {
        $desde_s = $conn->real_escape_string($desde);
        $hasta_s = $conn->real_escape_string($hasta);
        $sql .= " AND r.fecha BETWEEN '$desde_s' AND '$hasta_s' ";
    }

    $sql .= " GROUP BY e.legajo, e.nombre ORDER BY total_3anos DESC, e.legajo ASC";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($out, [
                $row['legajo'],
                $row['nombre'],
                round($row["dias_{$year3}"],2),
                round($row["dias_{$year2}"],2),
                round($row["dias_{$year_actual}"],2),
                round($row['total_3anos'],2),
                $row['estado']
            ], ';');
        }
    }
} else {
    // Exportar empleados (por defecto)
    fputcsv($out, ['legajo','nombre','categoria','fecha_ingreso','dias_actuales','dias_totales','escalafon'], ';');

    // Permite filtrar por rango de actividad (opcional)
    $where = "";
    if ($desde && $hasta) {
        $desde_s = $conn->real_escape_string($desde);
        $hasta_s = $conn->real_escape_string($hasta);
        $where = "AND e.legajo IN (SELECT DISTINCT legajo FROM registros WHERE fecha BETWEEN '$desde_s' AND '$hasta_s')";
    } elseif ($desde) {
        $desde_s = $conn->real_escape_string($desde);
        $where = "AND e.legajo IN (SELECT DISTINCT legajo FROM registros WHERE fecha >= '$desde_s')";
    } elseif ($hasta) {
        $hasta_s = $conn->real_escape_string($hasta);
        $where = "AND e.legajo IN (SELECT DISTINCT legajo FROM registros WHERE fecha <= '$hasta_s')";
    }

    $sql = "SELECT e.legajo,e.nombre,e.categoria,e.fecha_ingreso,e.dias_actuales,e.dias_totales,e.escalafon
            FROM empleados e
            WHERE 1=1 $where
            ORDER BY e.legajo ASC";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            fputcsv($out, [
                $r['legajo'],
                $r['nombre'],
                $r['categoria'],
                $r['fecha_ingreso'],
                $r['dias_actuales'],
                $r['dias_totales'],
                $r['escalafon']
            ], ';');
        }
    }
}

fclose($out);
exit;
