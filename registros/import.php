<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/footer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = fopen($_FILES['file']['tmp_name'], "r");
    $row = 0;
    $errores = [];
    $importados = 0;

    while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {
        if ($row == 0) { 
            $row++; 
            continue; // saltar encabezado
        }

        // CSV esperado: legajo;fecha;horas;dias
        $legajo = (int)$data[0];
        $fecha  = trim($data[1]);
        $horas  = isset($data[2]) ? (float)$data[2] : 0;
        $dias   = isset($data[3]) ? (float)$data[3] : 0;

        // 🔹 Si la fecha viene con "/", la convertimos a formato MySQL
        if (strpos($fecha, '/') !== false) {
            $partes = explode('/', $fecha);
            if (count($partes) === 3) {
                $fecha = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
            }
        }

        // 🔹 Si hay horas, convertirlas a días (8 horas = 1 día)
        if ($horas > 0) {
            $dias += $horas / 8;
        }

        // 🔹 Obtener el año y mes (para validación mensual)
        $mes = date('Y-m', strtotime($fecha));

        // 🔹 Consultar acumulado actual del mes
        $check = $conn->prepare("
            SELECT 
                IFNULL(SUM(dias_calculados),0) AS dias_mes,
                IFNULL(SUM(horas),0) AS horas_mes
            FROM registros 
            WHERE legajo = ? AND DATE_FORMAT(fecha, '%Y-%m') = ?
        ");
        $check->bind_param("is", $legajo, $mes);
        $check->execute();
        $check->bind_result($dias_mes, $horas_mes);
        $check->fetch();
        $check->close();

        // 🔹 Verificar límites
        $nuevo_dias = $dias_mes + $dias;
        $nuevo_horas = $horas_mes + $horas;

        if ($nuevo_dias > 31 || $nuevo_horas > 248) {
            $errores[] = "⚠️ El legajo <b>$legajo</b> supera el límite mensual (31 días o 248 horas) en <b>$mes</b>. Registro omitido.";
            continue;
        }

        try {
            // ---- INSERTAR REGISTRO ----
            $stmt = $conn->prepare("
                INSERT INTO registros (legajo, fecha, horas, dias_calculados) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isdd", $legajo, $fecha, $horas, $dias);
            $stmt->execute();

            // ---- ACTUALIZAR EMPLEADO ----
            $upd = $conn->prepare("
                UPDATE empleados 
                SET dias_totales = dias_totales + ?,
                    dias_actuales = dias_actuales + ?
                WHERE legajo = ?
            ");
            $upd->bind_param("ddi", $dias, $dias, $legajo);
            $upd->execute();

            // ---- AJUSTAR ESCALAFÓN ----
            $q = $conn->prepare("SELECT dias_actuales, escalafon FROM empleados WHERE legajo = ?");
            $q->bind_param("i", $legajo);
            $q->execute();
            $q->bind_result($dias_actuales, $escalafon);
            $q->fetch();
            $q->close();

            if ($dias_actuales >= 270) {
                $nuevoEscalafon = $escalafon + floor($dias_actuales / 270);
                $nuevoDiasActuales = $dias_actuales % 270;

                $upd2 = $conn->prepare("
                    UPDATE empleados 
                    SET escalafon = ?, dias_actuales = ?
                    WHERE legajo = ?
                ");
                $upd2->bind_param("dii", $nuevoEscalafon, $nuevoDiasActuales, $legajo);
                $upd2->execute();
            }

            $importados++;

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1452) { // código error FK
                $errores[] = "⚠️ El legajo <b>$legajo</b> no existe en la tabla empleados. Registro omitido.";
            } else {
                $errores[] = "⚠️ Error en legajo <b>$legajo</b>: " . $e->getMessage();
            }
        }
    }

    fclose($file);

    echo "<p style='color:green'>✅ $importados registros importados y empleados actualizados correctamente.</p>";
    if (!empty($errores)) {
        echo "<div style='color:red'><h3>Advertencias:</h3><ul>";
        foreach ($errores as $err) {
            echo "<li>$err</li>";
        }
        echo "</ul></div>";
    }
}
?>

<h2>Importar Registros</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" accept=".csv" required>
    <button type="submit">Importar</button>
</form>
<p>Formato CSV esperado: <b>legajo;fecha;horas;dias</b></p>
<p>👉 Ejemplo:</p>
<pre>
1001;2023-01-02;8;0
1001;2023-01-03;0;1
1002;2023-01-02;4;0
1003;2023-01-02;0;1
1003;2023-01-03;0;1
</pre>