//* venta_flow.js (vFinal++ CORREGIDO) — Ticket y Factura sin cruces, con cotizaciones y pago */
(() => {
  if (window.__ventaFlowV6) return;
  window.__ventaFlowV6 = true;

  // === CONFIG: a qué archivo apunta la FACTURA ===
  // 'imprimir_factura.php'              -> factura estilo ticket 80mm
  // 'imprimir_factura_preimpresa.php'   -> factura A4 preimpresa (solo datos)
  const FACTURA_URL = 'imprimir_factura.php';

  // ========= Utils =========
  const toNumber = (v) => {
    if (v == null) return 0;
    if (typeof v === 'number') return Number.isFinite(v) ? v : 0;

    let s = String(v).trim();
    if (!s) return 0;

    // deja solo números y separadores
    s = s.replace(/\s+/g, '').replace(/[^\d,.\-]/g, '');

    const lastComma = s.lastIndexOf(',');
    const lastDot = s.lastIndexOf('.');

    if (lastComma !== -1 && lastDot !== -1) {
      // separador decimal = el más a la derecha
      if (lastComma > lastDot) {
        s = s.replace(/\./g, '').replace(',', '.');
      } else {
        s = s.replace(/,/g, '');
      }
    } else if (lastComma !== -1) {
      s = s.replace(/\./g, '').replace(',', '.');
    } else {
      s = s.replace(/,/g, '');
    }

    const n = parseFloat(s);
    return Number.isFinite(n) ? n : 0;
  };

  const leerCotiz = (sel, fallback) => {
    const el = document.querySelector(sel);
    if (!el) return fallback;
    const txt = (el.textContent || '').trim();
    const n = toNumber(txt);
    return Number.isFinite(n) && n > 0 ? Math.round(n) : fallback;
  };

  const COTIZ = {
    br:  leerCotiz('#cotizacion-real', 1450),  // R$
    usd: leerCotiz('#cotizacion-dolar', 7600) // US$
  };

  const totales = (items) => {
    let total = 0;
    for (const it of items) {
      const cant = Number(it.cantidad ?? it.qty ?? 1) || 1;
      const precio = Number(it.precio ?? it.precio_unit ?? it.unit_price ?? 0) || 0;
      total += cant * precio;
    }
    return { total: Math.round(total) }; // en Gs suele ser entero
  };

  const mapeaProducto = (p) => ({
    id_producto:   p.id_producto ?? p.id_pro ?? p.id ?? null,
    id_pro:        p.id_pro ?? p.id_producto ?? p.id ?? null, // compat
    codigo:        p.codigo ?? p.codigo_barra ?? p.codigo_barra_pro ?? p.barcode ?? null,
    descripcion:   p.descripcion ?? p.nombre ?? p.nombre_pro ?? 'Producto',
    nombre_pro:    p.nombre_pro ?? p.descripcion ?? p.nombre ?? 'Producto', // compat
    cantidad:      Number(p.cantidad ?? p.qty ?? 1),
    precio:        Number(p.precio ?? p.precio_unit ?? p.unit_price ?? 0),
    precio_unit:   Number(p.precio_unit ?? p.precio ?? p.unit_price ?? 0), // compat
    tipo_impuesto: String(p.tipo_impuesto ?? p.iva ?? '10')
  });

  // ===== Parser DOM (fallback) =====
  function leerCarritoDesdeTabla() {
    const rows = document.querySelectorAll(
      '#tbody-carrito tr, #tabla-carrito tbody tr, table.carrito tbody tr, table[data-role="carrito"] tbody tr, table#carrito tbody tr'
    );
    const items = [];
    rows.forEach((row) => {
      const ths = row.querySelectorAll('th');
      const tds = row.querySelectorAll('td');
      if (ths.length || tds.length === 0) return;

      const cellTxt = (i) => (tds[i]?.textContent || '').trim();
      const codigo = cellTxt(0);
      const nombre = cellTxt(1);

      const cantInp = tds[2]?.querySelector('input,select');
      const cantidad = toNumber(cantInp ? cantInp.value : cellTxt(2)) || 1;

      const precInp = tds[3]?.querySelector('input,select');
      const precio = toNumber(precInp ? precInp.value : cellTxt(3)) || 0;

      const idAttr = row.dataset.idProducto || row.dataset.id_producto || row.dataset.id || null;
      const id_producto =
        idAttr != null
          ? (String(idAttr).match(/^\d+$/) ? Number(idAttr) : idAttr)
          : (String(codigo).match(/^\d+$/) ? Number(codigo) : null);

      if (!codigo && !nombre) return;

      items.push(mapeaProducto({
        id_producto,
        codigo: codigo || null,
        descripcion: nombre || 'Producto',
        cantidad,
        precio
      }));
    });
    return items;
  }

  const leerCarrito = () => {
    // 1) CartUI
    try {
      if (window.CartUI?.getItems) {
        const arr = CartUI.getItems() || [];
        if (arr.length) return arr.map(mapeaProducto);
      }
    } catch (_) {}

    // 2) Cart.items
    try {
      if (Array.isArray(window.Cart?.items) && window.Cart.items.length) {
        return window.Cart.items.map(mapeaProducto);
      }
    } catch (_) {}

    // 3) window.carrito
    if (Array.isArray(window.carrito) && window.carrito.length) {
      return window.carrito.map(mapeaProducto);
    }

    // 4) DOM
    return leerCarritoDesdeTabla();
  };

  // ===== Paths / open =====
  const basePathJoin = (rel) => {
    // rel puede ser "imprimir_ticket.php?x=1"
    const norm = String(rel).replace(/^\/+/, '');

    // si ya está en php/ventas, usar directorio actual
    const path = window.location.pathname;
    const inVentas = /\/php\/ventas\/[^/]*$/.test(path) || /\/php\/ventas\//.test(path);

    if (inVentas) {
      const dir = path.replace(/[^/]*$/, ''); // quita filename
      return dir + norm;
    }

    // fallback: relativo al root
    return '/php/ventas/' + norm;
  };

  const openNew = (url) => {
    const w = window.open(url, '_blank', 'noopener');
    if (!w) {
      const a = document.createElement('a');
      a.href = url;
      a.target = '_blank';
      a.rel = 'noopener';
      document.body.appendChild(a);
      a.click();
      setTimeout(() => a.remove(), 300);
    }
  };

  const limpiarUI = () => {
    try {
      if (Array.isArray(window.carrito)) window.carrito.length = 0;
      if (window.CartUI?.clear) CartUI.clear();
      if (typeof window.limpiarCarritoUI === 'function') window.limpiarCarritoUI(); // tu helper
      window.renderizarCarrito?.();
    } catch (_) {}

    ['#input-guarani', '#input-real', '#input-dolar'].forEach((sel) => {
      const el = document.querySelector(sel);
      if (el) el.value = '';
    });

    (document.getElementById('codigo-barra') || document.querySelector('input[type=search]'))?.focus();
  };

  const toggleBotones = (disabled) => {
    ['btn-cobro-rapido', 'btn-cobro-rapido-ticket', 'btn-factura-legal'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.disabled = disabled;
    });
  };

  const readResponseSmart = async (res) => {
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    const raw = await res.text();

    // intenta JSON igual aunque content-type sea incorrecto
    try {
      const data = JSON.parse(raw);
      return { kind: 'json', data, raw };
    } catch (_) {
      // si decía JSON y falló, igual devolvemos raw
      return { kind: ct.includes('application/json') ? 'badjson' : 'text', data: null, raw };
    }
  };

  // ===== Guardar + imprimir =====
  let guardando = false;

  async function guardar(tipo) {
    if (guardando) return;
    guardando = true;
    toggleBotones(true);

    try {
      // 1) Ítems y total
      const items = leerCarrito();
      if (!items.length) throw new Error('El carrito está vacío.');

      const { total } = totales(items);

      // 2) Pagos (mix)
      const g = toNumber(document.querySelector('#input-guarani')?.value);
      const r = toNumber(document.querySelector('#input-real')?.value);
      const u = toNumber(document.querySelector('#input-dolar')?.value);

      const brGs  = Math.round((r || 0) * COTIZ.br);
      const usdGs = Math.round((u || 0) * COTIZ.usd);
      const recibidoGs = Math.max(0, Math.round(g || 0) + brGs + usdGs);
      const vuelto = Math.max(0, recibidoGs - total);

      const partes = [];
      if (g) partes.push(`₲ ${Math.round(g).toLocaleString('es-PY')}`);
      if (r) partes.push(`R$ ${Number(r).toLocaleString('es-PY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
      if (u) partes.push(`US$ ${Number(u).toLocaleString('es-PY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
      const recibidoTxt = partes.join(' + ') || '₲ 0';

      // 3) Cliente
      const id_per  = document.body.dataset.cliId || document.getElementById('cliente-id')?.value || null;
      const cli_nom = document.body.dataset.cliNombre || document.getElementById('cliente-nombre')?.value || 'CONSUMIDOR FINAL';
      const cli_doc = document.body.dataset.cliRuc || document.getElementById('cliente-ruc')?.value || '';
      const cli_dir = document.body.dataset.cliDir || document.getElementById('cliente-dir')?.value || '';

      // 4) Guardar
      // NOTA: envío ambos formatos: "items" y "carrito" para que tu PHP no se enoje
      const payload = {
        tipo: tipo || 'venta',
        id_per,
        cliente_nombre: cli_nom,
        cliente_doc: cli_doc,
        cliente_dir: cli_dir,
        cotiz: { br: COTIZ.br, usd: COTIZ.usd },
        pago: {
          moneda: 'mix',
          recibido: recibidoGs,
          vuelto,
          recibido_txt: recibidoTxt,
          detalle: { gs: Math.round(g || 0), br: r || 0, usd: u || 0, br_gs: brGs, usd_gs: usdGs }
        },
        total,
        items,          // formato recomendado
        carrito: items  // compat con tu PHP actual
      };

      const resp = await fetch('guardar_venta.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const parsed = await readResponseSmart(resp);

      if (parsed.kind !== 'json' || !parsed.data) {
        console.error('Respuesta cruda del servidor:', parsed.raw);
        throw new Error('Respuesta inválida del servidor (no es JSON). Revisá warnings/errores PHP.');
      }

      const json = parsed.data;

      if (!resp.ok || !json.ok) {
        throw new Error(json?.msg || json?.mensaje || `No se guardó la venta (HTTP ${resp.status})`);
      }

      const id_venta = json.id_venta || json.id || json.idVenta;
      if (!id_venta) throw new Error('El servidor no devolvió id_venta.');

      // 5) Imprimir (Ticket ≠ Factura)
      const ts = Date.now();

      if (tipo === 'ticket') {
        const qTicket = new URLSearchParams({
          id_venta: String(id_venta),
          id: String(id_venta),
          cliente_nombre: cli_nom || '',
          cliente_doc: cli_doc || '',
          cotiz_br: String(COTIZ.br),
          cotiz_usd: String(COTIZ.usd),
          recibido_txt: recibidoTxt,
          recibido: String(recibidoGs),
          vuelto: String(vuelto),
          ts: String(ts)
        });
        openNew(basePathJoin(`imprimir_ticket.php?${qTicket.toString()}`));
      }

      if (tipo === 'factura') {
        const qFac = new URLSearchParams({
          id_venta: String(id_venta),
          id: String(id_venta),
          razon: cli_nom || '',
          ruc: cli_doc || '',
          direccion: cli_dir || '',
          ts: String(ts)
        });
        openNew(basePathJoin(`${FACTURA_URL}?${qFac.toString()}`));
      }

      // 6) Limpiar
      limpiarUI();

    } catch (err) {
      console.error(err);
      alert('Error al guardar: ' + (err?.message || err));
    } finally {
      toggleBotones(false);
      guardando = false;
    }
  }

  // ===== API pública (sin pisar helpers) =====
  window.ventaFlow = window.ventaFlow || {};
  window.ventaFlow.guardar = guardar;

  // Compat opcional (pero con nombre distinto para no romper tu helper)
  window.guardarVentaFlow = guardar;

  // ===== Enlazar botones =====
  const safeBind = (id, tipo) => {
    const el = document.getElementById(id);
    if (!el || el.dataset.boundFlow === '1') return;
    el.dataset.boundFlow = '1';
    el.addEventListener('click', (e) => {
      e.preventDefault();
      guardar(tipo);
    });
  };

  window.addEventListener('DOMContentLoaded', () => {
    safeBind('btn-cobro-rapido', 'venta');        // guarda sin imprimir
    safeBind('btn-cobro-rapido-ticket', 'ticket');
    safeBind('btn-factura-legal', 'factura');     // factura legal

    // Atajo de teclado: F9 para Factura
    document.addEventListener('keydown', (e) => {
      if (e.key === 'F9') {
        e.preventDefault();
        guardar('factura');
      }
    });
  });
})();



/* =========================================================
   CLIENTE (Persona) — Enter en CI/RUC -> busca; si no existe abre modal; guarda opcionales
   Requiere:
     - #cliente-ruc, #cliente-nombre, #cliente-id
     - Modal: #modal-persona + inputs #mp-ruc #mp-nombre #mp-telefono #mp-email #mp-direccion + botón #mp-guardar
   Endpoints:
     - buscar_persona_bi.php  (POST ruc) => {success:true,data:{...}} o {success:false}
     - register_persona_bi.php (POST ruc,nombre,direccion,telefono,correo) => {success:true,data:{...}}
   ========================================================= */
(function(){
  'use strict';
  if (window.__personaClienteV1) return;
  window.__personaClienteV1 = true;

  const $  = (s,root=document)=> root.querySelector(s);
  const $$ = (s,root=document)=> Array.from(root.querySelectorAll(s));

  function normalizePersona(d){
    if (!d) return null;
    return {
      id: d.id_persona ?? d.id_per ?? d.id ?? null,
      ruc: d.ruc_ci ?? d.cedula_per ?? d.ruc ?? d.doc ?? '',
      nombre: d.nombre ?? d.nombre_per ?? '',
      direccion: d.direccion ?? '',
      telefono: d.telefono ?? '',
      correo: d.correo ?? ''
    };
  }

  function setClienteUI(p){
    const n = normalizePersona(p);
    if (!n) return;
    const rucEl = $('#cliente-ruc');
    const nomEl = $('#cliente-nombre');
    const idEl  = $('#cliente-id');
    if (rucEl){ rucEl.value = n.ruc || ''; rucEl.dataset.loadedDoc = n.ruc || ''; }
    if (nomEl) nomEl.value = n.nombre || '';
    if (idEl)  idEl.value  = n.id ?? '';
    // visor opcional (si existe)
    const visor = $('#cliente-visor');
    if (visor){
      visor.innerHTML = `
        <div class="badge">Cliente: <b>${escapeHtml(n.nombre || '—')}</b></div>
        <div class="badge">Doc: <b>${escapeHtml(n.ruc || '—')}</b></div>
      `;
    }
  }

  function escapeHtml(str){
    return String(str ?? '')
      .replaceAll('&','&amp;').replaceAll('<','&lt;')
      .replaceAll('>','&gt;').replaceAll('"','&quot;')
      .replaceAll("'","&#39;");
  }

  // ---- Modal helpers (app-forms.css) ----
  function getModal(){
    return $('#modal-persona');
  }
  function openModal(prefillDoc=''){
    const m = getModal();
    if (!m) return console.warn('[Persona] #modal-persona no existe en la página');
    m.classList.add('is-open');
    m.classList.remove('hidden'); // por si quedó de versiones viejas
    m.setAttribute('aria-hidden','false');

    const inDoc = $('#mp-ruc');
    const inNom = $('#mp-nombre');
    if (inDoc) inDoc.value = prefillDoc || '';
    if (inNom) inNom.value = '';
    $('#mp-telefono') && ($('#mp-telefono').value = '');
    $('#mp-email') && ($('#mp-email').value = '');
    $('#mp-direccion') && ($('#mp-direccion').value = '');

    setTimeout(()=> (inNom || inDoc)?.focus(), 0);
  }
  function closeModal(){
    const m = getModal();
    if (!m) return;
    m.classList.remove('is-open');
    m.setAttribute('aria-hidden','true');
    $('#cliente-nombre')?.focus();
  }

  function wireModalClose(){
    const m = getModal();
    if (!m) return;
    // botones [data-close]
    $$('[data-close]', m).forEach(btn=>{
      btn.addEventListener('click', (e)=>{ e.preventDefault(); closeModal(); });
    });
    // click backdrop
    m.addEventListener('click', (e)=>{
      if (e.target === m || e.target.classList.contains('modal-backdrop')) closeModal();
    });
    // ESC
    document.addEventListener('keydown', (e)=>{
      if (e.key === 'Escape' && m.classList.contains('is-open')) closeModal();
    });
  }

  async function postJson(url, dataObj){
    const fd = new FormData();
    Object.entries(dataObj).forEach(([k,v])=> fd.append(k, v ?? ''));
    const r = await fetch(url, { method:'POST', body: fd });
    const ct = r.headers.get('content-type') || '';
    const data = ct.includes('application/json') ? await r.json() : null;
    return { ok:r.ok, data, status:r.status };
  }

  async function buscarPersona(doc){
    const r = await postJson('buscar_persona_bi.php', { ruc: doc });
    if (!r.ok) return null;
    if (r.data && r.data.success && r.data.data) return normalizePersona(r.data.data);
    return null;
  }

  async function guardarPersonaDesdeModal(){
    const ruc = ($('#mp-ruc')?.value || '').trim();
    const nombre = ($('#mp-nombre')?.value || '').trim();
    const telefono = ($('#mp-telefono')?.value || '').trim();
    const correo = ($('#mp-email')?.value || '').trim();
    const direccion = ($('#mp-direccion')?.value || '').trim();

    if (!ruc || !nombre){
      alert('CI/RUC y Nombre son obligatorios.');
      return;
    }

    const r = await postJson('register_persona_bi.php', {
      ruc, nombre, direccion, telefono, correo
    });

    if (!(r.ok && r.data && r.data.success)){
      const msg = r.data?.message || 'No se pudo guardar la persona.';
      alert(msg);
      return;
    }

    const persona = normalizePersona(r.data.data);
    setClienteUI(persona);
    closeModal();
  }

  function wireEnterBuscar(){
    const inDoc = $('#cliente-ruc');
    if (!inDoc) return;

    let busy = false;
    const isEnter = (e)=> (e.key === 'Enter' || e.key === 'NumpadEnter' || e.keyCode === 13);

    async function run(){
      const doc = inDoc.value.trim();
      if (!doc || busy) return;
      busy = true;
      try{
        const persona = await buscarPersona(doc);
        if (persona) setClienteUI(persona);
        else openModal(doc);
      }catch(err){
        console.error(err);
        // si falla el fetch, igual dejemos abrir el modal para cargar manual
        openModal(doc);
      }finally{
        busy = false;
      }
    }

    // captura: que nadie te robe el Enter
    inDoc.addEventListener('keydown', (e)=>{
      if (!isEnter(e)) return;
      e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation();
      run();
    }, true);
  }

  function wireGuardar(){
    $('#mp-guardar')?.addEventListener('click', (e)=>{
      e.preventDefault();
      guardarPersonaDesdeModal();
    });

    // Enter dentro del modal -> guardar
    $('#modal-persona')?.addEventListener('keydown', (e)=>{
      if (e.key === 'Enter'){
        const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
        if (tag === 'textarea') return;
        e.preventDefault();
        guardarPersonaDesdeModal();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    // si el modal no está, no hacemos nada
    if (!getModal()) return;

    wireModalClose();
    wireEnterBuscar();
    wireGuardar();
  });
})();

