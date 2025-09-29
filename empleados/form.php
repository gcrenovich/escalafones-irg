<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/footer.php';
if (!isset($_SESSION['username'])) { header('Location: /login.php'); exit; }

$legajo = $_GET['legajo'] ?? null;
$editing = false;
$data = null;
if ($legajo) {
    $editing = true;
    $stm = $mysqli->prepare("SELECT * FROM empleados WHERE legajo = ?");
    $stm->bind_param('s',$legajo);
    $stm->execute();
    $data = $stm->get_result()->fetch_assoc();
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legajoP = trim($_POST['legajo']);
    $nombre = trim($_POST['nombre']);
    $categoria = $_POST['categoria'];
    $fecha_ing = $_POST['fecha_ingreso']?:null;

    if ($editing) {
        $up = $mysqli->prepare("UPDATE empleados SET nombre=?, categoria=?, fecha_ingreso=? WHERE legajo=?");
        $up->bind_param('ssss',$nombre,$categoria,$fecha_ing,$legajoP);
        $up->execute();
        $msg = 'Empleado actualizado.';
        header('Location: index.php'); exit;
    } else {
        $ins = $mysqli->prepare("INSERT INTO empleados (legajo,nombre,categoria,fecha_ingreso) VALUES (?,?,?,?)");
        $ins->bind_param('ssss',$legajoP,$nombre,$categoria,$fecha_ing);
        if ($ins->execute()) { header('Location: index.php'); exit; }
        else $msg = 'Error al crear empleado. Verifique legajo único.';
    }
}
?>

<h2><?= $editing ? "Editar empleado" : "Nuevo empleado" ?></h2>
<?php if($msg) echo "<p>$msg</p>"; ?>
<form method="post">
  <label>Legajo<br><input name="legajo" value="<?= $data['legajo'] ?? '' ?>" <?= $editing ? 'readonly' : '' ?> required></label><br>
  <label>Nombre<br><input name="nombre" value="<?= htmlspecialchars($data['nombre'] ?? '') ?>" required></label><br>
  <label>Categoría<br>
    <select name="categoria">
      <option value="eventual" <?= (!empty($data) && $data['categoria']=='eventual') ? 'selected' : '' ?>>eventual</option>
      <option value="transitorio" <?= (!empty($data) && $data['categoria']=='transitorio') ? 'selected' : '' ?>>transitorio</option>
    </select>
  </label><br>
  <label>Fecha ingreso<br><input type="date" name="fecha_ingreso" value="<?= $data['fecha_ingreso'] ?? '' ?>"></label><br><br>
  <button type="submit">Guardar</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
