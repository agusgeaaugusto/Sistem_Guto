<?php
include 'conexion_bi.php';

header('Content-Type: application/json; charset=utf-8');

// 📁 Carpeta para guardar imágenes
$directorioImagenes = '../img/productos/';

// Crear carpeta si no existe
if (!is_dir($directorioImagenes)) {
    if (!mkdir($directorioImagenes, 0777, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo crear la carpeta de imágenes.']);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_pro       = trim($_POST['nombre_pro'] ?? '');
    $codigo_barra_pro = trim($_POST['codigo_barra_pro'] ?? '');
    $uni_caja_pro     = isset($_POST['uni_caja_pro']) ? (int)$_POST['uni_caja_pro'] : 0;
    $iva_pro          = isset($_POST['iva_pro']) ? (float)$_POST['iva_pro'] : 0;
    $id_cat           = isset($_POST['id_cat']) ? (int)$_POST['id_cat'] : 0;
    $favorito         = isset($_POST['favorito']) && $_POST['favorito'] === '1';
    $precio1_pro      = isset($_POST['precio1_pro']) ? (float)$_POST['precio1_pro'] : 0;
    $imagen           = '';

    // Validar campos obligatorios
    if (
        $nombre_pro === '' || $codigo_barra_pro === '' ||
        $uni_caja_pro <= 0 || $iva_pro < 0 || $id_cat <= 0 || $precio1_pro <= 0
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan campos obligatorios o son inválidos.']);
        exit;
    }

    // Subir imagen si hay
    if (isset($_FILES['imagen']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
        $archivo = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['imagen']['name']));
        $destino = $directorioImagenes . $archivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            $imagen = $archivo;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al subir la imagen.']);
            exit;
        }
    }

    // Insertar producto
    $query = "INSERT INTO producto (nombre_pro, codigo_barra_pro, uni_caja_pro, iva_pro, id_cat, favorito, imagen_pro, precio1_pro)
              VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
    $params = [$nombre_pro, $codigo_barra_pro, $uni_caja_pro, $iva_pro, $id_cat, $favorito, $imagen, $precio1_pro];
    $result = pg_query_params($conexion, $query, $params);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar en la base de datos: ' . pg_last_error()]);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// GET: Obtener todos los productos (para favoritos)
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $query = "SELECT id_pro, nombre_pro, precio1_pro, codigo_barra_pro, imagen_pro, favorito
              FROM producto
              WHERE favorito = true
              ORDER BY id_pro ASC";

    $result = pg_query($conexion, $query);

    $productos = [];

    while ($row = pg_fetch_assoc($result)) {
        $row['favorito'] = in_array(strtolower($row['favorito']), ['1', 't', 'true'], true);
        $row['precio1_pro'] = floatval($row['precio1_pro']);
        $productos[] = $row;
    }

    echo json_encode($productos, JSON_UNESCAPED_UNICODE);
    pg_close($conexion);
}
?>
