<?php
include 'conexion_bi.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_pro            = isset($_POST['id_pro']) ? (int)$_POST['id_pro'] : 0;
    $nombre_pro        = trim($_POST['nombre_pro'] ?? '');
    $codigo_barra_pro  = trim($_POST['codigo_barra_pro'] ?? '');
    $uni_caja_pro      = isset($_POST['uni_caja_pro']) ? (int)$_POST['uni_caja_pro'] : 0;
    $iva_pro           = isset($_POST['iva_pro']) ? (float)$_POST['iva_pro'] : 0;
    $id_cat            = isset($_POST['id_cat']) ? (int)$_POST['id_cat'] : 0;
    $favorito          = isset($_POST['favorito']) && $_POST['favorito'] === '1';
    $imagen_nueva      = '';
    $directorioImagenes = '../img/productos/';

    if ($id_pro <= 0 || $nombre_pro === '' || $codigo_barra_pro === '' || $uni_caja_pro <= 0 || $id_cat <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos inválidos.']);
        exit;
    }

    // Verificamos si se subió una imagen nueva
    if (isset($_FILES['imagen']) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
        if (!is_dir($directorioImagenes)) {
            mkdir($directorioImagenes, 0777, true);
        }

        $archivo = basename($_FILES['imagen']['name']);
        $archivo = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $archivo);
        $destino = $directorioImagenes . $archivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            $imagen_nueva = $archivo;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al subir la imagen.']);
            exit;
        }
    }

    // Si no se sube imagen, mantener la anterior
    if ($imagen_nueva !== '') {
        $query = "UPDATE producto 
                  SET nombre_pro = $1, codigo_barra_pro = $2, uni_caja_pro = $3, iva_pro = $4, id_cat = $5, favorito = $6, imagen_pro = $7 
                  WHERE id_pro = $8";
        $params = [$nombre_pro, $codigo_barra_pro, $uni_caja_pro, $iva_pro, $id_cat, $favorito, $imagen_nueva, $id_pro];
    } else {
        $query = "UPDATE producto 
                  SET nombre_pro = $1, codigo_barra_pro = $2, uni_caja_pro = $3, iva_pro = $4, id_cat = $5, favorito = $6 
                  WHERE id_pro = $7";
        $params = [$nombre_pro, $codigo_barra_pro, $uni_caja_pro, $iva_pro, $id_cat, $favorito, $id_pro];
    }

    $result = pg_query_params($conexion, $query, $params);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar: ' . pg_last_error()]);
        exit;
    }

    echo json_encode(['success' => true]);
}
?>
