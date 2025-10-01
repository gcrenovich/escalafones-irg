// Insertar en la tabla registros
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

// Ajustar escalafón si corresponde
$q = $conn->prepare("SELECT dias_actuales, escalafon FROM empleados WHERE legajo = ?");
$q->bind_param("i", $legajo);
$q->execute();
$q->bind_result($dias_actuales, $escalafon);
$q->fetch();
$q->close();

if ($dias_actuales >= 270) {
    $nuevoEscalafon = $escalafon + floor($dias_actuales / 270);
    $nuevoDiasActuales = $dias_actuales % 270;

    $upd2 = $conn->prepare("UPDATE empleados SET escalafon = ?, dias_actuales = ? WHERE legajo = ?");
    $upd2->bind_param("dii", $nuevoEscalafon, $nuevoDiasActuales, $legajo);
    $upd2->execute();
}
