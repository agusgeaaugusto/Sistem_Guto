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
    <title>Filtro Reporte Detallado</title>
    <link rel="stylesheet" href="../../assents/css/styles.css">
</head>
<body>
    <h2>Reporte Detallado por Producto</h2>
    <form action="reporte_detallado_productos.php" method="get" target="_blank">
        <label for="desde">Desde:</label>
        <input type="date" name="desde" required>
        <label for="hasta">Hasta:</label>
        <input type="date" name="hasta" required>
        <button type="submit">Generar PDF</button>
    </form>
</body>
</html>
