<?php
include 'conexion_bi.php';

header('Content-Type: application/json; charset=utf-8');

function validarID($id) {
    return isset($id) && is_numeric($id) && $id > 0;
}

// 📌 Ruta para guardar imágenes
$directorioImagenes = '../img/productos/';

// Si no existe la carpeta, la crea
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
    $imagen           = '';

    // 🛑 Validar campos obligatorios
    if ($nombre_pro === '' || $codigo_barra_pro === '' || $uni_caja_pro <= 0 || $iva_pro < 0 || $id_cat <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan campos obligatorios.']);
        exit;
    }

    // 📷 Subida de imagen si existe
    if (isset($_FILES['imagen']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
        $archivo = basename($_FILES['imagen']['name']);
        $archivo = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $archivo); // prevenir nombres inseguros
        $destino = $directorioImagenes . $archivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            $imagen = $archivo;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al subir la imagen.']);
            exit;
        }
    }

    // 📦 Insertar en la base de datos
    $query = "INSERT INTO producto (nombre_pro, codigo_barra_pro, uni_caja_pro, iva_pro, id_cat, favorito, imagen_pro)
              VALUES ($1, $2, $3, $4, $5, $6, $7)";
    $params = [$nombre_pro, $codigo_barra_pro, $uni_caja_pro, $iva_pro, $id_cat, $favorito, $imagen];

    $result = pg_query_params($conexion, $query, $params);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar en la base de datos: ' . pg_last_error()]);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

// 📤 Obtener lista de productos
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $query = "SELECT * FROM producto ORDER BY id_pro ASC";
    $result = pg_query($conexion, $query);

    $productos = [];
    while ($row = pg_fetch_assoc($result)) {
        $productos[] = $row;
    }

    echo json_encode($productos, JSON_UNESCAPED_UNICODE);
    pg_close($conexion);
}
?>
