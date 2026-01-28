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
  <link rel="stylesheet" href="../css/app-forms.css?v=20260127-std">

  
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
<div class="form-card">
<form id="formAdd" autocomplete="off" novalidate class="app-form">
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

        

          
        </form>
          <div class="actions">
            <button class="btn primary" id="btnAdd" type="submit">Guardar</button>
            <button class="btn" type="reset">Limpiar</button>
          </div>
</div>
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
      <form id="formEdit" autocomplete="off" novalidate class="app-form">
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
