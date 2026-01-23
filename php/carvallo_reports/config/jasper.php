<?php
define('JASPERSTARTER_BIN', getenv('JASPERSTARTER_BIN') ?: 'C:\\jasperstarter\\bin\\jasperstarter.exe');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'carvallo');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');
define('DB_SCHEMA', getenv('DB_SCHEMA') ?: 'public');
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('REPORTS_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'reports');
function run_report($jrxml, $outputPdf, $params = []) {
  $jrxmlPath = REPORTS_PATH . DIRECTORY_SEPARATOR . $jrxml;
  if (!file_exists($jrxmlPath)) { http_response_code(500); die('JRXML no encontrado: ' . $jrxmlPath); }
  $outBase = preg_replace('/\.pdf$/i', '', $outputPdf);
  $outDir  = dirname($outBase);
  if (!is_dir($outDir)) mkdir($outDir, 0777, true);
  $cmd = [
    escapeshellarg(JASPERSTARTER_BIN), 'pr', escapeshellarg($jrxmlPath),
    '-f', 'pdf', '-o', escapeshellarg($outBase),
    '-t', 'postgres', '-H', escapeshellarg(DB_HOST),
    '-n', escapeshellarg(DB_NAME), '-u', escapeshellarg(DB_USER), '-p', escapeshellarg(DB_PASS),
    '--db-port', escapeshellarg(DB_PORT), '--jdbc-dir', escapeshellarg(getenv('JDBC_DIR') ?: '')
  ];
  foreach ($params as $k => $v) { $cmd[] = '-P'; $cmd[] = escapeshellarg($k . '=' . $v); }
  $cmdline = implode(' ', $cmd);
  $descriptor = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
  $proc = proc_open($cmdline, $descriptor, $pipes);
  if (!is_resource($proc)) { http_response_code(500); die('No se pudo lanzar JasperStarter.'); }
  $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
  $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
  $exit = proc_close($proc);
  if ($exit !== 0) { http_response_code(500); header('Content-Type: text/plain; charset=utf-8'); echo 'Error JasperStarter (' . $exit . '):\n' . $stderr . '\n' . $stdout; exit; }
  $pdf = $outBase . '.pdf';
  if (!file_exists($pdf)) { http_response_code(500); die('No se generó el PDF esperado.'); }
  header('Content-Type: application/pdf'); header('Content-Disposition: inline; filename=' . basename($pdf)); readfile($pdf); exit;
}


