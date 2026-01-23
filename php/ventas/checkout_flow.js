import { TICKET_PRINTER_NAME } from './config_impresoras.js';

/** Utils **/
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
const fmt = (n) => new Intl.NumberFormat('es-PY').format(n ?? 0);

function toIntFromTxt(txt, def = 0) {
  if (txt == null) return def;
  const s = String(txt).replace(/[^\d\-]/g, '');
  const n = parseInt(s, 10);
  return Number.isFinite(n) ? n : def;
}

function openNew(url) {
  if (!url) return;
  const w = window.open(url, '_blank', 'noopener');
  if (!w) {
    const a = document.createElement('a');
    a.href = url;
    a.target = '_blank';
    a.rel = 'noopener';
    document.body.appendChild(a);
    a.click();
    setTimeout(() => a.remove(), 250);
  }
}

function limpiarUI() {
  try {
    if (window.Carrito?.vaciar) window.Carrito.vaciar();
    if (window.Pagos?.reset) window.Pagos.reset();
    if (typeof window.limpiarCarritoUI === 'function') window.limpiarCarritoUI();
  } catch (e) {}

  // Limpieza genérica UI (fallback)
  const contCarrito = $('#carrito-lista, .carrito-lista, #lista-carrito');
  if (contCarrito) contCarrito.innerHTML = '';

  const totalEl = $('#total-venta, #totalVenta, .total-venta');
  if (totalEl) totalEl.textContent = '0';

  const recibidoEl = $('#recibido-input, #recibidoGs, #recibido');
  if (recibidoEl) recibidoEl.value = '';

  const vueltoEl = $('#vuelto, #vueltoGs, .vuelto');
  if (vueltoEl) vueltoEl.textContent = '0';
}

/** POST helper (robusto) */
async function postJSON(url, data) {
  const rsp = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(data),
    cache: 'no-store'
  });

  const raw = await rsp.text();
  let j = null;

  try { j = JSON.parse(raw); } catch (_) {}

  if (!rsp.ok) {
    // si no es JSON, mostramos crudo para depurar PHP warnings
    if (!j) console.error('[postJSON] Respuesta cruda:', raw);
    throw new Error(j?.msg || j?.mensaje || `HTTP ${rsp.status}`);
  }

  if (!j) {
    console.error('[postJSON] Respuesta cruda:', raw);
    throw new Error('Respuesta inválida del servidor (no es JSON).');
  }

  return j;
}

/** Imprimir ticket */
function imprimirTicket(id_venta) {
  const url =
    `imprimir_ticket.php?id_venta=${encodeURIComponent(id_venta)}` +
    `&printer=${encodeURIComponent(TICKET_PRINTER_NAME || '')}`;
  openNew(url);
}

/** Imprimir factura */
function imprimirFactura(id_venta) {
  const url = `imprimir_factura.php?id_venta=${encodeURIComponent(id_venta)}`;
  openNew(url);
}

