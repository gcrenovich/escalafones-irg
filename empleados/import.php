<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
if (!isset($_SESSION['username'])) { header('Location: /login.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $tmp = $_FILES['archivo']['tmp_name'];
    $name = $_FILES['archivo']['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $rows = [];

    if (in_array($ext,['csv','txt'])) {
        if (($h = fopen($tmp,'r')) !== false) {
            $header = fgetcsv($h,0,',');
            while (($r = fgetcsv($h,0,',')) !== false) $rows[] = $r;
            fclose($h);
        }
    } else {
        $msg = "Sólo se permiten CSV en esta importación (o instalar PhpSpreadsheet para XLS/XLSX).";
    }

    $processed = 0;
    foreach($rows as $r) {
        $leg = trim($r[0] ?? '');
        $nom = trim($r[1] ?? '');
        $fing = trim($r[2] ?? '');
        $cat = strtolower(trim($r[3] ?? 'eventual'));
        if ($leg=='' || $nom=='') continue;
        // upsert
        $stm = $mysqli->prepare("SELECT legajo FROM empleados WHERE legajo = ?");
        $stm->bind_param('s',$leg); $stm->execute();
        if ($stm->get_result()->fetch_assoc()) {
            $up = $mysqli->prepare("UPDATE empleados SET nombre=?, categoria=?, fecha_ingreso=? WHERE legajo=?");
            $up->bind_param('ssss',$nom,$cat,$fing,$leg);
            $up->execute();
        } else {
            $ins = $mysqli->prepare("INSERT INTO empleados (legajo,nombre,categoria,fecha_ingreso) VALUES (?,?,?,?)");
            $ins->bind_param('ssss',$leg,$nom,$cat,$fing);
            $ins->execute();
        }
        $processed++;
    }
    $msg = "Importación finalizada. Filas procesadas: $processed";
}
?>

<h2>Importar Empleados (CSV)</h2>
<?php if($msg) echo "<p>$msg</p>"; ?>
<form method="post" enctype="multipart/form-data">
  <label>Archivo CSV (legajo,nombre,fecha_ingreso,categoria)</label><br>
  <input type="file" name="archivo" accept=".csv" required><br><br>
  <button>Subir</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
