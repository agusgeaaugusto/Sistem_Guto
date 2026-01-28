<?php
// ventas.php - Carvallo Bodega (pantalla de ventas) - REESCRITO / ESTABLE
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Carvallo Bodega - Pantalla de Ventas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="venta.css?v=20260103-1">
  <link rel="stylesheet" href="../css/app-forms.css?v=20260103-1">
</head>

<body class="modern-ui shell-embed">

  <header class="appbar">
    <div class="brand">CARVALLO BODEGA</div>
    <nav class="top-actions">
      <button type="button" class="btn ghost">Inicio</button>
      <button type="button" class="btn ghost active">Ventas</button>
      <button type="button" class="btn danger-outline">Salir</button>
    </nav>
  </header>

  <main class="container">
    <div class="container-pantalla">

      <!-- IZQUIERDA -->
      <section class="left-panel card" aria-label="Datos y carrito">

       
        <!-- Cliente -->
        <div class="cliente-info">
          <label for="cliente-ruc">RUC / C.I.:</label>
          <input type="text" id="cliente-ruc" placeholder="CI o RUC" />
          <label for="cliente-nombre">Nombre:</label>
          <input type="text" id="cliente-nombre" placeholder="Nombre del cliente" />
          <input type="hidden" id="cliente-id" name="cliente_id" />
        </div>

        <div id="cliente-visor" class="cliente-visor mb-8" aria-live="polite">
         
        </div>


        <!-- MODAL REGISTRAR PERSONA -->
<div class="modal" id="modal-persona" aria-hidden="true">
  <div class="modal-backdrop" data-close></div>

  <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="mp-title">
    <div class="modal-head">
      <h3 id="mp-title">Registrar Persona</h3>
      <button type="button" class="btn ghost" data-close>✕</button>
    </div>

    <div class="modal-body">
      <label>CI / RUC</label>
      <input type="text" id="mp-ruc" placeholder="CI o RUC">

      <label>Nombre</label>
      <input type="text" id="mp-nombre" placeholder="Nombre completo">
    </div>

    <div class="modal-foot">
      <button type="button" class="btn" id="mp-guardar">Guardar</button>
      <button type="button" class="btn danger-outline" data-close>Cancelar</button>
    </div>
  </div>
