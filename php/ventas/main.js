// ===== Cotizaciones =====
let cotizaciones = {
  guarani: 1,
  real: 1250,
  dolar: 7200
};

let productosCache = [];     // cache de productos
let productosMap = new Map();// index por código
let precioSeleccionado = 'precio1_pro';

// Utils
const $id = (id) => document.getElementById(id);

function normCodigo(v) {
  return String(v ?? '')
    .trim()
    .replace(/\s+/g, '')
    .replace(/-/g, '');
}

function toInt(v, def = 0) {
  const n = parseInt(String(v ?? '').replace(/[^\d-]/g, ''), 10);
  return Number.isFinite(n) ? n : def;
}

function formatGs(n) {
  return new Intl.NumberFormat('es-PY', { maximumFractionDigits: 0 }).format(Number(n || 0));
}

// ===== Cargar cotizaciones desde PHP =====
async function cargarCotizaciones() {
  try {
    const res = await fetch('get_cotizaciones.php', { cache: 'no-store' });
    const data = await res.json();

    // Mezcla segura
    cotizaciones = { ...cotizaciones, ...data };

    if ($id('cotizacion-guarani')) $id('cotizacion-guarani').innerText = `₲ ${cotizaciones.guarani}`;
    if ($id('cotizacion-real'))    $id('cotizacion-real').innerText    = `R$ ${cotizaciones.real}`;
    if ($id('cotizacion-dolar'))   $id('cotizacion-dolar').innerText   = `US$ ${cotizaciones.dolar}`;
  } catch (err) {
    console.warn('No se pudieron cargar las cotizaciones:', err);
  }
}

// ===== Cargar productos UNA VEZ y cachear =====
async function cargarProductosCache() {
  if (productosCache.length) return;

  const res = await fetch('listar_productos.php', {
    headers: { 'Accept': 'application/json' },
    cache: 'no-store'
  });

  const response = await res.json();
  if (!response?.success) throw new Error(response?.error || 'Error al cargar productos');

  productosCache = Array.isArray(response.data) ? response.data : [];
  productosMap = new Map();

  for (const p of productosCache) {
    const code = normCodigo(p.codigo_barra_pro);
    if (code) productosMap.set(code, p);
  }
}

// ===== Buscar producto por código (con *cantidad*código) =====
async function buscarProducto() {
  const input = $id('codigo-barra');
  const entradaRaw = (input?.value || '').trim();
  if (!entradaRaw) return alert('Por favor, ingresa un código de barra.');

  let cantidad = 1;
  let codigo = entradaRaw;

  // formato: 3*123456789
  if (entradaRaw.includes('*')) {
    const [c, ...rest] = entradaRaw.split('*');
    cantidad = Math.max(1, toInt(c, 1));
    codigo = rest.join('*'); // por si el código tiene '*' raro (no debería, pero...)
  }

  codigo = normCodigo(codigo);
  if (!codigo) return alert('Código inválido.');

  try {
    await cargarProductosCache();

    const producto = productosMap.get(codigo);
    if (!producto) return alert('Producto no encontrado');

    // Enviamos el producto + precio seleccionado
    const precio = Number(producto?.[precioSeleccionado] ?? producto?.precio1_pro ?? 0) || 0;
    const prodConPrecio = { ...producto, precio, precio_unit: precio };

    if (typeof window.agregarAlCarrito === 'function') {
      window.agregarAlCarrito(prodConPrecio, cantidad);
    } else {
      alert(`Agregado: ${producto.nombre_pro} x${cantidad} a ₲ ${formatGs(precio)}`);
    }

    input.value = '';
    input.focus();
  } catch (err) {
    console.error('Error al buscar producto:', err);
    alert('Error al buscar producto. Revisá listar_productos.php / conexión.');
  }
}

// ===== UI: selección de precio =====
function initPrecioButtons() {
  const map = {
    btnPrecio1: 'precio1_pro',
    btnPrecio2: 'precio2_pro',
    btnPrecio3: 'precio3_pro'
  };

  document.querySelectorAll('.precio-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.precio-btn').forEach((b) => b.classList.remove('activo'));
      btn.classList.add('activo');

      const key = map[btn.id];
      if (key) precioSeleccionado = key;

      // opcional: exponerlo global para otros módulos
      window.precioSeleccionado = precioSeleccionado;

      console.log('Precio seleccionado:', precioSeleccionado);
    });
  });
}

// ===== Formatear inputs de precios (si existen) =====
function initFormateoInputsPrecios() {
  const ids = ['precio1_pro', 'precio2_pro', 'precio3_pro'];
  ids.forEach((id) => {
    const el = $id(id);
    if (!el) return;

    el.addEventListener('input', () => {
      // sólo números
      const valor = toInt(el.value, 0);
      el.value = formatGs(valor);
    });
  });
}

// ===== DOM Ready =====
document.addEventListener('DOMContentLoaded', async () => {
  // defaults global para compat
  window.precioSeleccionado = precioSeleccionado;

  cargarCotizaciones();
  if (typeof window.cargarFavoritos === 'function') window.cargarFavoritos();

  const input = $id('codigo-barra');
  if (input) {
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        buscarProducto();
      }
    });
  }

  initPrecioButtons();
  initFormateoInputsPrecios();

  // opcional: precargar productos para que el primer scan sea instantáneo
  try { await cargarProductosCache(); } catch (e) { /* no bloquea */ }
});
