<?php
$host = "192.168.0.125";
$port = "5432"; // Puerto por defecto de PostgreSQL
$dbname = "sistem";
$user = "postgres"; // Reemplaza 'tu_usuario' con el nombre de usuario de PostgreSQL
$password = "admin";
 // Reemplaza 'tu_contraseña' con la contraseña de PostgreSQL
$conexion = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

/*if (!$conexion) {
    die("No se ha podido conectar a la base de datos: " . pg_last_error());
}
//Puedes descomentar el siguiente código para verificar si la conexión fue exitosa
if($conexion){
    echo "Conectado exitosamente a la base de datos";
} else {
    echo "No se ha podido conectar a la base de datos";
}*/




//$conexion = mysqli_connect("localhost", "root","","login_register_db");
/*if($conexion){
    echo "conectado exitosamente a ala base de datos";
}else{
    echo "No se a podido conectar a la base de datos";
}*/
?>