</div>


        <div class="contenedor-totales-cotizaciones">
          <!-- TOTAL -->
          <div class="total card-kpi">
            <h3>TOTAL</h3>
            <div class="fila-monedas">
              <div class="moneda grande">
                <img src="../img/productos/guarani.png" class="icono-moneda" alt="Gs">
                <strong id="espejo-total-guarani">₲ 0</strong>
              </div>
              <div class="moneda">
                <img src="../img/productos/real.png" class="icono-moneda" alt="R$">
                <strong id="espejo-total-real">R$ 0,00</strong>
              </div>
              <div class="moneda">
                <img src="../img/productos/dolar.png" class="icono-moneda" alt="US$">
                <strong id="espejo-total-dolar">US$ 0,00</strong>
              </div>
            </div>
          </div>

          <!-- COTIZACIÓN -->
          <div class="cotizacion card-kpi">
            <h3>COTIZACIÓN</h3>
            <div class="fila-monedas">
              <div class="moneda">
                <img src="../img/productos/guarani.png" class="icono-moneda" alt="Gs">
                <strong id="cotizacion-guarani">₲ 0</strong>
              </div>
              <div class="moneda">
                <img src="../img/productos/real.png" class="icono-moneda" alt="R$">
                <strong id="cotizacion-real">R$ 0</strong>
              </div>
              <div class="moneda">
                <img src="../img/productos/dolar.png" class="icono-moneda" alt="US$">
                <strong id="cotizacion-dolar">US$ 0</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="factura">
          <table class="carrito" data-role="carrito">
            <thead>
              <tr>
                <th>Código</th>
                <th style="min-width:140px;">Producto</th>
                <th class="text-center" style="width:60px;">Cant.</th>
                <th class="num">Precio Unit.</th>
                <th class="num">Subtotal</th>
                <th style="width:40px;"></th>
              </tr>
            </thead>
            <tbody id="tbody-carrito"></tbody>
          </table>
        </div>

        <div class="barra-accion-unificada">

          <!-- PRECIOS (✅ active real) -->
          <div class="grupo-precios-mini">
            <button type="button" id="btnPrecio1" class="precio-btn active">P1</button>
            <button type="button" id="btnPrecio2" class="precio-btn">P2</button>
            <button type="button" id="btnPrecio3" class="precio-btn">P3</button>
          </div>

          <!-- CÓDIGO DE BARRA -->
          <div class="input-grupo-compacto">
            <input type="text" id="codigo-barra" placeholder="Cód. Barra">
          </div>

          <!-- PRODUCTOS -->
          <button type="button" id="btn-abrir-lista" class="btn accent rippleable">
            <img src="../img/productos/carritocompras.png" class="btn-ico" alt="">
            <span>Productos</span>
          </button>

          <!-- COBRAR -->
          <button type="button" id="btn-pagar-vender" class="btn green compact-btn rippleable">
            <img src="../img/productos/billete.png" class="btn-ico" alt="">
            <span>Cobrar</span>
          </button>
        </div>

        <!-- Modal productos (fuera de la barra, más limpio) -->
        <div id="modal-productos" class="modal hidden" aria-hidden="true" role="dialog" aria-modal="true">
          <div class="modal-content mp-content" role="document">
            <div class="mp-header">
              <h3>Lista de productos</h3>
              <button type="button" id="mp-cerrar" aria-label="Cerrar">×</button>
            </div>

            <div class="mp-tools">
              <input type="text" id="mp-buscar" placeholder="Buscar por código o nombre" autocomplete="off" />
            </div>

            <div class="mp-table-wrap">
              <table class="mp-table" id="mp-tabla">
                <thead>
                  <tr>
                    <th style="width:120px">Código</th>
                    <th>Producto</th>
                    <th style="width:220px; text-align:right">Precio</th>
                    <th style="width:90px; text-align:right">Stock</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="mp-foot" style="padding:0 12px 12px; color:#9aa4b2;">
              <small>Tip: escribí para filtrar. Enter agrega el seleccionado. ESC cierra.</small>
            </div>
          </div>
        </div>

      </section>

      <!-- DERECHA -->
     
      <aside class="right-panel card" aria-label="Favoritos y cobro">
        <div class="resize-handle" aria-hidden="true"></div>

        <div class="rp-tabs">
          <button type="button" class="rp-tab active rippleable" data-target="panel-accesos">Acceso Rápido</button>
          <button type="button" class="rp-tab rippleable" data-target="panel-finalizar" id="tab-finalizar">Finalizar Venta</button>
        </div>

        <div class="rp-content">
          <section id="panel-finalizar" hidden>

            <div class="resumen-pago resumen-monedas">
              <div class="resumen-item">
                <div class="resumen-head">
                  <img src="../img/productos/guarani.png" alt="Gs" class="resumen-icon">
                  <span class="resumen-simbolo">₲</span><div class="resumen-valor" id="resta-guarani">0</div>
                </div>
                
                
              </div>

              <div class="resumen-item">
                <div class="resumen-head">
                  <img src="../img/productos/real.png" alt="R$" class="resumen-icon">
                  <span class="resumen-simbolo">R$</span><div class="resumen-valor" id="resta-real">0,00</div>
                </div>
                
                
              </div>

              <div class="resumen-item">
                <div class="resumen-head">
                  <img src="../img/productos/dolar.png" alt="US$" class="resumen-icon">
                  <span class="resumen-simbolo">US$</span><div class="resumen-valor" id="resta-dolar">0,00</div>
                </div>
                
                
              </div>
            </div>

            <div class="pagos-inline">
              <div class="pago-inline">
                <div class="moneda">
               
                  <span class="pago-simbolo">₲</span>
                </div>
                <input type="number" id="input-guarani" inputmode="numeric" step="1" min="0" placeholder="0">
              </div>

              <div class="pago-inline">
                <div class="moneda">
                 
                  <span class="pago-simbolo">R$</span>
                </div>
                <input type="number" id="input-real" inputmode="decimal" step="0.01" min="0" placeholder="0.00">
              </div>

              <div class="pago-inline">
                <div class="moneda">
                 
                  <span class="pago-simbolo">US$</span>
                </div>
                <input type="number" id="input-dolar" inputmode="decimal" step="0.01" min="0" placeholder="0.00">
              </div>
            </div>

            <div class="teclado-y-cobro">
              <div class="lado-derecho">
                <div class="teclado" id="teclado">
                  <button type="button" data-key="1">1</button><button type="button" data-key="2">2</button><button type="button" data-key="3">3</button>
                  <button type="button" data-key="4">4</button><button type="button" data-key="5">5</button><button type="button" data-key="6">6</button>
                  <button type="button" data-key="7">7</button><button type="button" data-key="8">8</button><button type="button" data-key="9">9</button>
                  <button type="button" data-key="0">0</button><button type="button" data-key=".">.</button><button type="button" data-key="BACK">←</button>
                </div>
                
              </div>

              <div class="botones-cobro">
                <button type="button" class="btn-cuadrado rippleable" id="btn-cobro-rapido">Cobro Rápido</button>
                <button type="button" class="btn-cuadrado rippleable" id="btn-cobro-rapido-ticket"onclick="imprimirTicket(<?= (int)$id_venta ?>).catch(()=>{})">Imprimir Ticket</button>
                <button type="button" id="btn-factura-legal" class="btn-cuadrado rippleable" title="Imprimir Factura (F9)"onclick="imprimirFactura(<?= (int)$id_venta ?>).catch(()=>{})">Imprimir Factura</button>

                
                <button type="button" class="btn" style="height:48px; background:#3a3a3a; color:#fff;">Otros Pagos</button>
              </div>
            </div>

          </section>

          <section id="panel-accesos">
            <h3 style="margin:2px 0 8px;">Acceso Rápido</h3>
            <div class="product-grid" id="panelFavoritos" aria-live="polite"></div>
          </section>
        </div>
      </aside>

    </div>
  </main>

  <!-- Scripts externos (los tuyos) -->
  <script src="precio_selector.js"></script>
  <script src="main.js"></script>
  <script src="carrito_y_favorito.js"></script>
  <script src="pagos.js"></script>
  <script src="calculadora.js"></script>
  <script src="cliente.js"></script>
  <script src="productos-modal.js?v=20260103-2"></script>
  <script src="venta_flow.js?v=20251028-7"></script>
