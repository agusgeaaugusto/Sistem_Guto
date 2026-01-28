<?php
declare(strict_types=1);

/**
 * conexion_bi.php — PostgreSQL (Carvallo Bodega)
 * Nota: En producción, no dejes credenciales hardcodeadas.
 */

$host = "192.168.0.125";
$port = "5432";
$dbname = "sistem";
$user = "postgres";
$password = "admin";

$conexion = @pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$password}");
if (!$conexion) {
  http_response_code(500);
  die("Sin conexión DB: " . pg_last_error());
}

// Asegura formato de fechas consistente si usás DateStyle en server
@pg_query($conexion, "SET datestyle TO ISO, YMD");
?>