<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Filtro de Ventas</title>
    <link rel="stylesheet" href="../../assents/css/styles.css">
</head>
<body>
    <h2>Generar Reporte de Ventas por Fecha</h2>
    <form action="reporte_ventas.php" method="get" target="_blank">
        <label for="desde">Desde:</label>
        <input type="date" name="desde" required>
        <label for="hasta">Hasta:</label>
        <input type="date" name="hasta" required>
        <button type="submit">Generar PDF</button>
    </form>
</body>
</html>
