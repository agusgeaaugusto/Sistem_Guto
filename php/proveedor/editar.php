<?php
include 'conexion_bi.php';

// Función para validar un ID
function validarID($id) {
    return isset($id) && is_numeric($id);
}

// Función para editar un proveedor
function editarProveedor($id, $nuevoNombre, $nuevoRUC, $nuevaDireccion, $nuevoTelefono) {
    global $conexion;

    // Validar el ID antes de realizar la edición
    if (validarID($id)) {
        // Preparar la consulta con consultas preparadas para evitar inyecciones SQL
        $query = "UPDATE Proveedor SET nombre_prove = $1, ruc_prove = $2, direccion_prove = $3, telefono_prove = $4 WHERE id_proveedor = $5";
        $result = pg_query_params($conexion, $query, array($nuevoNombre, $nuevoRUC, $nuevaDireccion, $nuevoTelefono, $id));

        if (!$result) {
            die("Error en la consulta de actualización: " . pg_last_error());
        }

        // Devolver una respuesta JSON exitosa
        echo json_encode(array('success' => true));
    } else {
        // Devolver una respuesta JSON con error si el ID no es válido
        echo json_encode(array('success' => false, 'message' => 'ID de proveedor no válido.'));
    }
}

// Verificar si se proporciona un ID válido y nuevos datos del proveedor
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_proveedor']) && isset($_POST['nuevo_nombre_proveedor']) && isset($_POST['nuevo_ruc']) && isset($_POST['nueva_direccion']) && isset($_POST['nuevo_telefono'])) {
    $idProveedorEditar = $_POST['id_proveedor'];
    $nuevoNombreProveedor = $_POST['nuevo_nombre_proveedor'];
    $nuevoRUC = $_POST['nuevo_ruc'];
    $nuevaDireccion = $_POST['nueva_direccion'];
    $nuevoTelefono = $_POST['nuevo_telefono'];

    // Validar los datos
    if (empty($nuevoNombreProveedor) || empty($nuevoRUC) || empty($nuevaDireccion) || empty($nuevoTelefono)) {
        // Devolver una respuesta JSON con error si algún campo está vacío
        echo json_encode(array('success' => false, 'message' => 'Por favor, completa todos los campos.'));
    } else {
        // Editar el proveedor con los nuevos datos
        editarProveedor($idProveedorEditar, $nuevoNombreProveedor, $nuevoRUC, $nuevaDireccion, $nuevoTelefono);
    }
} else {
    // Devolver una respuesta JSON con error si no se proporciona un ID o nuevos datos
    echo json_encode(array('success' => false, 'message' => 'ID de proveedor o nuevos datos no proporcionados.'));
}
?>
