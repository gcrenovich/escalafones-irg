<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$delete = $_GET['delete'] ?? null;
$nombre = $legajo = $categoria = $fecha_ingreso = "";
$dias_actuales = $dias_totales = 0;

// Eliminar empleado
if ($delete) {
    $stmt = $conn->prepare("DELETE FROM empleados WHERE id = ?");
    $stmt->bind_param("i", $delete);
    $stmt->execute();
    header("Location: index.php");
    exit;
}

// Si edita, cargar datos
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM empleados WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $legajo = $row['legajo'];
        $nombre = $row['nombre'];
        $categoria = $row['categoria'];
        $fecha_ingreso = $row['fecha_ingreso'];
        $dias_actuales = $row['dias_actuales'];
        $dias_totales = $row['dias_totales'];
    }
}

// Guardar empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $legajo = $_POST['legajo'];
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $dias_actuales = $_POST['dias_actuales'];
    $dias_totales = $_POST['dias_totales'];

    if ($id) {
        $stmt = $conn->prepare("UPDATE empleados SET legajo=?, nombre=?, categoria=?, fecha_ingreso=?, dias_actuales=?, dias_totales=? WHERE id=?");
        $stmt->bind_param("ssssiii", $legajo, $nombre, $categoria, $fecha_ingreso, $dias_actuales, $dias_totales, $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO empleados (legajo, nombre, categoria, fecha_ingreso, dias_actuales, dias_totales) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $legajo, $nombre, $categoria, $fecha_ingreso, $dias_actuales, $dias_totales);
        $stmt->execute();
    }

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? "Editar" : "Nuevo" ?> Empleado</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <h1><?= $id ? "Editar" : "Nuevo" ?> Empleado</h1>

    <form method="post">
        <label>Legajo</label>
        <input type="text" name="legajo" value="<?= htmlspecialchars($legajo) ?>" required>

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>

        <label>Categoría</label>
        <select name="categoria" required>
            <option value="eventual" <?= $categoria === 'eventual' ? 'selected' : '' ?>>Eventual</option>
            <option value="transitorio" <?= $categoria === 'transitorio' ? 'selected' : '' ?>>Transitorio</option>
        </select>

        <label>Fecha de ingreso</label>
        <input type="date" name="fecha_ingreso" value="<?= htmlspecialchars($fecha_ingreso) ?>" required>

        <label>Días actuales</label>
        <input type="number" name="dias_actuales" value="<?= htmlspecialchars($dias_actuales) ?>">

        <label>Días totales</label>
        <input type="number" name="dias_totales" value="<?= htmlspecialchars($dias_totales) ?>">

        <button type="submit" class="btn">💾 Guardar</button>
        <a href="index.php" class="btn btn-secondary">↩ Volver</a>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
