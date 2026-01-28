<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Roles</title>

  <!-- 🔗 Theme unificado -->
 <link rel="stylesheet" href="../css/app-forms.css?v=20260127-std">

  <!-- Layout mínimo -->
</head>
<body>
<div class="app" role="application" aria-label="Gestión de Roles">
  <div class="header"><div class="title">Gestión de Roles</div></div>

  <div class="grid">
    <!-- Panel: Alta de rol -->
    <div class="form-card">
      <h2 class="form-title">Agregar rol</h2>
      <form id="formAdd" class="app-form" autocomplete="off" novalidate>
        <div class="field">
          <label class="label" for="descripcion_rol">Descripción</label>
          <input class="input" id="descripcion_rol" name="descripcion_rol" minlength="2" required>
          <div class="helper">Mínimo 2 caracteres.</div>
        </div>

        <div class="field">
          <label class="label" for="accesos_rol">Accesos (separados por coma)</label>
          <input class="input" id="accesos_rol" name="accesos_rol" placeholder="usuarios:listar,ventas:crear,...">
          <div class="helper">Se enviará como texto; tu backend puede parsear por coma.</div>
        </div>

        <div class="field">
          <label class="label" for="fecha_rol">Fecha de creación</label>
          <input class="input" type="date" id="fecha_rol" name="fecha_rol" required>
        </div>

        
      </form>
      <div class="form-actions">
          <button class="btn" type="submit">Guardar</button>
          <button class="btn ghost" type="reset">Limpiar</button>
        </div>
    </div>

    <!-- Panel: Listado -->
    <div class="table-wrap">
      <div class="panel">
        <h2 class="form-title">Listado de roles</h2>
        <div class="grid" style="grid-template-columns:1fr 220px;gap:12px">
          <div class="field">
            <label class="label" for="buscar">Buscar</label>
            <div class="control has-icon">
              <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              <input class="input" id="buscar" placeholder="Descripción, accesos…">
            </div>
          </div>
          <div class="field">
            <label class="label" for="pageSize">Tamaño de página</label>
            <select class="select" id="pageSize">
              <option value="5">5 / pág</option>
              <option value="10" selected>10 / pág</option>
              <option value="20">20 / pág</option>
              <option value="50">50 / pág</option>
            </select>
          </div>
        </div>
      </div>

      <div class="scroll-x">
        <table class="table" aria-label="tabla de roles">
          <thead>
            <tr>
              <th data-key="id_rol" class="nowrap">ID ▾</th>
              <th data-key="descripcion_rol">Descripción</th>
              <th data-key="accesos_rol">Accesos</th>
              <th data-key="creado_rol">Creado</th>
              <th data-key="fecha_rol">Fecha</th>
              <th class="right">Acciones</th>
            </tr>
          </thead>
          <tbody id="tbody"></tbody>
        </table>
      </div>

      <div class="pager">
        <button class="btn ghost" id="prev">&laquo;</button>
        <span class="badge" id="info">Página 1/1</span>
        <button class="btn ghost" id="next">&raquo;</button>
      </div>
    </div>
  </div>
</div>

<!-- Toasts -->
<div class="toast-wrap" id="toasts" aria-live="polite" aria-atomic="true"></div>

<!-- Modal editar -->
<dialog id="dlgEdit">
  <div class="dialog-header">Editar rol</div>
  <div class="dialog-body">
    <form id="formEdit" class="app-form" novalidate>
      <input type="hidden" id="edit_id" name="id_rol">
      <div class="field">
        <label class="label" for="edit_desc">Descripción</label>
        <input class="input" id="edit_desc" name="nueva_descripcion_rol" minlength="2" required>
      </div>
    </form>
  </div>
  <div class="dialog-actions">
    <button class="btn ghost" id="btnCancelE" type="button">Cancelar</button>
    <button class="btn" id="btnSaveE" type="button">Guardar</button>
  </div>
</dialog>

<script>
/* ===== Endpoints ===== */
const API_LIST = 'register_rol_bi.php'; // GET: lista | POST: crear
const API_GET  = 'get_rol.php?id=';     // GET: uno
const API_EDIT = 'editar.php';          // POST: id_rol, nueva_descripcion_rol
const API_DEL  = 'eliminar.php?eliminar='; // GET: eliminar

/* ===== DOM ===== */
const tbody = document.getElementById('tbody');
const buscar = document.getElementById('buscar');
const pageSize = document.getElementById('pageSize');
const info = document.getElementById('info');
const prev = document.getElementById('prev');
const next = document.getElementById('next');

const formAdd = document.getElementById('formAdd');
const fechaAdd = document.getElementById('fecha_rol');

const dlgEdit = document.getElementById('dlgEdit');
const formEdit = document.getElementById('formEdit');
const edit_id = document.getElementById('edit_id');
const edit_desc = document.getElementById('edit_desc');

/* ===== Estado ===== */
let data = [];
let page = 1;
let key = 'id_rol';
let asc = true;

/* ===== Utils ===== */
const toast=(t,m='',type='success')=>{
  const w=document.getElementById('toasts'), el=document.createElement('div');
  el.className='toast '+(type||''); el.innerHTML=`<div><div class="t-title">${t}</div>${m?`<div class="t-msg">${m}</div>`:''}</div>`;
  w.appendChild(el); setTimeout(()=>{ el.style.opacity=0; setTimeout(()=>w.removeChild(el),300) },3200);
};
const safeJson=async r=>{try{return await r.json()}catch{return null}};
const setLoading=()=>{tbody.innerHTML=Array.from({length:6}).map(()=>`
  <tr><td><div class="skeleton"></div></td>
      <td><div class="skeleton" style="width:60%"></div></td>
      <td><div class="skeleton" style="width:50%"></div></td>
      <td><div class="skeleton" style="width:30%"></div></td>
      <td><div class="skeleton" style="width:40%"></div></td>
      <td class="right"><div class="skeleton" style="width:50%;margin-left:auto"></div></td></tr>`).join('')};
