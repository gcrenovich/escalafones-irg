<?php
include '../includes/db.php';
include '../includes/head.php';
include '../includes/menu.php';

$result = $conn->query("
    SELECT r.id, r.fecha, r.concepto, e.legajo, e.nombre, e.categoria
    FROM registros r
    JOIN empleados e ON r.legajo = e.legajo
    ORDER BY r.fecha DESC
");
?>
<h2>Registros</h2>
<a href="add.php">➕ Agregar Registro</a> |
<a href="import.php">📂 Importar Registros</a>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th><th>Fecha</th><th>Concepto</th>
        <th>Legajo</th><th>Nombre</th><th>Categoría</th>
        <th>Acciones</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['fecha'] ?></td>
        <td><?= $row['concepto'] ?></td>
        <td><?= $row['legajo'] ?></td>
        <td><?= $row['nombre'] ?></td>
        <td><?= ucfirst($row['categoria']) ?></td>
        <td><a href="delete.php?id=<?= $row['id'] ?>">🗑️ Eliminar</a></td>
    </tr>
    <?php endwhile; ?>
</table>
