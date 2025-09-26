<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Obtener lista de empleados
$sql = "SELECT * FROM empleados ORDER BY legajo ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados - Escalafones IRG</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <h1>👨‍💼 Empleados</h1>
    <a href="form.php" class="btn">➕ Nuevo Empleado</a>

    <table class="table">
        <thead>
            <tr>
                <th>Legajo</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Fecha Ingreso</th>
                <th>Días actuales</th>
                <th>Días totales</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['legajo']) ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= htmlspecialchars($row['categoria']) ?></td>
                    <td><?= htmlspecialchars($row['fecha_ingreso']) ?></td>
                    <td><?= htmlspecialchars($row['dias_actuales']) ?></td>
                    <td><?= htmlspecialchars($row['dias_totales']) ?></td>
                    <td>
                        <a href="form.php?id=<?= $row['id'] ?>" class="btn-small">✏️ Editar</a>
                        <a href="form.php?delete=<?= $row['id'] ?>" class="btn-small btn-danger" onclick="return confirm('¿Seguro de eliminar este empleado?');">🗑 Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No hay empleados cargados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
