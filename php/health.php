<?php
// health.php - Verificador de rutas de módulos
$base = __DIR__ . DIRECTORY_SEPARATOR;

$checks = [
  'dashboard.php',
  'acerca.php',
  'cargos/cargo.php',
  'categoria/categoria.php',
  'ventas/ventas.php',
  'comprovante/comprovante.php', // ojo: carpeta "comprovante" según tu árbol
  'persona/persona.php',
  'proveedor/proveedor.php',
  'usuario/usuario.php',
  'rol/rol.php',
  'gestion/gestion.php',
  'compra/compra.php',
  'producto/producto.php',
  'producto_det/producto.php',
  'producto_det/producto_det.php',
  'moneda/moneda.php',
  'compra_detalle/compra_detalle.php',
  'portafolio/admin.php',   // ojo: "portafolio" (no "portaforlio")
  'portafolio/clientes.php'
];

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>Health Check - Rutas</title>
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#0c0d12;color:#e6e9f5;margin:0;padding:24px}
  table{border-collapse:collapse;width:100%;max-width:920px}
  th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,.12);text-align:left}
  .ok{color:#22c55e;font-weight:700}
  .fail{color:#ef4444;font-weight:700}
  .muted{color:#a5adcb}
  code{background:#11131a;padding:2px 6px;border-radius:8px}
</style>
</head>
<body>
  <h1>Verificador de rutas</h1>
  <p class="muted">Base: <code><?php echo realpath($base); ?></code></p>
  <table>
    <thead><tr><th>Ruta</th><th>Existe</th><th>Lectura</th><th>Realpath</th></tr></thead>
    <tbody>
      <?php foreach ($checks as $rel): 
        $path = $base . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
        $existe = file_exists($path);
        $legible = $existe ? is_readable($path) : false;
        $real = $existe ? realpath($path) : '-';
      ?>
      <tr>
        <td><code><?php echo htmlspecialchars($rel); ?></code></td>
        <td class="<?php echo $existe ? 'ok' : 'fail'; ?>"><?php echo $existe ? 'Sí' : 'No'; ?></td>
        <td class="<?php echo $legible ? 'ok' : 'fail'; ?>"><?php echo $legible ? 'Sí' : 'No'; ?></td>
        <td class="muted"><?php echo htmlspecialchars($real); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