const cmp=(a,b,k)=>{const A=(a[k]??'').toString().toLowerCase(),B=(b[k]??'').toString().toLowerCase();if(!isNaN(+a[k])&&!isNaN(+b[k]))return +a[k]-+b[k];return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'})};

/* ===== Filtro + render ===== */
const filt=()=>{
  const q=(buscar.value||'').toLowerCase().trim();
  if(!q) return data.slice();
  return data.filter(r=>{
    const t = `${r.id_rol||''} ${r.descripcion_rol||''} ${r.accesos_rol||''} ${r.creado_rol||''} ${r.fecha_rol||''}`.toLowerCase();
    return t.includes(q);
  });
};
function render(){
  const rows = filt().sort((a,b)=> asc ? cmp(a,b,key) : cmp(b,a,key));
  const size = +pageSize.value || 10;
  const total = rows.length, max = Math.max(1, Math.ceil(total/size));
  if(page>max) page=max;
  const sl = rows.slice((page-1)*size, (page-1)*size+size);

  tbody.innerHTML = sl.length ? sl.map(r=>`
    <tr class="data">
      <td class="mono nowrap">#${r.id_rol??''}</td>
      <td>${(r.descripcion_rol??'').replace(/</g,'&lt;')}</td>
      <td><span class="badge">${(r.accesos_rol??'').toString().replace(/</g,'&lt;')}</span></td>
      <td>${(r.creado_rol??'').toString().replace(/</g,'&lt;')}</td>
      <td>${r.fecha_rol??''}</td>
      <td class="right nowrap">
        <button class="btn ghost" onclick="openEdit(${r.id_rol})">Editar</button>
        <button class="btn danger" onclick="delRow(${r.id_rol})">Eliminar</button>
      </td>
    </tr>
  `).join('') : `<tr><td colspan="6"><div class="empty">Sin resultados</div></td></tr>`;

  info.textContent = `Página ${page}/${max}`;
  prev.disabled = page<=1; next.disabled = page>=max;
}

/* ===== Orden ===== */
document.querySelectorAll('thead th[data-key]').forEach(th=>{
  th.addEventListener('click', ()=>{
    const k=th.getAttribute('data-key');
    if(key===k) asc=!asc; else {key=k; asc=true}
    document.querySelectorAll('thead th[data-key]').forEach(t=>{
      t.textContent = t.textContent.replace(/[▴▾]/g,'').trim();
      if(t.getAttribute('data-key')===key) t.textContent += ' ' + (asc?'▴':'▾');
    });
    render();
  });
});
buscar.oninput=()=>{page=1;render()};
pageSize.onchange=()=>{page=1;render()};
prev.onclick=()=>{if(page>1){page--;render()}};
next.onclick=()=>{page++;render()};

/* ===== Data I/O ===== */
async function load(){
  setLoading();
  const r = await fetch(API_LIST, {cache:'no-store'});
  const j = await safeJson(r);
  // Backend puede devolver array o {data:[...]}
  data = Array.isArray(j) ? j : (j && Array.isArray(j.data)) ? j.data : [];
  render();
}

/* Alta */
formAdd.addEventListener('submit', async (ev)=>{
  ev.preventDefault();
  const fd = new FormData(formAdd);
  // accesos_rol: enviamos texto tal cual; si querés array, adapta en backend
  const desc = (fd.get('descripcion_rol')||'').trim();
  if(desc.length < 2){ toast('Descripción muy corta','Mínimo 2 caracteres','error'); return; }

  const r = await fetch(API_LIST,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo guardar', j.message||'', 'error'); return; }
  toast('Rol guardado');
  formAdd.reset();
  setToday(); // reponer fecha actual
  await load();
});

/* Editar (abrir) */
window.openEdit = async (id)=>{
  const r = await fetch(API_GET + encodeURIComponent(id), {cache:'no-store'});
  const j = await safeJson(r);
  if(!j || !j.id_rol){ toast('No se pudo cargar el rol','','error'); return; }
  edit_id.value = j.id_rol;
  edit_desc.value = j.descripcion_rol || '';
  dlgEdit.showModal();
};
document.getElementById('btnCancelE').onclick = ()=> dlgEdit.close();

/* Editar (guardar) */
document.getElementById('btnSaveE').addEventListener('click', async ()=>{
  const fd = new FormData(formEdit);
  const desc = (fd.get('nueva_descripcion_rol')||'').trim();
  if(desc.length < 2){ toast('Descripción muy corta','','error'); return; }

  const r = await fetch(API_EDIT, {method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo editar', j.message||'', 'error'); return; }
  toast('Rol actualizado');
  dlgEdit.close();
  await load();
});

/* Eliminar */
window.delRow = async (id)=>{
  if(!confirm('¿Eliminar rol?')) return;
  const r = await fetch(API_DEL + encodeURIComponent(id), {method:'GET'});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo eliminar', j.message||'', 'error'); return; }
  toast('Eliminado');
  await load();
};

/* Fecha por defecto = hoy */
function setToday(){
  const t = new Date().toISOString().slice(0,10);
  fechaAdd.value = t;
}

/* Init */
setToday();
load();
</script>
</body>
</html>
