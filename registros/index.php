<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$sql = "SELECT r.id, e.legajo, e.nombre, r.fecha, r.concepto, r.cantidad, r.dias_equivalentes
        FROM registros r
        JOIN empleados e ON r.empleado_id = e.id
        ORDER BY r.fecha DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registros - Escalafones IRG</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <h1>📑 Registros</h1>
    <a href="import.php" class="btn">📥 Importar CSV</a>

    <table class="table">
        <thead>
            <tr>
                <th>Legajo</th>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Cantidad</th>
                <th>Días equivalentes</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['legajo']) ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                    <td><?= htmlspecialchars($row['concepto']) ?></td>
                    <td><?= htmlspecialchars($row['cantidad']) ?></td>
                    <td><?= htmlspecialchars($row['dias_equivalentes']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No hay registros cargados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