/** Abre modal de FACTURA */
function abrirModalFactura({ id_venta, total }) {
  let modal = $('#modal-factura-auto');

  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'modal-factura-auto';
    modal.className = 'modal-auto hidden';
    modal.innerHTML = `
      <div class="modal-backdrop"></div>
      <div class="modal-card modal-content" role="dialog" aria-modal="true">
        <div class="modal-head">
          <h3>Factura</h3>
          <button class="btn-close" id="mf-cerrar" type="button">×</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <label>Condición:</label>
            <select id="mf-condicion">
              <option value="CONTADO">CONTADO</option>
              <option value="CREDITO">CRÉDITO</option>
            </select>
          </div>
          <div class="row">
            <small>Total: <strong id="mf-total">0</strong> Gs.</small>
          </div>
          <div class="row">
            <label>Nº de Factura:</label>
            <input id="mf-numero" placeholder="(opcional si tu backend lo genera)" />
          </div>
          <input type="hidden" id="mf-id-venta" />
        </div>
        <div class="modal-foot">
          <button id="mf-guardar" class="btn-primario" type="button">Guardar</button>
          <button id="mf-imprimir" class="btn-secundario" type="button">Imprimir</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);

    // estilos mínimos
    const style = document.createElement('style');
    style.textContent = `
      .modal-auto.hidden{display:none}
      .modal-auto{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center}
      .modal-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.5)}
      .modal-card{position:relative;background:#0f1217;border:1px solid #2b2f3b;border-radius:12px;padding:16px;min-width:320px;max-width:92vw;color:#d4d7e3}
      .modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
      .row{display:flex;gap:8px;align-items:center;margin:8px 0}
      select,input{background:#0a0d13;border:1px solid #2b2f3b;border-radius:8px;color:#d4d7e3;padding:8px;flex:1}
      label{min-width:90px}
      .btn-primario{background:#2563eb;border:1px solid #2563eb;color:#fff;border-radius:10px;padding:10px 12px}
      .btn-secundario{background:#0a0d13;border:1px solid #2b2f3b;color:#d4d7e3;border-radius:10px;padding:10px 12px}
      .btn-close{background:#0a0d13;border:1px solid #2b2f3b;color:#d4d7e3;border-radius:8px;padding:4px 10px}
      .modal-foot{display:flex;gap:8px;justify-content:flex-end;margin-top:12px}
    `;
    document.head.appendChild(style);

    // cerrar (X, backdrop, Escape)
    $('#mf-cerrar', modal).addEventListener('click', () => modal.classList.add('hidden'));
    $('.modal-backdrop', modal).addEventListener('click', () => modal.classList.add('hidden'));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !modal.classList.contains('hidden')) modal.classList.add('hidden');
    });

    // Guardar factura asociada a la venta
    $('#mf-guardar', modal).addEventListener('click', async () => {
      const condicion = $('#mf-condicion', modal).value;
      const nro = $('#mf-numero', modal).value?.trim() || null;
      const idv = $('#mf-id-venta', modal).value?.trim();

      if (!idv) return alert('No hay id_venta para facturar. Guardá una venta primero.');

      try {
        const resp = await postJSON('guardar_venta.php', {
          id_venta: idv,
          es_factura: 1,
          condicion,
          nrofactura: nro
        });
        if (!resp?.ok) throw new Error(resp?.msg || 'No se pudo guardar factura');
        alert('Factura guardada.');
      } catch (e) {
        alert('Error al guardar factura: ' + (e.message || e));
      }
    });

    // Imprimir factura de esa venta
    $('#mf-imprimir', modal).addEventListener('click', () => {
      const idv = $('#mf-id-venta', modal).value?.trim();
      if (!idv) return alert('No hay id_venta para imprimir.');
      imprimirFactura(idv);
    });
  }

  // set data actual
  $('#mf-total', modal).textContent = fmt(total ?? 0);
  $('#mf-id-venta', modal).value = id_venta ? String(id_venta) : '';
  modal.classList.remove('hidden');
}

/** COBRO RÁPIDO = guarda venta y manda directo a ticket */
async function cobroRapido(payloadVenta) {
  const totalFallback =
    toIntFromTxt($('#total-venta')?.textContent, 0) ||
    toIntFromTxt($('#totalVenta')?.textContent, 0);

  const total = payloadVenta?.total ?? totalFallback;

  let resp;
  try {
    resp = await postJSON('guardar_venta.php', {
      ...payloadVenta,
      total,
      tipo: 'ticket',
      cobro_rapido: 1,
      es_ticket: 1
      // OJO: es_factura acá solo si tu backend soporta "guardar info mínima" sin imprimir
      // es_factura: 1
    });
  } catch (e) {
    alert('Error al guardar venta: ' + (e.message || e));
    return;
  }

  if (!resp?.ok) {
    alert(resp?.msg || 'No se pudo guardar la venta.');
    return;
  }

  const id_venta = resp.id_venta ?? resp.id ?? resp.idVenta ?? null;
  if (!id_venta) {
    alert('Venta guardada, pero el servidor no devolvió id_venta.');
    return;
  }

  // guardamos para botones de imprimir luego
  window.UltimaVentaID = id_venta;

  // imprimir ticket
  imprimirTicket(id_venta);

  // limpiar UI (sin reload)
  limpiarUI();
}

/** CHANGE LABELS + HOOKS **/
function ensureActionBar() {
  // evita doble bind
  if (window.__checkoutBarWired) return;
  window.__checkoutBarWired = true;

  // Si ya existen botones, sólo conectamos; si no, creamos barra flotante.
  let btnCR = $('#btn-cobro-rapido');
  let btnIF = $('#btn-imprimir-factura');
  let btnIT = $('#btn-imprimir-ticket');

  if (!btnCR || !btnIF || !btnIT) {
    let bar = $('#barra-acciones-checkout');
    if (!bar) {
      bar = document.createElement('div');
      bar.id = 'barra-acciones-checkout';
      bar.innerHTML = `
        <div class="checkout-bar">
          <button id="btn-cobro-rapido" type="button">Cobro Rápido</button>
          <button id="btn-imprimir-factura" type="button">Imprimir Factura</button>
          <button id="btn-imprimir-ticket" type="button">Imprimir Ticket</button>
        </div>`;
      document.body.appendChild(bar);

      const style = document.createElement('style');
      style.textContent = `
        .checkout-bar{position:fixed;right:16px;bottom:16px;display:flex;gap:8px;background:#0a0d13;border:1px solid #2b2f3b;border-radius:12px;padding:10px;z-index:9999}
        .checkout-bar button{cursor:pointer;border-radius:10px;padding:10px 12px;border:1px solid #2b2f3b;background:#121826;color:#d4d7e3}
        .checkout-bar button:hover{background:#0f1623}
      `;
      document.head.appendChild(style);
    }
    btnCR = $('#btn-cobro-rapido');
    btnIF = $('#btn-imprimir-factura');
    btnIT = $('#btn-imprimir-ticket');
  }

  // renombrar por si existían con otros textos
  btnIF.textContent = 'Imprimir Factura';
  btnIT.textContent = 'Imprimir Ticket';

  // listeners (sin mezclar flujos)
  btnCR.addEventListener('click', async () => {
    const items = window.Carrito?.items ?? window.carrito ?? [];
    const total = toIntFromTxt($('#total-venta')?.textContent, 0);
    const recibido = toIntFromTxt($('#recibidoGs')?.value, total) || total;
    const vuelto = Math.max(0, recibido - total);
    await cobroRapido({ items, total, pago: { recibido, vuelto }, recibido, vuelto });
  });

  btnIF.addEventListener('click', () => {
    const id = window.UltimaVentaID || $('#id-venta')?.textContent?.trim() || '';
    const total = toIntFromTxt($('#total-venta')?.textContent, 0);
    abrirModalFactura({ id_venta: id, total });
  });

  btnIT.addEventListener('click', () => {
    const id = window.UltimaVentaID || $('#id-venta')?.textContent?.trim() || '';
    if (!id) {
      alert('Aún no hay venta para imprimir ticket. Usá Cobro Rápido o guardá primero.');
      return;
    }
    imprimirTicket(id);
  });
}

window.addEventListener('DOMContentLoaded', ensureActionBar);
