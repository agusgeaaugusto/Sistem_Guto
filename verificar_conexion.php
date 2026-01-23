<?php
$host = "localhost";
$port = "5432";
$dbname = "sistem";
$user = "postgres";
$password = "admin";

$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

/*if ($conexion) {
    echo "✅ Conectado exitosamente a la base de datos PostgreSQL.";
} else {
    echo "❌ Error de conexión: " . pg_last_error();
}*/
?>
