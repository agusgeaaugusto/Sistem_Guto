<?php
session_start();
session_unset();
session_destroy();
// Para asegurar que no se guarde la sesión al cerrar el navegador
setcookie(session_name(), '', time() - 3600, '/');


header("Location: ../login.php");
exit();
?>