<script src="impresion.js"></script>



  <script>
    // =========================
    // Tabs (estable)
    // =========================
    document.querySelectorAll('.rp-tab').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.rp-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.querySelectorAll('.rp-content section').forEach(sec => sec.hidden = true);
        const target = document.getElementById(btn.dataset.target);
        if (target) target.hidden = false;
      });
    });

    // =========================
    // Modal Productos (abre/cierra SIEMPRE)
    // =========================
    (function(){
      const modal = document.getElementById('modal-productos');
      const openBtn = document.getElementById('btn-abrir-lista');
      const closeBtn = document.getElementById('mp-cerrar');
      const buscar = document.getElementById('mp-buscar');

      function openModal(){
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden','false');
        // foco al buscar
        setTimeout(() => buscar?.focus(), 0);
      }
      function closeModal(){
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden','true');
        openBtn?.focus();
      }

      openBtn?.addEventListener('click', openModal);
      closeBtn?.addEventListener('click', closeModal);

      // Click fuera (backdrop)
      modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
      });

      // ESC
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
      });
    })();

    // =========================
    // Ripple (solo en .rippleable) — NO teclado
    // =========================
    (function(){
      document.addEventListener('click', (ev) => {
        const target = ev.target.closest('.rippleable');
        if (!target) return;

        target.classList.add('ripple-host');

        const rect = target.getBoundingClientRect();
        const ripple = document.createElement('span');
        const size = Math.max(rect.width, rect.height);

        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (ev.clientX - rect.left - size / 2) + 'px';
        ripple.style.top  = (ev.clientY - rect.top  - size / 2) + 'px';

        target.appendChild(ripple);
        ripple.addEventListener('animationend', () => ripple.remove(), { once:true });
      }, { capture:true });
    })();
  </script>




