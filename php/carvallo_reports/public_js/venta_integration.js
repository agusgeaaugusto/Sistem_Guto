
async function postJSON(url, data) {
  const r = await fetch(url, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) });
  if (!r.ok) throw new Error('HTTP ' + r.status);
  return r.json();
}
async function getJSON(url) {
  const r = await fetch(url, { headers: {'Accept':'application/json'} });
  if (!r.ok) throw new Error('HTTP ' + r.status);
  return r.json();
}
let ultimaVentaId = null;
const $cobro   = document.getElementById('btn-cobro-rapido');
const $ticket  = document.getElementById('btn-cobro-rapido-ticket');
const $factura = document.getElementById('btn-factura-legal');
async function getVentaIdPreferida() {
  if (ultimaVentaId) return ultimaVentaId;
  const res = await getJSON('carvallo_reports/php/ultima_venta.php');
  if (!res.ok || !res.id_venta) throw new Error(res.error || res.msg || 'No hay ventas guardadas para imprimir.');
  return res.id_venta;
}
$cobro && $cobro.addEventListener('click', async () => {
  try {
    const payload = {
      cliente: {
        id: window.state?.cliente?.id ?? null,
        nombre: window.state?.cliente?.nombre ?? 'CONSUMIDOR FINAL',
        ruc: window.state?.cliente?.ruc ?? '',
        direccion: window.state?.cliente?.direccion ?? ''
      },
      items: (window.carrito || []).map(p => ({
        id_pro: p.id_pro, nombre_pro: p.nombre_pro, codigo_barra_pro: p.codigo_barra_pro,
        cantidad: p.cantidad, precio_unit: p.precio
      })),
      tipo_comprobante: 'TICKET',
      usuario_id: window.state?.usuario_id ?? 1,
      observacion: ''
    };
    const res = await postJSON('carvallo_reports/php/generar_venta.php', payload);
    if (!res.ok) throw new Error(res.error || 'No se pudo guardar la venta');
    ultimaVentaId = res.id_venta;
    alert('Venta guardada. ID: ' + ultimaVentaId);
  } catch (err) { alert('Error en cobro rápido: ' + err.message); }
});
$ticket && $ticket.addEventListener('click', async () => {
  try { const id = await getVentaIdPreferida(); window.open('carvallo_reports/php/reportes/ticket.php?id_venta=' + id, '_blank'); }
  catch (err) { alert(err.message); }
});
$factura && $factura.addEventListener('click', async () => {
  try { const id = await getVentaIdPreferida(); window.open('carvallo_reports/php/reportes/factura.php?id_venta=' + id, '_blank'); }
  catch (err) { alert(err.message); }
});
document.addEventListener('keydown', async (e) => {
  if (e.key === 'F12') {
    e.preventDefault();
    try { const id = await getVentaIdPreferida(); window.open('carvallo_reports/php/reportes/factura.php?id_venta=' + id, '_blank'); }
    catch (err) { alert(err.message); }
  }
});
