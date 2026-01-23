<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para editar un comprovante
function editarComprovante($id, $nuevoNombre) {
    global $conexion;

    // Validar el ID antes de realizar la edición
    if (validarID($id)) {
        // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
        $query = "UPDATE TipoComprovante SET nombre_comprovante = $1 WHERE id_comprovante = $2";
        $result = pg_query_params($conexion, $query, array($nuevoNombre, $id));

        if (!$result) {
            die("Error en la consulta de actualización: " . pg_last_error());
        }

        // Devolver una respuesta JSON exitosa
        echo json_encode(array('success' => true));
    } else {
        // Devolver una respuesta JSON con error si el ID no es válido
        echo json_encode(array('success' => false, 'message' => 'ID de comprovante no válido.'));
    }
}

// Verificar si se proporciona un ID válido y un nuevo nombre
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_comprovante']) && isset($_POST['nuevo_nombre_comprovante'])) {
    $idComprovanteEditar = $_POST['id_comprovante'];
    $nuevoNombreComprovante = $_POST['nuevo_nombre_comprovante'];

    // Validar los datos
    if (empty($nuevoNombreComprovante)) {
        // Devolver una respuesta JSON con error si el nuevo nombre está vacío
        echo json_encode(array('success' => false, 'message' => 'Por favor, ingresa un nuevo nombre para el comprovante.'));
    } else {
        // Editar el comprovante con el nuevo nombre
        editarComprovante($idComprovanteEditar, $nuevoNombreComprovante);
    }
} else {
    // Devolver una respuesta JSON con error si no se proporciona un ID o un nuevo nombre
    echo json_encode(array('success' => false, 'message' => 'ID de comprovante o nuevo nombre no proporcionado.'));
}
?>
