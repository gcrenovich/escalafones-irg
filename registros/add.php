<?php
include '../includes/db.php';
include '../includes/head.php';
include '../includes/menu.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $concepto = $_POST['concepto'];
    $legajo = $_POST['legajo'];

    $stmt = $conn->prepare("INSERT INTO registros (fecha, concepto, legajo) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $fecha, $concepto, $legajo);
    $stmt->execute();

    echo "<p style='color:green'>✅ Registro agregado.</p>";
}
$empleados = $conn->query("SELECT legajo, nombre FROM empleados ORDER BY legajo ASC");
?>
<h2>Agregar Registro</h2>
<form method="POST">
    <label>Fecha:</label>
    <input type="date" name="fecha" required><br>
    <label>Concepto:</label>
    <input type="text" name="concepto" required><br>
    <label>Legajo:</label>
    <select name="legajo" required>
        <option value="">Seleccione...</option>
        <?php while($e = $empleados->fetch_assoc()): ?>
            <option value="<?= $e['legajo'] ?>"><?= $e['legajo']." - ".$e['nombre'] ?></option>
        <?php endwhile; ?>
    </select><br>
    <button type="submit">Guardar</button>
</form>
