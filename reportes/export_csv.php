<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($_GET['token'])) {
    // acceso mínimo; en prod validar sesión/permiso
    // header('Location: /escalafones-irg/login.php'); exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=empleados_export.csv');

$out = fopen('php://output','w');
fputcsv($out, ['legajo','nombre','categoria','fecha_ingreso','dias_actuales','dias_totales','escalafon']);

$res = $mysqli->query("SELECT legajo,nombre,categoria,fecha_ingreso,dias_actuales,dias_totales,escalafon FROM empleados ORDER BY legajo ASC");
while ($r = $res->fetch_assoc()) {
    fputcsv($out, [$r['legajo'],$r['nombre'],$r['categoria'],$r['fecha_ingreso'],$r['dias_actuales'],$r['dias_totales'],$r['escalafon']]);
}
fclose($out);
exit;
