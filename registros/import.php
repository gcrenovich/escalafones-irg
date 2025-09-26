<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Verificar login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            if ($row == 1) continue; // Saltar encabezado

            // Asumimos que el CSV trae: legajo, fecha, concepto, cantidad
            $legajo = $data[0];
            $fecha = $data[1];
            $concepto = strtolower(trim($data[2])); 
            $cantidad = floatval($data[3]);

            // Buscar ID del empleado
            $stmt = $conn->prepare("SELECT id FROM empleados WHERE legajo=?");
            $stmt->bind_param("s", $legajo);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($emp = $result->fetch_assoc()) {
                $empleado_id = $emp['id'];

                // Convertir cantidad a días equivalentes
                $dias = 0;
                if ($concepto === "dia") {
                    $dias = $cantidad;
                } elseif ($concepto === "hora") {
                    $dias = $cantidad / 8; // 8 horas = 1 día
                } elseif ($concepto === "surco") {
                    $dias = $cantidad / 12; // 12 surcos = 1 día
                }

                // Guardar registro
                $stmt2 = $conn->prepare("INSERT INTO registros (empleado_id, fecha, concepto, cantidad, dias_equivalentes) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("isssd", $empleado_id, $fecha, $concepto, $cantidad, $dias);
                $stmt2->execute();

                // Actualizar acumuladores en empleados
                $stmt3 = $conn->prepare("UPDATE empleados SET dias_actuales = dias_actuales + ?, dias_totales = dias_totales + ? WHERE id=?");
                $stmt3->bind_param("ddi", $dias, $dias, $empleado_id);
                $stmt3->execute();
            }
        }
        fclose($handle);
        $msg = "Archivo procesado correctamente ✅";
    } else {
        $msg = "Error al abrir el archivo ❌";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Importar Registros</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container">
    <h1>📥 Importar registros</h1>
    <?php if ($msg): ?>
        <p><?= $msg ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Selecciona archivo CSV:</label>
        <input type="file" name="file" required>
        <button type="submit" class="btn">📤 Importar</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
