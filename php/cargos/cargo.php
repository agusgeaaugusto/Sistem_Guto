<?php
// cargo.php — Gestión de Cargos (UI con app-forms.css)
// Endpoints existentes:
// - Listar  : GET  register_cargo_bi.php  (JSON: array o {cargos:[]})
// - Agregar : POST register_cargo_bi.php  (nombre_cargo)
// - Editar  : POST editar.php             (id_cargo, nombre_cargo)
// - Eliminar: GET  eliminar.php?eliminar=ID
// - Obtener : GET  get_cargo.php?id=ID
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestión de Cargos</title>

  <!-- 🔗 Estilo global de formularios y tablas (excluye venta) -->
<link rel="stylesheet" href="../css/app-forms.css?v=20260103-1">






  <!-- Estilos mínimos de layout (solo estructura de la página) -->
  <style>
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      display:flex; align-items:flex-start; justify-content:center;
      padding:24px;
    }
    .app{ width:min(1200px,100%); display:grid; gap:18px }
    .header{ display:flex; align-items:center; justify-content:space-between }
    .title{ font-weight:800; font-size: clamp(18px, 2.2vw, 26px) }
    .grid{ display:grid; grid-template-columns: 420px 1fr; gap:18px }
    @media (max-width: 920px){ .grid{ grid-template-columns: 1fr } }
  </style>
