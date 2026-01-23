<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para editar un cargo
function editarCargo($id, $nuevoNombre) {
    global $conexion;

    // Validar el ID antes de realizar la edición
    if (validarID($id)) {
        // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
        $query = "UPDATE Cargo SET nombre_cargo = $1 WHERE id_cargo = $2";
        $result = pg_query_params($conexion, $query, array($nuevoNombre, $id));

        if (!$result) {
            die("Error en la consulta de actualización: " . pg_last_error());
        }

        // Devolver una respuesta JSON exitosa
        echo json_encode(array('success' => true));
    } else {
        // Devolver una respuesta JSON con error si el ID no es válido
        echo json_encode(array('success' => false, 'message' => 'ID de cargo no válido.'));
    }
}

// Verificar si se proporciona un ID válido y un nuevo nombre
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_cargo']) && isset($_POST['nuevo_nombre_cargo'])) {
    $idCargoEditar = $_POST['id_cargo'];
    $nuevoNombreCargo = $_POST['nuevo_nombre_cargo'];

    // Validar los datos
    if (empty($nuevoNombreCargo)) {
        // Devolver una respuesta JSON con error si el nuevo nombre está vacío
        echo json_encode(array('success' => false, 'message' => 'Por favor, ingresa un nuevo nombre para el cargo.'));
    } else {
        // Editar el cargo con el nuevo nombre
        editarCargo($idCargoEditar, $nuevoNombreCargo);
    }
} else {
    // Devolver una respuesta JSON con error si no se proporciona un ID o un nuevo nombre
    echo json_encode(array('success' => false, 'message' => 'ID de cargo o nuevo nombre no proporcionado.'));
}
?>
