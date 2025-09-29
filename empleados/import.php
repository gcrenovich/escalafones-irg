<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/footer.php';

if ($_POST && isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");
    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $legajo = $data[0];
        $nombre = $data[1];
        $categoria = $data[2];
        $fecha = $data[3];

        $conn->query("INSERT INTO empleados (legajo,nombre,categoria,fecha_ingreso) 
                      VALUES ('$legajo','$nombre','$categoria','$fecha')");
    }
    fclose($handle);
    header("Location: index.php");
}
?>

<h2>Importar Empleados</h2>
<form method="post" enctype="multipart/form-data">
  Archivo CSV: <input type="file" name="file" accept=".csv" required>
  <input type="submit" value="Importar">
</form>
