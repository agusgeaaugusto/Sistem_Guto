<?php
session_start();
require_once 'php/cargos/conexion_bi.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $clave = $_POST['clave'] ?? '';

    if ($usuario && $clave) {
        $query = "SELECT id_usu, nombre_usu, clave_usu, id_rol FROM usuario WHERE nombre_usu = $1";
        $result = pg_query_params($conexion, $query, array($usuario));

        if ($row = pg_fetch_assoc($result)) {
            if ($clave === $row['clave_usu']) {
                $_SESSION['usuario'] = $row['nombre_usu'];
                $_SESSION['id_usu'] = $row['id_usu'];
                $_SESSION['rol'] = $row['id_rol'];
                header("Location: php/index.php");
                exit();
            } else {
                $error = "Clave incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Carvallo Bodega</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f3f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 300px;
        }
        h2 {
            text-align: center;
            color: #195d75;
            margin-bottom: 20px;
        }
        label {
            font-size: 14px;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #2e7e9d;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #256b86;
        }
        .options {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #555;
        }
        .options input[type="checkbox"] {
            margin-right: 5px;
        }
        .error {
            color: red;
            font-size: 13px;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>LOGIN</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label for="usuario">Username</label>
            <input type="text" name="usuario" required>

            <label for="clave">Password</label>
            <input type="password" name="clave" required>

            <div class="options">
                <label><input type="checkbox"> Remember me</label>
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit">LOGIN</button>
        </form>
    </div>
</body>
</html>
