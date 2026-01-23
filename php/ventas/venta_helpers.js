// === venta_helpers.js (corregido y robusto) ===

// Helpers DOM
function $(sel, ctx = document) { return ctx.querySelector(sel); }
function $all(sel, ctx = document) { return Array.from(ctx.querySelectorAll(sel)); }

// Parse numérico tolerante a "1.234,56" / "1,234.56" / "Gs. 12.000"
function parseNumber(txt, def = 0) {
  if (txt == null) return def;
  if (typeof txt === 'number') return Number.isFinite(txt) ? txt : def;

  let s = String(txt).trim();
  if (!s) return def;

  // Quitar símbolos y espacios raros
  s = s.replace(/\s+/g, '').replace(/[^\d.,-]/g, '');

  // Heurística:
  // - si tiene coma y punto, asumimos el último separador como decimal
  // - si tiene solo coma, coma decimal
  // - si tiene solo punto, punto decimal
  const lastComma = s.lastIndexOf(',');
  const lastDot = s.lastIndexOf('.');

  if (lastComma !== -1 && lastDot !== -1) {
    // el separador decimal es el que aparece más a la derecha
    if (lastComma > lastDot) {
      // coma decimal, puntos miles
      s = s.replace(/\./g, '').replace(',', '.');
    } else {
      // punto decimal, comas miles
      s = s.replace(/,/g, '');
    }
  } else if (lastComma !== -1) {
    // solo coma: coma decimal, puntos miles si existen
    s = s.replace(/\./g, '').replace(',', '.');
  } else {
    // solo punto o ninguno: quitar comas miles
    s = s.replace(/,/g, '');
  }

  const n = parseFloat(s);
  return Number.isFinite(n) ? n : def;
}

function getCarritoTable() {
  return (
    $('#tabla-carrito') ||
    $('#tabla-venta') ||
    $('.tabla-carrito') ||
    $('.tabla-venta') ||
    $('table#venta-items') ||
    $('table#carrito')
  );
}

function parseItemsFromDOM() {
  const table = getCarritoTable();
  if (!table) return null;

  const rows = $all('tbody tr', table);
  const items = [];

  for (const tr of rows) {
    const id = parseInt(
      tr.dataset.id_pro ||
      tr.dataset.id ||
      tr.getAttribute('data-id') ||
      tr.dataset.id_producto ||
      0,
      10
    );

    const nombre =
      tr.dataset.nombre ||
      $('.nombre', tr)?.textContent?.trim() ||
      tr.querySelector('td:nth-child(2)')?.textContent?.trim() ||
      '';

    // Cantidad
    const iCant =
      $('.cant, input.cant, input[name="cantidad"]', tr) ||
      tr.querySelector('td:nth-child(3) input');
    const cant = parseNumber(iCant?.value ?? iCant?.textContent ?? 1, 1);

    // Precio unitario
    const precioEl =
      $('.precio, input.precio', tr) ||
      tr.querySelector('td:nth-child(4) input') ||
      tr.querySelector('td:nth-child(4)');
    const precio = parseNumber(precioEl?.value ?? precioEl?.textContent ?? 0, 0);

    if (cant > 0 && precio >= 0) {
      const subtotal = +(cant * precio).toFixed(2);
      items.push({
        id_pro: id,
        nombre_pro: nombre,
        cantidad: cant,
        precio_unit: precio,
        subtotal
      });
    }
  }

  return items.length ? items : null;
}

function normalizarItems(items) {
  return (items || [])
    .map((it) => {
      const id_pro = parseInt(it.id_pro ?? it.id_producto ?? it.id ?? 0, 10);
      const cantidad = parseNumber(it.cantidad ?? it.cant ?? 0, 0);
      const precio_unit = parseNumber(it.precio_unit ?? it.precio ?? 0, 0);
      const nombre_pro = (it.nombre_pro ?? it.descripcion ?? it.nombre ?? '').trim();

      const subtotal =
        it.subtotal != null
          ? parseNumber(it.subtotal, 0)
          : +(cantidad * precio_unit).toFixed(2);

      return { id_pro, nombre_pro, cantidad, precio_unit, subtotal };
    })
    .filter((it) => it.cantidad > 0 && (it.id_pro > 0 || it.nombre_pro));
}

// Carrito global (compatibilidad)
window.carrito = Array.isArray(window.carrito) ? window.carrito : [];

// Hooks opcionales existentes en tu sistema
function renderCarrito() {}
function actualizarTotales(total = 0, desc = 0, vuelto = 0) {}

