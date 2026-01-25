// carrito_y_favorito.js — FINAL CORREGIDO (Grid + P1/P2/P3 “solo próximo ítem” + eventos)
(() => {
  'use strict';

  ///////////////////////////
  // Estado global único
  ///////////////////////////
  window.carrito = Array.isArray(window.carrito) ? window.carrito : [];
  const carrito = window.carrito;

  ///////////////////////////
  // Helpers
  ///////////////////////////
  const $  = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));

  const esc = (s) => String(s ?? '')
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#039;');

  function toNumber(v) {
    if (v == null) return 0;
    if (typeof v === 'number') return Number.isFinite(v) ? v : 0;

    let s = String(v).trim();
    if (!s) return 0;

    s = s.replace(/[^\d,.\-]/g, '');

    const lastComma = s.lastIndexOf(',');
    const lastDot = s.lastIndexOf('.');
    if (lastComma !== -1 && lastDot !== -1) {
      if (lastComma > lastDot) s = s.replace(/\./g,'').replace(',', '.');
      else s = s.replace(/,/g,'');
    } else if (lastComma !== -1) {
      s = s.replace(/\./g,'').replace(',', '.');
    } else {
      s = s.replace(/,/g,'');
    }

    const n = parseFloat(s);
    return Number.isFinite(n) ? n : 0;
  }

  function getCotizaciones() {
    const brTxt  = $('#cotizacion-real')?.textContent ?? '';
    const usdTxt = $('#cotizacion-dolar')?.textContent ?? '';
    return {
      real : Math.round(toNumber(brTxt))  || window.COT_BR  || 1450,
      dolar: Math.round(toNumber(usdTxt)) || window.COT_USD || 7600
    };
  }

  function formatearMoneda(valor, tipo) {
    const n = Number(valor) || 0;
    switch (tipo) {
      case 'guarani': return `₲ ${Math.round(n).toLocaleString('es-PY')}`;
      case 'real':    return `R$ ${n.toLocaleString('es-PY', { minimumFractionDigits:2, maximumFractionDigits:2 })}`;
      case 'dolar':   return `US$ ${n.toLocaleString('es-PY', { minimumFractionDigits:2, maximumFractionDigits:2 })}`;
      default:        return n.toLocaleString();
    }
  }

  ///////////////////////////
  // Resolver de precio (P1/P2/P3)
  ///////////////////////////
  function resolverPrecio(producto, nivel) {
    const nv = String(nivel || '').toLowerCase().trim();

    // acepta: p1/p2/p3 y precio1_pro/precio2_pro/precio3_pro
    const tier =
      (['precio1_pro','p1','precio1','precio_1'].includes(nv)) ? 'p1' :
      (['precio2_pro','p2','precio2','precio_2'].includes(nv)) ? 'p2' :
      (['precio3_pro','p3','precio3','precio_3'].includes(nv)) ? 'p3' : 'p1';

    const keysPorTier = {
      p1: ['precio1_pro','precio1','precio_1','p1','precio','precio_unit'],
      p2: ['precio2_pro','precio2','precio_2','p2'],
      p3: ['precio3_pro','precio3','precio_3','p3']
    };

    const pick = (k) => {
      const val = producto?.[k];
      const n = toNumber(val);
      return Number.isFinite(n) && n > 0 ? n : null;
    };

    for (const k of keysPorTier[tier]) {
      const n = pick(k);
      if (n != null) return { precio: n, key: k, tier };
    }
    // fallback p1
    for (const k of keysPorTier.p1) {
      const n = pick(k);
      if (n != null) return { precio: n, key: k, tier: 'p1' };
    }
    return { precio: 0, key: 'precio', tier: 'p1' };
  }

  ///////////////////////////
  // Render del carrito
  ///////////////////////////
  function renderizarCarrito() {
    const tbody =
      $(".carrito tbody") ||
      $('table[data-role="carrito"] tbody') ||
      $('#tbody-carrito');

    if (!tbody) return;

    tbody.innerHTML = "";
    let total = 0;

    carrito.forEach((producto, index) => {
      const cant = Math.max(1, Number(producto.cantidad) || 1);
      const precio = Number(producto.precio) || 0;
      const subtotal = cant * precio;
      total += subtotal;

      const codigo = producto.codigo ?? producto.codigo_barra_pro ?? producto.codigo_barra ?? '';
      const nombre = producto.nombre_pro ?? producto.descripcion ?? producto.nombre ?? 'Producto';

      const fila = document.createElement("tr");
      fila.dataset.index = String(index);

      fila.innerHTML = `
        <td>${esc(codigo)}</td>
        <td>${esc(nombre)}</td>
        <td class="text-center">
          <input
            type="number"
            class="cantidad-input"
            value="${esc(cant)}"
            data-index="${esc(index)}"
            min="1"
            step="1"
            style="width: 50px; text-align: center;"
          >
        </td>
        <td class="num">${esc(formatearMoneda(precio, 'guarani'))}</td>
        <td class="num">${esc(formatearMoneda(subtotal, 'guarani'))}</td>
        <td class="text-center">
          <button class="btn red btn-icon-only" type="button"
            style="width:30px;height:30px;font-size:0.8rem;"
            data-index="${esc(index)}" title="Quitar">🗑️</button>
        </td>
      `;

      // Eventos fila
      const inp = fila.querySelector('.cantidad-input');
      inp?.addEventListener('change', function () {
        const i = Number(this.dataset.index);
        const nuevaCantidad = Math.max(1, parseInt(this.value, 10) || 1);
        if (carrito[i]) carrito[i].cantidad = nuevaCantidad;
        renderizarCarrito();
      });

      fila.querySelector('button')?.addEventListener('click', (e) => {
        const i = Number(e.currentTarget.dataset.index);
        removerDelCarrito(i);
      });

      // ✅ Orden natural
      tbody.appendChild(fila);
    });

    // Totales y espejos
    const cot = getCotizaciones();
    const updateText = (sel, val) => {
      const el = $(sel);
      if (el) el.innerText = val;
    };

    ['#espejo-total-guarani', '#derecha-total-guarani'].forEach(s =>
      updateText(s, formatearMoneda(total, 'guarani'))
    );
    ['#espejo-total-real', '#derecha-total-real'].forEach(s =>
      updateText(s, formatearMoneda(total / (cot.real || 1), 'real'))
    );
    ['#espejo-total-dolar', '#derecha-total-dolar'].forEach(s =>
      updateText(s, formatearMoneda(total / (cot.dolar || 1), 'dolar'))
    );

    // ✅ Avisar a CartUI / otros scripts
    document.dispatchEvent(new CustomEvent('carrito:cambio', { detail: { subtotalGs: Math.round(total) } }));
  }

  ///////////////////////////
  // Mutadores
  ///////////////////////////
  function removerDelCarrito(index) {
    if (index >= 0 && index < carrito.length) {
      carrito.splice(index, 1);
      renderizarCarrito();
    }
  }

  function agregarAlCarrito(producto, cantidad = 1) {
    // precioSeleccionado puede venir como p1/p2/p3 o precioX_pro
    if (typeof window.precioSeleccionado !== 'string') window.precioSeleccionado = 'p1';

    const { precio, key, tier } = resolverPrecio(producto, window.precioSeleccionado);
    const codigoPrincipal =
      producto.codigo_barra_pro ??
      producto.codigo_barra ??
      producto.codigo ??
      producto.barcode ??
      '';

    carrito.push({
      ...producto,
      id_pro: producto.id_pro ?? producto.id_producto ?? producto.id ?? null,
      codigo: codigoPrincipal,
      cantidad: Math.max(1, Number(cantidad) || 1),
      precio: Number(precio) || 0,
      precio_unit: Number(precio) || 0,
      _precio_nivel: key,
      _tier: tier
    });

    renderizarCarrito();

    // Reset a Precio 1 visual y lógico (solo afectaba al “próximo ítem”)
    window.precioSeleccionado = 'p1';
    ['btnPrecio1', 'btnPrecio2', 'btnPrecio3'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.toggle('activo', id === 'btnPrecio1');
    });

    // Foco vuelve al input
    document.getElementById('codigo-barra')?.focus();
  }

  // Exponer funciones si otros módulos las usan
  window.renderizarCarrito = renderizarCarrito;
  window.agregarAlCarrito = agregarAlCarrito;
  window.removerDelCarrito = removerDelCarrito;

  ///////////////////////////
  // Favoritos (CSS GRID)
  ///////////////////////////
  async function cargarFavoritos() {
    try {
      const resp = await fetch('listar_productos.php', {
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
      });
      const data = await resp.json();

      if (!data || data.success === false || !Array.isArray(data.data)) {
        console.error("Error al cargar productos:", data);
        return;
      }

      const panel = document.getElementById("panelFavoritos");
      if (!panel) return;

      panel.innerHTML = '';

      data.data.forEach(producto => {
        const esFavorito = (producto.favorito === true || producto.favorito === 1 || producto.favorito === "1");
        if (!esFavorito) return;

        const btn = document.createElement('button');
        btn.className = 'product-btn';
        btn.type = 'button';

        const nombre = String(producto.nombre_pro || 'PRODUCTO').trim();
        btn.title = nombre;

        const imgFile = producto.imagen_pro ? String(producto.imagen_pro).trim() : '';
        const tieneImagen = imgFile.length > 0;

        // ✅ Render: con imagen o con texto centrado
        const renderTextoCentrado = () => {
          btn.classList.add('no-img');
          btn.innerHTML = `<div class="fav-text">${esc(nombre)}</div>`;
        };

        if (tieneImagen) {
          const imgPath = `../img/productos/${imgFile}`;

          btn.innerHTML = `
            <img src="${esc(imgPath)}" alt="${esc(nombre)}">
            <span>${esc(nombre)}</span>
          `;

          // Si la imagen falla -> mostrar nombre completo centrado
          const img = btn.querySelector('img');
          img?.addEventListener('error', () => {
            renderTextoCentrado();
          });
        } else {
          renderTextoCentrado();
        }

        btn.addEventListener("click", () => {
          const cantidad = obtenerCantidadDesdeInput();
          agregarAlCarrito(producto, cantidad);
          mostrarToast(`Agregado: ${producto.nombre_pro}`);
        });

        panel.appendChild(btn);
      });

    } catch (error) {
      console.error("Error cargando favoritos:", error);
    }
  }

  ///////////////////////////
  // Aux UI
  ///////////////////////////
  function obtenerCantidadDesdeInput() {
    const input = document.getElementById("codigo-barra");
    const entrada = input?.value.trim() || "";
    let cantidad = 1;

    if (entrada.includes("*")) {
      const partes = entrada.split("*");
      const parsed = parseInt(partes[0], 10);
      if (Number.isFinite(parsed) && parsed > 0) cantidad = parsed;
    }

    // No limpiar si el usuario está escribiendo código real sin *
    if (entrada.includes("*") && input) input.value = "";

    return cantidad;
  }

  function mostrarToast(mensaje) {
    let toast = document.getElementById("toast");
    if (!toast) {
      toast = document.createElement("div");
      toast.id = "toast";
      toast.style.cssText =
        "position:fixed; bottom:10px; left:50%; transform:translateX(-50%); background:#333; color:#fff; padding:10px 20px; border-radius:20px; opacity:0; transition:opacity 0.3s; z-index:9999;";
      document.body.appendChild(toast);
    }

    toast.innerText = mensaje;
    toast.style.opacity = "1";
    toast.style.bottom = "40px";
    setTimeout(() => {
      toast.style.opacity = "0";
      toast.style.bottom = "10px";
    }, 1600);
  }

  ///////////////////////////
  // Limpieza post-venta
  ///////////////////////////
  function limpiarCamposPostVenta() {
    carrito.length = 0;
    renderizarCarrito();

    ['#codigo-barra', '#input-guarani', '#input-real', '#input-dolar'].forEach(sel => {
      const el = document.querySelector(sel);
      if (el) el.value = '';
    });

    // reset “restante/vuelto” (ids que venís usando)
    ['#restante-guarani', '#restante-real', '#restante-dolar'].forEach(sel => {
      const el = document.querySelector(sel);
      if (el) el.innerText = (sel.includes('guarani') ? '₲ 0' : sel.includes('real') ? 'R$ 0' : 'US$ 0');
    });

    document.dispatchEvent(new CustomEvent('carrito:cambio', { detail: { subtotalGs: 0 } }));

    setTimeout(() => document.getElementById('codigo-barra')?.focus(), 100);
  }

  // Evento global para limpiar desde otros scripts
  window.addEventListener('venta:ok', limpiarCamposPostVenta);

  ///////////////////////////
  // Selectores de Precio
  ///////////////////////////
  document.addEventListener('DOMContentLoaded', () => {
    ['btnPrecio1', 'btnPrecio2', 'btnPrecio3'].forEach((id, idx) => {
      const btn = document.getElementById(id);
      if (!btn) return;

      btn.addEventListener('click', () => {
        ['btnPrecio1', 'btnPrecio2', 'btnPrecio3'].forEach(i => document.getElementById(i)?.classList.remove('activo'));
        btn.classList.add('activo');
        window.precioSeleccionado = `p${idx + 1}`;
        document.getElementById('codigo-barra')?.focus();
      });
    });

    renderizarCarrito();
    cargarFavoritos();
  });

})();
