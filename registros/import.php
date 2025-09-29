<?php
include '../includes/db.php';
include '../includes/head.php';
include '../includes/menu.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = fopen($_FILES['file']['tmp_name'], "r");
    $row = 0;
    while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {
        if ($row == 0) { $row++; continue; } // saltar encabezado
        list($fecha, $concepto, $legajo) = $data;

        $stmt = $conn->prepare("INSERT INTO registros (fecha, concepto, legajo) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $fecha, $concepto, $legajo);
        $stmt->execute();
    }
    fclose($file);
    echo "<p style='color:green'>✅ Registros importados.</p>";
}
?>
<h2>Importar Registros</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" accept=".csv" required>
    <button type="submit">Importar</button>
</form>
<p>Formato CSV esperado: <b>fecha;concepto;legajo</b></p>