<script>
/* =========================================================
   RESIZE RIGHT PANEL — mouse + touch
   ========================================================= */
(function(){
  const handle = document.querySelector('.resize-handle');
  const rightPanel = document.querySelector('.right-panel');
  const container = document.querySelector('.container-pantalla');

  if (!handle || !rightPanel || !container) return;

  let startX = 0;
  let startWidth = 0;

  const startResize = (e) => {
    e.preventDefault();
    handle.classList.add('active');
    document.body.classList.add('resizing');

    startX = e.touches ? e.touches[0].clientX : e.clientX;
    startWidth = rightPanel.offsetWidth;

    document.addEventListener('mousemove', resize);
    document.addEventListener('mouseup', stopResize);
    document.addEventListener('touchmove', resize, { passive:false });
    document.addEventListener('touchend', stopResize);
  };

  const resize = (e) => {
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const dx = startX - clientX; // arrastrar hacia la izquierda agranda
    const newWidth = startWidth + dx;

    const min = 280;
    const max = window.innerWidth * 0.55;

    if (newWidth >= min && newWidth <= max) {
      rightPanel.style.width = newWidth + 'px';
      rightPanel.style.flex = '0 0 auto';
    }
  };

  const stopResize = () => {
    handle.classList.remove('active');
    document.body.classList.remove('resizing');

    document.removeEventListener('mousemove', resize);
    document.removeEventListener('mouseup', stopResize);
    document.removeEventListener('touchmove', resize);
    document.removeEventListener('touchend', stopResize);
  };

  handle.addEventListener('mousedown', startResize);
  handle.addEventListener('touchstart', startResize, { passive:false });
})();
</script>



<script>
/* =========================================================
   MODAL PERSONA (cuando no existe el cliente)
   - abrirModalPersona(rucOpcional)
   - cerrarModalPersona()
   ========================================================= */
(function(){
  const modal = document.getElementById('modal-persona');
  if(!modal) return;

  const rucEl = document.getElementById('mp-ruc');
  const nomEl = document.getElementById('mp-nombre');

  let lastFocus = null;

  window.abrirModalPersona = function(ruc=''){
    lastFocus = document.activeElement;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden','false');
    document.body.classList.add('modal-open');

    if(rucEl && ruc){
      rucEl.value = String(ruc).trim();
    }

    // foco: si ya hay ruc, saltar al nombre
    setTimeout(() => {
      if(!rucEl || !nomEl) return;
      (rucEl.value ? nomEl : rucEl).focus();
      (rucEl.value ? nomEl : rucEl).select?.();
    }, 0);
  };

  window.cerrarModalPersona = function(){
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden','true');
    document.body.classList.remove('modal-open');

    const fallback = document.getElementById('cliente-ruc');
    setTimeout(() => (lastFocus || fallback)?.focus(), 0);
  };

  // cerrar por backdrop / botones con data-close
  modal.addEventListener('click', (e) => {
    const closeHit = e.target.closest?.('[data-close]');
    if(closeHit) window.cerrarModalPersona();
  });

  // ESC
  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape' && modal.classList.contains('open')){
      window.cerrarModalPersona();
    }
  });
})();
</script>

</body>
</html>