</head>
<body>
  <div class="app" role="application" aria-label="Gestión de Cargos">
    <div class="header">
      <div class="title">Gestión de Cargos</div>
    </div>

    <div class="grid">
      <!-- Panel izquierdo: formulario -->
      <div class="form-card" aria-labelledby="titulo-form">
        <h2 class="form-title" id="titulo-form">Agregar nuevo cargo</h2>
        <!-- ⚠️ Usa el motor de formularios unificado -->
        <form id="agregarCargoForm" class="app-form" autocomplete="off" novalidate>
          <div class="field">
            <label class="label" for="nombre_cargo">Nombre del cargo</label>
            <div class="control">
              <input class="input" type="text" id="nombre_cargo" name="nombre_cargo"
                     placeholder="Ej: Auxiliar de ventas" minlength="3" required aria-required="true" />
            </div>
            <div class="helper">Mínimo 3 caracteres.</div>
          </div>

          <div class="form-actions">
            <button class="btn" id="btnGuardar" type="submit">Guardar cargo</button>
            <button class="btn ghost" type="reset">Limpiar</button>
          </div>
        </form>
      </div>

      <!-- Panel derecho: listado -->
      <div class="table-wrap" aria-labelledby="titulo-lista">
        <div class="panel">
          <h2 class="form-title" id="titulo-lista">Listado de cargos</h2>

          <div class="grid-2">
            <!-- Buscador -->
            <div class="field">
              <label class="label" for="search">Buscar por nombre</label>
              <div class="control has-icon">
                <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                <input class="input" id="search" placeholder="Escribe para filtrar..." />
              </div>
            </div>

            <!-- Controles -->
            <div class="field">
              <label class="label" for="pageSize">Tamaño de página</label>
              <select id="pageSize" class="select">
                <option value="5">5 / pág</option>
                <option value="10" selected>10 / pág</option>
                <option value="20">20 / pág</option>
                <option value="50">50 / pág</option>
              </select>
              <div class="form-actions" style="justify-content:flex-start;margin-top:10px">
                <button class="btn ghost" id="btnRefrescar" type="button">Refrescar</button>
              </div>
            </div>
          </div>
        </div>

        <div class="scroll-x">
          <table id="tablaCargos" class="table" aria-describedby="titulo-lista">
            <thead>
              <tr>
                <th data-key="id_cargo" class="nowrap">ID ▾</th>
                <th data-key="nombre_cargo">Nombre del cargo</th>
                <th class="right">Acciones</th>
              </tr>
            </thead>
            <tbody id="tbodyCargos"></tbody>
          </table>
        </div>

        <div class="pager">
          <button class="btn ghost" id="prevPage">&laquo;</button>
          <span class="badge" id="pageInfo">Página 1/1</span>
          <button class="btn ghost" id="nextPage">&raquo;</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toasts -->
  <div class="toast-wrap" id="toasts" aria-live="polite" aria-atomic="true"></div>

  <!-- Modal editar -->
  <dialog id="dialogEditar">
    <div class="dialog-header">Editar cargo</div>
    <div class="dialog-body">
      <form id="editarCargoForm" class="app-form" novalidate>
        <input type="hidden" id="id_cargo_editar" name="id_cargo" />
        <div class="field">
          <label class="label" for="nombre_cargo_editar">Nombre del cargo</label>
          <input class="input" type="text" id="nombre_cargo_editar" name="nombre_cargo" minlength="3" required />
        </div>
      </form>
    </div>
    <div class="dialog-actions">
      <button class="btn ghost" type="button" id="btnCancelarEditar">Cancelar</button>
      <button class="btn" type="button" id="btnGuardarEditar">Guardar cambios</button>
    </div>
  </dialog>

  <!-- Dialog confirmar eliminar -->
  <dialog id="dialogConfirm">
    <div class="dialog-header">Confirmar eliminación</div>
    <div class="dialog-body">
      <p class="helper">Esta acción no se puede deshacer.</p>
      <p>¿Desea eliminar el cargo <span class="badge" id="delId"></span>?</p>
    </div>
    <div class="dialog-actions">
      <button class="btn ghost" type="button" id="btnCancelDel">Cancelar</button>
      <button class="btn danger" type="button" id="btnOkDel">Eliminar</button>
    </div>
  </dialog>

  <script>
    // --- Config Endpoints ---
    const apiListar = 'register_cargo_bi.php';    // GET → JSON
    const apiAgregar = 'register_cargo_bi.php';   // POST nombre_cargo
    const apiEditar  = 'editar.php';              // POST id_cargo, nombre_cargo
    const apiEliminar= 'eliminar.php?eliminar=';  // GET ?eliminar=ID
    const apiGet     = 'get_cargo.php?id=';       // GET → JSON

    // --- DOM ---
    const tbody = document.getElementById('tbodyCargos');
    const formAgregar = document.getElementById('agregarCargoForm');
    const btnGuardar = document.getElementById('btnGuardar');
    const inputBuscar = document.getElementById('search');
    const pageSizeSel = document.getElementById('pageSize');
    const pageInfo = document.getElementById('pageInfo');
    const prevBtn = document.getElementById('prevPage');
    const nextBtn = document.getElementById('nextPage');
    const btnRefrescar = document.getElementById('btnRefrescar');

    const dlgEditar = document.getElementById('dialogEditar');
    const formEditar = document.getElementById('editarCargoForm');
    const dlgConfirm = document.getElementById('dialogConfirm');
    const delIdSpan = document.getElementById('delId');

    // --- Estado ---
    let datos = [];
    let ordenKey = 'id_cargo';
    let ordenAsc = true;
    let page = 1;

    // --- Utils UI ---
    function showToast(title, msg='', type='success', timeout=3200){
      const wrap = document.getElementById('toasts');
      const el = document.createElement('div');
      el.className = 'toast ' + (type || '');
      el.innerHTML = '<div><div class="t-title">'+title+'</div>' + (msg ? '<div class="t-msg">'+msg+'</div>' : '') + '</div>';
      wrap.appendChild(el);
      setTimeout(()=>{ el.style.opacity='0'; setTimeout(()=>wrap.removeChild(el), 300); }, timeout);
    }

    async function safeJson(res){
      try{ return await res.json(); }catch{ return null; }
    }
    function normalizeList(resp){
      if(Array.isArray(resp)) return resp;
      if(resp && Array.isArray(resp.cargos)) return resp.cargos;
      return [];
    }

    function setLoading(rows=6){
      tbody.innerHTML = Array.from({length:rows}).map(()=>`
        <tr>
          <td><div class="skeleton"></div></td>
          <td><div class="skeleton" style="width:60%"></div></td>
          <td class="right"><div class="skeleton" style="width:40%; margin-left:auto"></div></td>
        </tr>
      `).join('');
    }
    function emptyState(){
      tbody.innerHTML = '<tr><td colspan="3"><div class="empty">Sin resultados</div></td></tr>';
    }
    function cmp(a,b,key){
      const va = (a[key] ?? '').toString().toLowerCase();
      const vb = (b[key] ?? '').toString().toLowerCase();
      if(!isNaN(Number(a[key])) && !isNaN(Number(b[key]))){
        return Number(a[key]) - Number(b[key]);
      }
      return va.localeCompare(vb, 'es', {numeric:true, sensitivity:'base'});
    }
    function getFiltrados(){
      const q = (inputBuscar.value || '').toLowerCase().trim();
      return q ? datos.filter(f => (f.nombre_cargo||'').toLowerCase().includes(q)) : datos.slice();
    }
    function render(){
      const filas = getFiltrados().sort((a,b)=> (ordenAsc ? cmp(a,b,ordenKey) : cmp(b,a,ordenKey)));
      const pageSize = Number(pageSizeSel.value || 10);
      const total = filas.length || 0;
      const maxPage = Math.max(1, Math.ceil(total / pageSize));
      if(page > maxPage) page = maxPage;
      const start = (page-1)*pageSize;
      const end = start + pageSize;
      const visibles = filas.slice(start,end);

      if(visibles.length === 0){ emptyState(); }
      else{
        tbody.innerHTML = visibles.map(f => `
          <tr class="data">
            <td class="mono nowrap">#${f.id_cargo ?? ''}</td>
            <td><span class="badge">${(f.nombre_cargo ?? '').replace(/</g,'&lt;')}</span></td>
            <td class="right nowrap">
              <button class="btn ghost" onclick="abrirEditar(${Number(f.id_cargo)})">Editar</button>
              <button class="btn danger" onclick="confirmEliminar(${Number(f.id_cargo)})">Eliminar</button>
            </td>
          </tr>
        `).join('');
      }
      pageInfo.textContent = 'Página ' + page + '/' + (maxPage || 1);
      prevBtn.disabled = page <= 1;
      nextBtn.disabled = page >= maxPage;
    }

    async function listar(){
      setLoading();
      try{
        const res = await fetch(apiListar, {cache: 'no-store'});
        const js = await safeJson(res);
        datos = normalizeList(js);
        render();
      }catch(e){
        console.error(e);
        emptyState();
        showToast('Error al listar', 'No se pudo cargar el listado', 'error');
      }
    }

    // Sorting
    document.querySelectorAll('thead th[data-key]').forEach(th=>{
      th.addEventListener('click', ()=>{
        const key = th.getAttribute('data-key');
        if(ordenKey === key){ ordenAsc = !ordenAsc; }
        else { ordenKey = key; ordenAsc = true; }
        render();
        document.querySelectorAll('thead th[data-key]').forEach(t=>{
          t.textContent = t.textContent.replace(/[▴▾]/g, '').trim();
          if(t.getAttribute('data-key') === ordenKey){
            t.textContent += ordenAsc ? ' ▴' : ' ▾';
          }
        });
      });
    });

    inputBuscar.addEventListener('input', ()=>{ page=1; render(); });
    pageSizeSel.addEventListener('change', ()=>{ page=1; render(); });
    prevBtn.addEventListener('click', ()=>{ if(page>1){ page--; render(); } });
    nextBtn.addEventListener('click', ()=>{ page++; render(); });
    btnRefrescar.addEventListener('click', listar);

    // Agregar
    formAgregar.addEventListener('submit', async (ev)=>{
      ev.preventDefault();
      const nombre = (formAgregar.nombre_cargo.value || '').trim();
      if(nombre.length < 3){
        showToast('Nombre muy corto', 'Mínimo 3 caracteres', 'error');
        formAgregar.nombre_cargo.focus();
        return;
      }
      btnGuardar.disabled = true;
      const fd = new FormData(formAgregar);
      fd.set('nombre_cargo', nombre);
      try{
        const res = await fetch(apiAgregar, { method:'POST', body: fd });
        const j = await safeJson(res);
        if(j && j.success === false){
          showToast('No se pudo guardar', j.message || '', 'error');
        }else{
          showToast('Cargo guardado', '', 'success');
          formAgregar.reset();
          await listar();
        }
      }catch(e){
        showToast('Error al guardar', '', 'error');
      }finally{
        btnGuardar.disabled = false;
      }
    });

    // Editar
    async function abrirEditar(id){
      try{
        const res = await fetch(apiGet + encodeURIComponent(id));
        const j = await res.json();
        formEditar.id_cargo.value = j.id_cargo;
        formEditar.nombre_cargo.value = j.nombre_cargo || '';
        dlgEditar.showModal();
      }catch(e){
        showToast('No se pudo cargar el cargo', '', 'error');
      }
    }
    window.abrirEditar = abrirEditar;

    document.getElementById('btnCancelarEditar').addEventListener('click', ()=>dlgEditar.close());
    document.getElementById('btnGuardarEditar').addEventListener('click', async ()=>{
      const nombre = (formEditar.nombre_cargo.value || '').trim();
      if(nombre.length < 3){
        showToast('Nombre muy corto', 'Mínimo 3 caracteres', 'error');
        formEditar.nombre_cargo.focus();
        return;
      }
      const fd = new FormData(formEditar);
      fd.set('nombre_cargo', nombre);
      try{
        const res = await fetch(apiEditar, { method:'POST', body: fd });
        const j = await safeJson(res);
        if(j && j.success === false){
          showToast('No se pudo editar', j.message || '', 'error');
        }else{
          showToast('Cargo actualizado', '', 'success');
          dlgEditar.close();
          await listar();
        }
      }catch(e){
        showToast('Error al editar', '', 'error');
      }
    });

    // Eliminar
    let idAEliminar = null;
    function confirmEliminar(id){
      idAEliminar = id;
      delIdSpan.textContent = '#' + id;
      dlgConfirm.showModal();
    }
    window.confirmEliminar = confirmEliminar;

    document.getElementById('btnCancelDel').addEventListener('click', ()=>{
      idAEliminar = null; dlgConfirm.close();
    });
    document.getElementById('btnOkDel').addEventListener('click', async ()=>{
      if(!idAEliminar) return;
      try{
        await fetch(apiEliminar + encodeURIComponent(idAEliminar), { method:'GET' });
        showToast('Eliminado', '', 'success');
        dlgConfirm.close();
        await listar();
      }catch(e){
        showToast('Error al eliminar', '', 'error');
      }finally{
        idAEliminar = null;
      }
    });

    // Inicial
    listar();
  </script>
</body>
</html>
