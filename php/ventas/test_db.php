<?php
/**
 * test_db.php
 * Prueba rápida de conexión + SELECT simple sobre tabla venta.
 * Abre en el navegador para verificar que tu conexion_bi.php funciona.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/conexion_bi.php";

echo "<pre>";
echo "Conexión OK. Intentando SELECT en tabla venta...\n";

try {
    // Crea la tabla si no existe (opcional para pruebas)
    $conn->exec("CREATE TABLE IF NOT EXISTS venta (
        id_venta SERIAL PRIMARY KEY,
        fecha TIMESTAMP NOT NULL DEFAULT NOW(),
        id_per INT,
        total NUMERIC(12,2) NOT NULL DEFAULT 0,
        estado VARCHAR(20) NOT NULL DEFAULT 'ACTIVO'
    );");

    $stmt = $conn->query("SELECT COUNT(*) AS c FROM venta;");
    $row = $stmt->fetch();
    echo "Filas actuales en venta: " . ($row ? $row['c'] : '0') . "\n";
    echo "TODO OK.\n";
} catch (Throwable $e) {
    echo "ERROR en consulta: " . $e->getMessage() . "\n";
}
echo "</pre>";
