<?php
include 'conexion_bi.php';

// Configurar la cabecera para devolver JSON
header('Content-Type: application/json');

// Validar si la solicitud es POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar datos del formulario y limpiar
    $nombre_pro = isset($_POST['nombre_pro']) ? htmlspecialchars($_POST['nombre_pro']) : '';
    $codigo_barra_pro = isset($_POST['codigo_barra_pro']) ? htmlspecialchars($_POST['codigo_barra_pro']) : '';
    $uni_caja_pro = isset($_POST['uni_caja_pro']) ? (int)$_POST['uni_caja_pro'] : 0;
    $iva_pro = isset($_POST['iva_pro']) ? (float)$_POST['iva_pro'] : 0.0;
    $id_cat = isset($_POST['id_cat']) ? (int)$_POST['id_cat'] : 0;

    // Validar que los datos no estén vacíos
    if (empty($nombre_pro) || empty($codigo_barra_pro) || $uni_caja_pro <= 0 || $iva_pro < 0 || $id_cat <= 0) {
        echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios.']);
        exit();
    }

    // Verificar si el producto ya existe en la base de datos
    $query_check = "SELECT * FROM Producto WHERE codigo_barra_pro = $1";
    $result_check = pg_query_params($conexion, $query_check, array($codigo_barra_pro));

    if (pg_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'error' => 'El producto ya está registrado.']);
        exit();
    }

    // Insertar producto en la base de datos
    $query_insert = "INSERT INTO Producto (nombre_pro, codigo_barra_pro, uni_caja_pro, iva_pro, id_cat) VALUES ($1, $2, $3, $4, $5) RETURNING id_pro";
    $result_insert = pg_query_params($conexion, $query_insert, array($nombre_pro, $codigo_barra_pro, $uni_caja_pro, $iva_pro, $id_cat));

    if (!$result_insert) {
        echo json_encode(['success' => false, 'error' => pg_last_error($conexion)]);
        exit();
    }

    // Obtener el ID del producto recién insertado
    $nuevo_producto = pg_fetch_assoc($result_insert);

    // Consultar los datos del producto recién agregado
    $query_get = "SELECT * FROM Producto WHERE id_pro = $1";
    $result_get = pg_query_params($conexion, $query_get, array($nuevo_producto['id_pro']));
    $producto_data = pg_fetch_assoc($result_get);

    // Enviar la respuesta JSON con los datos del producto
    echo json_encode([
        'success' => true,
        'id_pro' => $producto_data['id_pro'],
        'nombre_pro' => $producto_data['nombre_pro'],
        'codigo_barra_pro' => $producto_data['codigo_barra_pro'],
        'uni_caja_pro' => $producto_data['uni_caja_pro'],
        'iva_pro' => $producto_data['iva_pro'],
        'id_cat' => $producto_data['id_cat']
    ]);

    // Cerrar conexión
    pg_close($conexion);
    exit();
}

// Si no es POST, devolver la lista de productos
$query = "SELECT * FROM Producto ORDER BY id_pro ASC";
$result = pg_query($conexion, $query);

$productos = array();
while ($row = pg_fetch_assoc($result)) {
    $productos[] = $row;
}

// Enviar los productos como respuesta JSON
echo json_encode($productos);

// Cerrar conexión
pg_close($conexion);
?>
