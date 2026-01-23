<?php
// usuario.php — Gestión de usuarios (CRUD) — CORREGIDO (campos completos)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestión de Usuarios</title>

  <!-- Si no tenés este CSS, igual funciona: es solo estética -->
  <link rel="stylesheet" href="../css/app-forms.css?v=20260103-1">

  <style>
    body{
      margin:0;
      font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      display:flex; align-items:flex-start; justify-content:center;
      padding:24px;
      background: radial-gradient(1200px 600px at 110% -10%,rgba(124,92,255,.12),transparent 50%),
                  radial-gradient(1000px 500px at -10% 110%,rgba(110,231,255,.12),transparent 50%),
                  #0c0d12;
      color:#e6e9f5;
    }
    .app{ width:min(1100px,100%); display:grid; gap:18px }
    .header{ display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap }
    .title{ font-weight:800; letter-spacing:.3px; font-size:clamp(18px,2.4vw,26px);
      background:linear-gradient(90deg,#e6e9f5,#6ee7ff);
      -webkit-background-clip:text; background-clip:text; color:transparent;
    }
    .card{ background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:16px; }
    .grid{ display:grid; grid-template-columns: 380px 1fr; gap:18px }
    @media (max-width: 980px){ .grid{ grid-template-columns:1fr; } }
    .form-title{ margin:0 0 10px; font-size:14px; color:#cfd6ff; opacity:.9 }
    .field{ margin:10px 0; }
    .label{ display:block; margin:0 0 6px; font-size:12px; color:#b9c2ff; opacity:.9 }
    .input, .select{
      width:100%; padding:10px 12px; border-radius:12px;
      border:1px solid rgba(255,255,255,.12);
      background: rgba(0,0,0,.25);
      color:#e6e9f5;
      outline:none;
    }
    .input:focus, .select:focus{ border-color: rgba(110,231,255,.55); box-shadow: 0 0 0 4px rgba(110,231,255,.12); }
    .row2{ display:grid; grid-template-columns:1fr 1fr; gap:10px }
    @media (max-width: 520px){ .row2{ grid-template-columns:1fr; } }
    .actions{ display:flex; gap:10px; flex-wrap:wrap; margin-top:12px }
    .btn{
      border:1px solid rgba(255,255,255,.14);
      background: rgba(255,255,255,.06);
      color:#e6e9f5;
      padding:10px 12px;
      border-radius:12px;
      cursor:pointer;
      font-weight:700;
    }
    .btn.primary{ background: linear-gradient(90deg, rgba(110,231,255,.18), rgba(124,92,255,.18)); border-color: rgba(110,231,255,.35); }
    .btn.danger{ background: rgba(239,68,68,.14); border-color: rgba(239,68,68,.38); }
    .table-wrap{ overflow:auto; }
    table{ width:100%; border-collapse:collapse; min-width:720px; }
    th,td{ text-align:left; padding:10px 10px; border-bottom:1px solid rgba(255,255,255,.08); font-size:13px; }
    th{ color:#b9c2ff; font-size:12px; text-transform:uppercase; letter-spacing:.8px; }
    .pill{ display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; border:1px solid rgba(255,255,255,.14); font-size:12px;}
    .pill.on{ background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.35); }
    .pill.off{ background: rgba(239,68,68,.10); border-color: rgba(239,68,68,.30); }
    .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    dialog{ border:none; border-radius:16px; padding:0; max-width:520px; width:min(520px, 96vw); background:#0f121a; color:#e6e9f5; border:1px solid rgba(255,255,255,.12); }
    dialog::backdrop{ background: rgba(0,0,0,.55); }
    .dlg-h{ padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.10); display:flex; justify-content:space-between; align-items:center; gap:10px }
    .dlg-b{ padding:14px 16px; }
    .toast{ position:fixed; right:18px; bottom:18px; padding:12px 14px; border-radius:14px;
      background: rgba(0,0,0,.70); border:1px solid rgba(255,255,255,.14); color:#e6e9f5;
      max-width:min(420px, 92vw); display:none;
    }
    .toast.show{ display:block; }
    .toast small{ display:block; opacity:.8; margin-top:2px }
  </style>
</head>

<body>
  <div class="app">
    <div class="header">
      <div class="title">Gestión de Usuarios</div>
      
    </div>

    <div class="grid">
      <!-- Panel izquierdo: alta -->
      <div class="card">
        <h2 class="form-title">Agregar usuario</h2>

        <form id="formAdd" autocomplete="off" novalidate>
          <div class="field">
            <label class="label">Nombre de usuario</label>
            <input class="input" name="nombre_usu" required>
          </div>

          <div class="field">
            <label class="label">Clave</label>
            <input class="input" type="password" name="clave_usu" required autocomplete="new-password">
          </div>

          <div class="row2">
            <div class="field">
              <label class="label">Rol (id_rol)</label>
              <input class="input" type="number" name="id_rol" required min="1">
            </div>

            <div class="field">
              <label class="label">Estado</label>
              <select class="select" name="estado_usu" required>
                <option value="1" selected>Activo</option>
                <option value="0">Inactivo</option>
              </select>
            </div>
          </div>

          <div class="actions">
            <button class="btn primary" id="btnAdd" type="submit">Guardar</button>
            <button class="btn" type="reset">Limpiar</button>
          </div>

          
        </form>
      </div>

      <!-- Panel derecho: listado -->
      <div class="card table-wrap">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap">
          <h2 class="form-title" style="margin:0">Listado</h2>
          <div class="actions" style="margin:0">
            <button class="btn" id="btnReload" type="button">Recargar</button>
          </div>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Usuario</th>
              <th>Estado</th>
              <th>Creado</th>
              <th>Actualizado</th>
              <th>Rol</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Dialog editar -->
  <dialog id="dlgEdit">
    <div class="dlg-h">
      <div style="font-weight:800">Editar usuario</div>
      <button class="btn" id="cancelE" type="button">Cerrar</button>
    </div>
    <div class="dlg-b">
      <form id="formEdit" autocomplete="off" novalidate>
        <input type="hidden" name="id_usu" id="edit_id">

        <div class="field">
          <label class="label">Nombre de usuario</label>
          <input class="input" name="nombre_usu" id="edit_nombre" required>
        </div>

        <div class="field">
          <label class="label">Nueva clave (opcional)</label>
          <input class="input" type="password" name="clave_usu" id="edit_clave" autocomplete="new-password" placeholder="Dejar vacío para no cambiar">
        </div>

        <div class="row2">
          <div class="field">
            <label class="label">Rol (id_rol)</label>
            <input class="input" type="number" name="id_rol" id="edit_rol" required min="1">
          </div>

          <div class="field">
            <label class="label">Estado</label>
            <select class="select" name="estado_usu" id="edit_estado" required>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
        </div>

        <div class="actions">
          <button class="btn primary" id="saveE" type="submit">Guardar cambios</button>
        </div>
      </form>
    </div>
  </dialog>

  <div id="toast" class="toast"></div>

  <script>
  const API_LIST = 'register_usuario_bi.php'; // GET lista / POST inserta
  const API_GET  = 'get_usuario.php';
  const API_EDIT = 'editar.php';
  const API_DEL  = 'eliminar.php';

  const tbody = document.getElementById('tbody');
  const toastEl = document.getElementById('toast');

  function toast(title, detail='', type='info'){
    toastEl.className = 'toast show';
    toastEl.innerHTML = `<strong>${title}</strong>${detail?`<small>${detail}</small>`:''}`;
    clearTimeout(window.__t);
    window.__t = setTimeout(()=>toastEl.className='toast', 2600);
  }

  async function safeJson(r){
    const t = await r.text();
    try { return JSON.parse(t); } catch(e){ return { success:false, message:t || 'Respuesta no-JSON' }; }
  }

  function pillEstado(v){
    const on = String(v) === '1';
    return `<span class="pill ${on?'on':'off'}">${on?'Activo':'Inactivo'}</span>`;
  }

  function row(u){
    return `<tr>
      <td class="mono">${u.id_usu}</td>
      <td>${escapeHtml(u.nombre_usu||'')}</td>
      <td>${pillEstado(u.estado_usu)}</td>
      <td class="mono">${u.fecha_creado_usu||''}</td>
      <td class="mono">${u.fecha_actualiza_usu||''}</td>
      <td class="mono">${u.id_rol||''}</td>
      <td>
        <button class="btn" type="button" onclick="openEdit(${u.id_usu})">Editar</button>
        <button class="btn danger" type="button" onclick="delUser(${u.id_usu})">Eliminar</button>
      </td>
    </tr>`;
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }

  async function load(){
    tbody.innerHTML = `<tr><td colspan="7" style="opacity:.7">Cargando...</td></tr>`;
    try{
      const r = await fetch(API_LIST, { method:'GET' });
      const j = await safeJson(r);
      if(!Array.isArray(j)){
        tbody.innerHTML = `<tr><td colspan="7" style="opacity:.7">No hay datos</td></tr>`;
        return;
      }
      tbody.innerHTML = j.map(row).join('') || `<tr><td colspan="7" style="opacity:.7">Sin usuarios</td></tr>`;
    }catch(e){
      tbody.innerHTML = `<tr><td colspan="7" style="opacity:.7">Error al cargar</td></tr>`;
    }
  }

  // Alta
  document.getElementById('formAdd').addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    const fd = new FormData(ev.target);
    try{
      const r = await fetch(API_LIST, { method:'POST', body: fd });
      const j = await safeJson(r);
      if(j && j.success === false) return toast('No se pudo guardar', j.message||'', 'error');
      toast('Usuario guardado');
      ev.target.reset();
      await load();
    }catch(e){
      toast('Error al guardar','', 'error');
    }
  });

  document.getElementById('btnReload').addEventListener('click', load);

  // Editar
  const dlg = document.getElementById('dlgEdit');
  const formE = document.getElementById('formEdit');

  window.openEdit = async (id)=>{
    try{
      const r = await fetch(`${API_GET}?id=${encodeURIComponent(id)}`, { method:'GET' });
      const d = await safeJson(r);
      if(d && d.error) return toast('No se pudo cargar', d.error, 'error');

      document.getElementById('edit_id').value = d.id_usu || id;
      document.getElementById('edit_nombre').value = d.nombre_usu || '';
      document.getElementById('edit_clave').value = '';
      document.getElementById('edit_rol').value = d.id_rol || '';
      document.getElementById('edit_estado').value = String(d.estado_usu ?? '1');

      dlg.showModal();
    }catch(e){
      toast('No se pudo cargar el usuario','', 'error');
    }
  };

  document.getElementById('cancelE').addEventListener('click', ()=>dlg.close());

  formE.addEventListener('submit', async (ev)=>{
    ev.preventDefault();
    try{
      const fd = new FormData(formE);
      const r = await fetch(API_EDIT, { method:'POST', body: fd });
      const j = await safeJson(r);
      if(j && j.success === false) return toast('No se pudo editar', j.message||'', 'error');
      toast('Usuario actualizado');
      dlg.close();
      await load();
    }catch(e){
      toast('Error al editar','', 'error');
    }
  });

  // Eliminar
  window.delUser = async (id)=>{
    if(!confirm('¿Eliminar usuario?')) return;
    try{
      const r = await fetch(`${API_DEL}?eliminar=${encodeURIComponent(id)}`, { method:'GET' });
      const j = await safeJson(r);
      if(j && j.success === false) return toast('No se pudo eliminar', j.message||'', 'error');
      toast('Eliminado');
      await load();
    }catch(e){
      toast('Error al eliminar','', 'error');
    }
  };

  load();
  </script>
</body>
</html>
