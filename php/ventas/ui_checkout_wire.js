// === ui_checkout_wire.js (corregido y robusto) ===
import { buildPayloadFromUI, guardarVenta } from './venta_helpers.js';

function byTextContains(...subs) {
  const all = [...document.querySelectorAll('button, a, .btn')];
  return all.find((el) => {
    const t = (el.textContent || '').toLowerCase();
    return subs.every((s) => t.includes(String(s).toLowerCase()));
  });
}

function showError(msg) {
  if (typeof Swal !== 'undefined' && Swal?.fire) {
    Swal.fire({ title: 'Error', text: msg, icon: 'error' });
  } else {
    alert(msg);
  }
}

function openNew(url) {
  if (!url) return;
  const w = window.open(url, '_blank', 'noopener');
  if (!w) {
    const a = document.createElement('a');
    a.href = url; a.target = '_blank'; a.rel = 'noopener';
    document.body.appendChild(a);
    a.click();
    setTimeout(() => a.remove(), 300);
  }
}

// Evita duplicar listeners
function wireOnce(el, handler) {
  if (!el) return;
  if (el.dataset.wiredCheckout === '1') return;
  el.dataset.wiredCheckout = '1';
  el.addEventListener('click', handler);
}

// Decide qué hacer después de guardar
async function postSaveAction(kind, res, snapshot) {
  // 1) Si el backend devuelve URLs, usarlas
  if (kind === 'ticket' && res?.url_ticket) return openNew(res.url_ticket);
  if (kind === 'factura' && res?.url_factura) return openNew(res.url_factura);

  // 2) Si existe tu flow (venta_flow.js corregido), usarlo
  if (window.ventaFlow?.guardar && (kind === 'ticket' || kind === 'factura')) {
    return window.ventaFlow.guardar(kind);
  }

  // 3) Si existe preview, usarlo como fallback para factura
  if (kind === 'factura' && typeof window.abrirPreviewFactura === 'function') {
    return window.abrirPreviewFactura({
      id_venta: res?.id_venta,
      nrofactura: res?.nrofactura,
      total: res?.total,
      vuelto: res?.vuelto,
      recibido: snapshot?.pago?.recibido ?? null,
      descuento: snapshot?.descuento ?? 0,
      observacion: snapshot?.observacion ?? '',
      estado_venta: snapshot?.estado_venta ?? 'CER',
      items: snapshot?.items || []
    });
  }
}

function wireCheckoutButtons() {
  const bCR =
    document.getElementById('btn-cobro-rapido') ||
    byTextContains('cobro', 'rápido') ||
    byTextContains('cobro', 'rapido');

  const bIF =
    document.getElementById('btn-imprimir-factura') ||
    document.getElementById('btn-factura-legal') ||
    byTextContains('factura');

  const bIT =
    document.getElementById('btn-imprimir-ticket') ||
    document.getElementById('btn-cobro-rapido-ticket') ||
    byTextContains('ticket');

  // Cobro rápido = SOLO guardar (sin imprimir)
  wireOnce(bCR, async (e) => {
    e.preventDefault();
    try {
      const payload = buildPayloadFromUI();
      const snapshot = JSON.parse(JSON.stringify(payload));
      const res = await guardarVenta(payload);
      // No imprimir acá para evitar cruces
      // Si querés imprimir ticket por defecto, decime y lo ajusto.
      return res;
    } catch (err) {
      console.error(err);
      showError('Error al guardar: ' + (err?.message || err));
    }
  });

  // Factura = guardar + factura
  wireOnce(bIF, async (e) => {
    e.preventDefault();
    try {
      const payload = buildPayloadFromUI();
      // marcamos intención (si tu PHP lo aprovecha)
      payload.tipo = 'factura';
      payload.estado_venta = payload.estado_venta || 'CER';

      const snapshot = JSON.parse(JSON.stringify(payload));
      const res = await guardarVenta(payload);
      await postSaveAction('factura', res, snapshot);
      return res;
    } catch (err) {
      console.error(err);
      showError('Error al guardar: ' + (err?.message || err));
    }
  });

  // Ticket = guardar + ticket
  wireOnce(bIT, async (e) => {
    e.preventDefault();
    try {
      const payload = buildPayloadFromUI();
      payload.tipo = 'ticket';
      payload.estado_venta = payload.estado_venta || 'CER';

      const snapshot = JSON.parse(JSON.stringify(payload));
      const res = await guardarVenta(payload);
      await postSaveAction('ticket', res, snapshot);
      return res;
    } catch (err) {
      console.error(err);
      showError('Error al guardar: ' + (err?.message || err));
    }
  });
}

document.readyState === 'loading'
  ? document.addEventListener('DOMContentLoaded', wireCheckoutButtons)
  : wireCheckoutButtons();
