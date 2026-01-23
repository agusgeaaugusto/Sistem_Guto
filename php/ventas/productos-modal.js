(() => {
  const $  = (s, c=document)=> c.querySelector(s);
  const $$ = (s, c=document)=> Array.from(c.querySelectorAll(s));

  const MODAL_ID = 'modal-productos';
  const MODAL    = '#modal-productos';
  const T_BODY   = '#mp-tabla tbody';

  let productos = [];
  let filtrados = [];
  let loaded = false;
  let selIndex = -1;

  const precioKey = ()=> (window.precioSeleccionado || 'precio1_pro');
  const num = (v)=> Number(v ?? 0);
  const fmtGs = (n)=> '₲ ' + Number(n||0).toLocaleString('es-PY', { maximumFractionDigits: 0 });

  const esc = (s)=> String(s ?? '')
    .replaceAll('&','&amp;')
    .replaceAll('<','&lt;')
    .replaceAll('>','&gt;')
    .replaceAll('"','&quot;')
    .replaceAll("'","&#039;");

  function tbodyMsg(html){
    const tbody = $(T_BODY);
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="4" style="padding:14px; color:#c8d1dc;">${html}</td></tr>`;
  }

  function closeModal(){
    const m = document.getElementById(MODAL_ID);
    if (!m) return;
    m.classList.add('hidden');
    m.classList.remove('is-open');
    m.style.display = 'none';
    m.setAttribute('aria-hidden','true');
    document.body.classList.remove('modal-open');
  }

  function openModal(){
    const m = document.getElementById(MODAL_ID);
    if (!m) return;

    // Tu CSS usa flex; mantenemos flex para centrar perfecto
    m.classList.remove('hidden');
    m.classList.add('is-open');
    m.style.display = 'flex';
    m.setAttribute('aria-hidden','false');
    document.body.classList.add('modal-open');

    setTimeout(()=> document.getElementById('mp-buscar')?.focus(), 0);
  }

  window.openModalProductos = openModal;

  async function ensureData(){
    if (loaded) return;

    // 👇 Abrimos el modal ANTES de cargar para que "siempre abra"
    tbodyMsg('Cargando productos…');

    const resp = await fetch('listar_productos.php', {
      headers: { 'Accept':'application/json' },
      cache: 'no-store'
    });

    const text = await resp.text();
    let js = null;
    try { js = JSON.parse(text); } catch(_){}

    if (!resp.ok || !js?.success) {
      console.error('listar_productos.php respondió:', text);
      throw new Error(js?.error || 'No se pudo cargar productos. Revisá ruta/servidor.');
    }

    productos = Array.isArray(js.data) ? js.data : [];
    loaded = true;
  }

  function filtrar(q){
    q = (q||'').trim().toLowerCase();
    if (!q){ filtrados = productos.slice(0, 500); return; }

    filtrados = productos.filter(p=>{
      const cod = String(p.codigo_barra_pro||'').toLowerCase();
      const nom = String(p.nombre_pro||'').toLowerCase();
      return cod.includes(q) || nom.includes(q);
    }).slice(0, 500);
  }

  function paintActive(){
    $$(T_BODY+' tr').forEach((tr, idx)=> tr.classList.toggle('row-active', idx === selIndex));
  }

  function render(){
    const tbody = $(T_BODY);
    if (!tbody) return;

    if (!filtrados.length){
      tbodyMsg('Sin resultados.');
      selIndex = -1;
      return;
    }

    tbody.innerHTML = '';

    filtrados.forEach((p, i)=>{
      const tr = document.createElement('tr');
      tr.tabIndex = 0;
      tr.dataset.idx = String(i);

      const p1 = num(p?.precio1_pro);
      const p2 = num(p?.precio2_pro);
      const p3 = num(p?.precio3_pro);
      const st = num(p?.cantidad_uni_pro ?? p?.stock ?? p?.stock_pro);

      const preciosHTML = `
        <div class="mp-precios">
          <button type="button" class="mp-add-tier" data-tier="precio1_pro">P1 · ${fmtGs(p1)}</button>
          <button type="button" class="mp-add-tier" data-tier="precio2_pro">P2 · ${fmtGs(p2)}</button>
          <button type="button" class="mp-add-tier" data-tier="precio3_pro">P3 · ${fmtGs(p3)}</button>
        </div>`;

      tr.innerHTML = `
        <td>${esc(p.codigo_barra_pro ?? '')}</td>
        <td>${esc(p.nombre_pro ?? '')}</td>
        <td style="text-align:right">${preciosHTML}</td>
        <td style="text-align:right">${st}</td>
      `;

      tbody.appendChild(tr);
    });

    selIndex = 0;
    paintActive();
  }

  function agregarConTier(p, tierKey){
    const precio = num(p?.[tierKey] ?? p?.precio1_pro);
    if (typeof window.agregarAlCarrito === 'function') {
      window.agregarAlCarrito({ ...p, precio }, 1);
    } else {
      alert(`Agregado: ${p?.nombre_pro} a ${fmtGs(precio)}`);
    }
  }

  function addSelectedAndClose(){
    if (selIndex < 0 || selIndex >= filtrados.length) return;
    agregarConTier(filtrados[selIndex], precioKey());
    closeModal();
  }

  // Delegación global (no depende del orden de carga)
  document.addEventListener('click', (e)=>{
    const abrirBtn = e.target.closest('#btn-abrir-lista');
    if (abrirBtn){
      e.preventDefault();

      // ✅ abrir SIEMPRE
      openModal();

      ensureData()
        .then(()=>{ filtrar(''); render(); })
        .catch(err=>{
          console.error(err);
          tbodyMsg(`<b>Error:</b> ${esc(err.message)}<br><small>Tip: abrí F12 → Console y verificá si <code>listar_productos.php</code> da 404/500.</small>`);
        });
      return;
    }

    if (e.target?.id === 'mp-cerrar') { e.preventDefault(); closeModal(); return; }

    const modalEl = document.getElementById(MODAL_ID);
    if (modalEl && !modalEl.classList.contains('hidden')) {
      if (e.target === modalEl) { closeModal(); return; }
    }

    const tierBtn = e.target.closest('.mp-add-tier');
    if (tierBtn){
      const tr = e.target.closest('tr[data-idx]');
      if (!tr) return;
      const p = filtrados[Number(tr.dataset.idx)];
      agregarConTier(p, tierBtn.dataset.tier);
      closeModal();
      return;
    }

    const row = e.target.closest('tr[data-idx]');
    if (row && e.target.tagName !== 'BUTTON'){
      selIndex = Number(row.dataset.idx || -1);
      addSelectedAndClose();
    }
  }, true);

  document.addEventListener('keydown', (e)=>{
    const m = document.getElementById(MODAL_ID);
    const visible = m && !m.classList.contains('hidden');
    if (!visible) return;

    if (e.key === 'Escape'){ e.preventDefault(); closeModal(); return; }

    const rows = $$(T_BODY+' tr');
    if (!rows.length) return;

    if (e.key === 'ArrowDown'){ e.preventDefault(); selIndex = Math.min(selIndex+1, rows.length-1); paintActive(); rows[selIndex]?.scrollIntoView({block:'nearest'}); }
    else if (e.key === 'ArrowUp'){ e.preventDefault(); selIndex = Math.max(selIndex-1, 0); paintActive(); rows[selIndex]?.scrollIntoView({block:'nearest'}); }
    else if (e.key === 'Enter'){ e.preventDefault(); addSelectedAndClose(); }
  });

  document.addEventListener('input', (e)=>{
    if (e.target?.id === 'mp-buscar'){
      filtrar(e.target.value);
      render();
    }
  });

})();