function limpiarCarritoUI() {
  try {
    window.carrito = [];
    localStorage.removeItem('carrito');
  } catch (e) {}

  const map = {
    'inp-recibido': '',
    'inp-vuelto': '',
    'inp-descuento': '',
    'inp-total': '0',
    'inp-observacion': ''
  };

  Object.entries(map).forEach(([id, value]) => {
    const el = document.getElementById(id);
    if (el) el.value = value;
  });

  const table = getCarritoTable();
  if (table) {
    const tb = table.tBodies && table.tBodies[0];
    if (tb) tb.innerHTML = '';
  }

  try { actualizarTotales(0, 0, 0); } catch (e) {}
  try { if (typeof renderCarrito === 'function') renderCarrito(); } catch (e) {}
}

function buildPayloadFromUI() {
  const domItems = parseItemsFromDOM();
  const items = normalizarItems(domItems || window.carrito);

  const read = (id, def = 0) => parseNumber(document.getElementById(id)?.value ?? def, def);

  const descuento = read('inp-descuento', 0);
  const recibido = read('inp-recibido', 0);
  const vuelto = read('inp-vuelto', 0);

  // Total: si el input falla o está vacío, recalculamos
  let total = read('inp-total', NaN);
  if (!Number.isFinite(total)) {
    total = items.reduce((acc, it) => acc + parseNumber(it.subtotal, 0), 0);
  }

  return {
    items,
    descuento,
    total,
    pago: { recibido, vuelto },
    observacion: document.getElementById('inp-observacion')?.value || '',
    estado_venta: 'CER'
  };
}

// Lee respuesta del servidor de forma segura (JSON o texto)
async function readResponseSmart(res) {
  const ct = (res.headers.get('content-type') || '').toLowerCase();
  const text = await res.text();

  if (ct.includes('application/json')) {
    try { return { kind: 'json', data: JSON.parse(text), raw: text }; }
    catch { return { kind: 'badjson', data: null, raw: text }; }
  }

  // A veces PHP devuelve JSON con content-type mal
  try { return { kind: 'json', data: JSON.parse(text), raw: text }; }
  catch { return { kind: 'text', data: null, raw: text }; }
}

async function guardarVenta(payloadOpt) {
  const payload = payloadOpt || buildPayloadFromUI();

  // snapshot para preview (evitar mutaciones)
  const snapshot = JSON.parse(JSON.stringify(payload));

  if (!payload.items || payload.items.length === 0) {
    throw new Error('Carrito vacío. No hay nada que guardar.');
  }

  const res = await fetch('guardar_venta.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });

  const parsed = await readResponseSmart(res);

  // Si no vino JSON válido, mostramos crudo (esto te salva cuando PHP rompe)
  if (parsed.kind !== 'json' || !parsed.data) {
    console.error('Respuesta cruda del servidor:', parsed.raw);
    throw new Error('Respuesta inválida del servidor (no es JSON). Revisá errores PHP/Warning/HTML.');
  }

  const json = parsed.data;

  if (!res.ok || !json.ok) {
    const msg = json?.msg || `Error al guardar (HTTP ${res.status})`;
    throw new Error(msg);
  }

  // OK
  limpiarCarritoUI();

  // Confirmación preview
  let desea = false;

  if (typeof Swal !== 'undefined' && Swal?.fire) {
    const r = await Swal.fire({
      title: 'Venta guardada',
      text: `Factura #${json.nrofactura}. ¿Previsualizar?`,
      icon: 'success',
      showCancelButton: true,
      confirmButtonText: 'Sí',
      cancelButtonText: 'No'
    });
    desea = !!r.isConfirmed;
  } else {
    desea = confirm(`Venta OK. Factura #${json.nrofactura}. ¿Previsualizar ahora?`);
  }

  if (desea && typeof abrirPreviewFactura === 'function') {
    abrirPreviewFactura({
      id_venta: json.id_venta,
      nrofactura: json.nrofactura,
      total: json.total,
      vuelto: json.vuelto,
      recibido: snapshot?.pago?.recibido ?? null,
      descuento: snapshot?.descuento ?? 0,
      observacion: snapshot?.observacion ?? '',
      estado_venta: snapshot?.estado_venta ?? 'CER',
      id_per: snapshot?.id_per ?? '',
      id_usuario: snapshot?.id_usuario ?? '',
      items: snapshot.items || []
    });
  }

  return json;
}

// Export (si usás módulos)
export { buildPayloadFromUI, guardarVenta, limpiarCarritoUI };

// Compatibilidad (si NO usás módulos)
try {
  window.buildPayloadFromUI = buildPayloadFromUI;
  window.guardarVenta = guardarVenta;
  window.limpiarCarritoUI = limpiarCarritoUI;
} catch (e) {}
