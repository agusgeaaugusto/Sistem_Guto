<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para editar un rol
function editarRol($id, $nuevaDescripcion) {
    global $conexion;

    // Validar el ID antes de realizar la edición
    if (validarID($id)) {
        // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
        $query = "UPDATE Roles SET descripcion_rol = $1 WHERE id_rol = $2";
        $result = pg_query_params($conexion, $query, array($nuevaDescripcion, $id));

        if (!$result) {
            die("Error en la consulta de actualización: " . pg_last_error($conexion));
        }

        // Devolver una respuesta JSON exitosa
        echo json_encode(array('success' => true));
    } else {
        // Devolver una respuesta JSON con error si el ID no es válido
        echo json_encode(array('success' => false, 'message' => 'ID de rol no válido.'));
    }
}

// Verificar si se proporciona un ID válido y una nueva descripción
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_rol']) && isset($_POST['nueva_descripcion_rol'])) {
    $idRolEditar = $_POST['id_rol'];
    $nuevaDescripcionRol = $_POST['nueva_descripcion_rol'];

    // Validar los datos
    if (empty($nuevaDescripcionRol)) {
        // Devolver una respuesta JSON con error si la nueva descripción está vacía
        echo json_encode(array('success' => false, 'message' => 'Por favor, ingresa una nueva descripción para el rol.'));
    } else {
        // Editar el rol con la nueva descripción
        editarRol($idRolEditar, $nuevaDescripcionRol);
    }
} else {
    // Devolver una respuesta JSON con error si no se proporciona un ID o una nueva descripción
    echo json_encode(array('success' => false, 'message' => 'ID de rol o nueva descripción no proporcionado.'));
}
?>
