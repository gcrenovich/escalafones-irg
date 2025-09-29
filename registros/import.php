<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['username'])) { header('Location: /login.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $tmp = $_FILES['archivo']['tmp_name'];
    $name = $_FILES['archivo']['name'];
    $rows = [];
    if (($h = fopen($tmp,'r')) !== false) {
        $header = fgetcsv($h,0,',');
        while (($r = fgetcsv($h,0,',')) !== false) $rows[] = $r;
        fclose($h);
    }
    $processed = 0;
    foreach($rows as $r) {
        $leg = trim($r[0] ?? '');
        $fecha = trim($r[1] ?? date('Y-m-d'));
        $unidad = strtolower(trim($r[2] ?? 'dias'));
        $cantidad = floatval($r[3] ?? 0);
        $concepto = trim($r[4] ?? null);
        if ($leg=='' || $cantidad<=0) continue;

        // existe empleado?
        $stm = $mysqli->prepare("SELECT legajo, dias_actuales FROM empleados WHERE legajo=?");
        $stm->bind_param('s',$leg); $stm->execute();
        $emp = $stm->get_result()->fetch_assoc();
        if (!$emp) {
            // ignorar o loguear
            file_put_contents(__DIR__ . '/../logs/import_errors.log', date('c')." - Empleado $leg no encontrado\n", FILE_APPEND);
            continue;
        }

        // calcular dias_equivalentes
        if ($unidad === 'horas') $dias = $cantidad / 8.0;
        else $dias = $cantidad;

        // insertar registro
        $ins = $mysqli->prepare("INSERT INTO registros (legajo, fecha, horas, dias_calculados, concepto) VALUES (?,?,?,?,?)");
        $horas_db = ($unidad === 'horas') ? intval($cantidad) : 0;
        $ins->bind_param('sidss', $leg, $fecha, $horas_db, $dias, $concepto);
        $ins->execute();

        // actualizar empleados: dias_actuales, dias_totales
        // sumar y comprobar ciclo 270
        $mysqli->begin_transaction();
        try {
            // obtener valores nuevos
            $update1 = $mysqli->prepare("UPDATE empleados SET dias_actuales = dias_actuales + ?, dias_totales = dias_totales + ? WHERE legajo = ?");
            $update1->bind_param('dds',$dias,$dias,$leg);
            $update1->execute();

            // leer dias_actuales
            $read = $mysqli->prepare("SELECT dias_actuales, escalafon FROM empleados WHERE legajo = ?");
            $read->bind_param('s',$leg); $read->execute();
            $cur = $read->get_result()->fetch_assoc();
            $cur_days = floatval($cur['dias_actuales']);
            $cur_escal = intval($cur['escalafon']);

            while ($cur_days >= 270.0) {
                $cur_escal += 1;
                $cur_days -= 270.0;
            }
            // actualizar escalafon y dias_actuales (resultado)
            $update2 = $mysqli->prepare("UPDATE empleados SET escalafon = ?, dias_actuales = ? WHERE legajo = ?");
            $update2->bind_param('ids',$cur_escal,$cur_days,$leg);
            $update2->execute();

            $mysqli->commit();
        } catch (Exception $e) {
            $mysqli->rollback();
            file_put_contents(__DIR__ . '/../logs/import_errors.log', date('c')." - Error al actualizar empleado $leg: ".$e->getMessage()."\n", FILE_APPEND);
            continue;
        }

        $processed++;
    }
    $msg = "Importación finalizada. Registros procesados: $processed";
}

?>

<h2>Importar Registros (CSV)</h2>
<?php if($msg) echo "<p>$msg</p>"; ?>
<form method="post" enctype="multipart/form-data">
  <label>Archivo CSV (legajo,fecha,unidad(dias|horas),cantidad,concepto)</label><br>
  <input type="file" name="archivo" accept=".csv" required><br><br>
  <button>Subir y procesar</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
