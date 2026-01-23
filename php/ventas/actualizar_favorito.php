<?php
include("conexion_bi.php"); // tu archivo de conexión

$data = json_decode(file_get_contents("php://input"), true);
$id_pro = $data["id_pro"];
$favorito = $data["favorito"] ? '1' : '0';

$query = "UPDATE producto SET favorito = $1 WHERE id_pro = $2";
$result = pg_query_params($conexion, $query, [$favorito, $id_pro]);

echo json_encode(["success" => $result !== false]);
