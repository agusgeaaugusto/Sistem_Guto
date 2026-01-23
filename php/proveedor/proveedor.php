<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Proveedores</title>

  <!-- 🔗 Theme unificado -->
<link rel="stylesheet" href="../css/app-forms.css?v=20260103-1">

  <!-- Layout mínimo -->
  <style>
    body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;display:flex;align-items:flex-start;justify-content:center;padding:24px}
    .app{width:min(1200px,100%);display:grid;gap:18px}
    .header{display:flex;align-items:center;justify-content:space-between}
    .title{font-weight:800;font-size:clamp(18px,2.2vw,26px)}
    .grid{display:grid;grid-template-columns:420px 1fr;gap:18px}
    @media (max-width: 960px){.grid{grid-template-columns:1fr}}
    .right{text-align:right}.nowrap{white-space:nowrap}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
  </style>
</head>
<body>
<div class="app" role="application" aria-label="Gestión de Proveedores">
  <div class="header"><div class="title">Gestión de Proveedores</div></div>

  <div class="grid">
    <!-- Formulario -->
    <div class="form-card">
      <h2 class="form-title">Agregar proveedor</h2>
      <form id="formAdd" class="app-form" autocomplete="off" novalidate>
        <div class="field">
          <label class="label" for="nombre_prove">Nombre</label>
          <input class="input" id="nombre_prove" name="nombre_prove" required>
        </div>
        <div class="field">
          <label class="label" for="ruc_prove">RUC</label>
          <input class="input" id="ruc_prove" name="ruc_prove" inputmode="numeric" required>
          <div class="helper">Solo números. Validamos duplicados antes de guardar.</div>
        </div>
        <div class="field">
          <label class="label" for="direccion_prove">Dirección</label>
          <input class="input" id="direccion_prove" name="direccion_prove" required>
        </div>
        <div class="field">
          <label class="label" for="telefono_prove">Teléfono</label>
          <input class="input" id="telefono_prove" name="telefono_prove" inputmode="numeric" required>
        </div>
        <div class="form-actions">
          <button class="btn" type="submit">Guardar</button>
          <button class="btn ghost" type="reset">Limpiar</button>
        </div>
      </form>
    </div>

    <!-- Listado -->
    <div class="table-wrap">
      <div class="panel">
        <h2 class="form-title">Listado de proveedores</h2>
        <div class="grid" style="grid-template-columns:1fr 220px;gap:12px">
          <div class="field">
            <label class="label" for="buscar">Buscar</label>
            <div class="control has-icon">
              <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              <input class="input" id="buscar" placeholder="Nombre, RUC, teléfono…">
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
        <table class="table" aria-label="tabla de proveedores">
          <thead>
            <tr>
              <th data-key="id_proveedor" class="nowrap">ID ▾</th>
              <th data-key="nombre_prove">Nombre</th>
              <th data-key="ruc_prove">RUC</th>
              <th data-key="direccion_prove">Dirección</th>
              <th data-key="telefono_prove">Teléfono</th>
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
  <div class="dialog-header">Editar proveedor</div>
  <div class="dialog-body">
    <form id="formEdit" class="app-form" novalidate>
      <input type="hidden" id="edit_id" name="id_proveedor">
      <div class="field">
        <label class="label" for="edit_nombre">Nombre</label>
        <input class="input" id="edit_nombre" name="nuevo_nombre_proveedor" required>
      </div>
      <div class="field">
        <label class="label" for="edit_ruc">RUC</label>
        <input class="input" id="edit_ruc" name="nuevo_ruc" inputmode="numeric" required>
      </div>
      <div class="field">
        <label class="label" for="edit_dir">Dirección</label>
        <input class="input" id="edit_dir" name="nueva_direccion" required>
      </div>
      <div class="field">
        <label class="label" for="edit_tel">Teléfono</label>
        <input class="input" id="edit_tel" name="nuevo_telefono" inputmode="numeric" required>
      </div>
    </form>
  </div>
  <div class="dialog-actions">
    <button class="btn ghost" id="btnCancelE" type="button">Cancelar</button>
    <button class="btn" id="btnSaveE" type="button">Guardar</button>
  </div>
</dialog>

<script>
/* ===== Config ===== */
const API_LIST = 'register_proveedor_bi.php'; // GET lista / POST crear
const API_GET  = 'get_proveedor.php?id=';     // GET detalle
const API_EDIT = 'editar.php';                // POST edición
const API_DEL  = 'eliminar.php?id=';          // GET eliminar (mantengo tu formato)

/* ===== DOM ===== */
const tbody = document.getElementById('tbody');
const buscar = document.getElementById('buscar');
const pageSize = document.getElementById('pageSize');
const info = document.getElementById('info');
const prev = document.getElementById('prev');
const next = document.getElementById('next');

const formAdd = document.getElementById('formAdd');
const dlgEdit = document.getElementById('dlgEdit');
const formEdit = document.getElementById('formEdit');
const edit_id = document.getElementById('edit_id');
const edit_nombre = document.getElementById('edit_nombre');
const edit_ruc = document.getElementById('edit_ruc');
const edit_dir = document.getElementById('edit_dir');
const edit_tel = document.getElementById('edit_tel');

/* ===== Estado ===== */
let data = [];
let page = 1;
let key = 'id_proveedor';
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
      <td><div class="skeleton" style="width:40%"></div></td>
      <td><div class="skeleton" style="width:80%"></div></td>
      <td><div class="skeleton" style="width:40%"></div></td>
      <td class="right"><div class="skeleton" style="width:50%;margin-left:auto"></div></td></tr>`).join('')};
const cmp=(a,b,k)=>{const A=(a[k]??'').toString().toLowerCase(),B=(b[k]??'').toString().toLowerCase();if(!isNaN(+a[k])&&!isNaN(+b[k]))return +a[k]-+b[k];return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'})};
const onlyDigits = v => /^[0-9]+$/.test((v||'').toString().trim());

/* ===== Filtro/Render ===== */
const filt=()=>{
  const q=(buscar.value||'').toLowerCase().trim();
  if(!q) return data.slice();
  return data.filter(r=>{
    const t = `${r.id_proveedor||''} ${r.nombre_prove||''} ${r.ruc_prove||''} ${r.direccion_prove||''} ${r.telefono_prove||''}`.toLowerCase();
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
      <td class="mono nowrap">#${r.id_proveedor??''}</td>
      <td>${(r.nombre_prove??'').replace(/</g,'&lt;')}</td>
      <td>${r.ruc_prove??''}</td>
      <td>${(r.direccion_prove??'').replace(/</g,'&lt;')}</td>
      <td>${r.telefono_prove??''}</td>
      <td class="right nowrap">
        <button class="btn ghost" onclick="openEdit(${r.id_proveedor})">Editar</button>
        <button class="btn danger" onclick="delRow(${r.id_proveedor})">Eliminar</button>
      </td>
    </tr>
  `).join('') : `<tr><td colspan="6"><div class="empty">Sin resultados</div></td></tr>`;

  info.textContent = `Página ${page}/${max}`;
  prev.disabled = page<=1; next.disabled = page>=max;
}

/* ===== Eventos de orden ===== */
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
  data = Array.isArray(j) ? j : (j && j.data) ? j.data : [];
  render();
}

/* Alta */
formAdd.addEventListener('submit', async (ev)=>{
  ev.preventDefault();
  const fd = new FormData(formAdd);
  const nombre = (fd.get('nombre_prove')||'').trim();
  const ruc = (fd.get('ruc_prove')||'').trim();
  const tel = (fd.get('telefono_prove')||'').trim();

  if(!onlyDigits(ruc)){ toast('RUC inválido','Solo números','error'); return; }
  if(!onlyDigits(tel)){ toast('Teléfono inválido','Solo números','error'); return; }

  // Duplicado RUC en memoria
  if(data.some(p => String(p.ruc_prove) === ruc)){
    toast('RUC ya registrado','','error'); return;
  }

  const r = await fetch(API_LIST,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo guardar', j.message||'', 'error'); return; }
  toast('Proveedor guardado');
  formAdd.reset();
  await load();
});

/* Editar (abrir) */
window.openEdit = async (id)=>{
  const r = await fetch(API_GET + encodeURIComponent(id), {cache:'no-store'});
  const j = await safeJson(r);
  if(!j || !j.id_proveedor){ toast('No se pudo cargar el proveedor','','error'); return; }
  edit_id.value = j.id_proveedor;
  edit_nombre.value = j.nombre_prove || '';
  edit_ruc.value = j.ruc_prove || '';
  edit_dir.value = j.direccion_prove || '';
  edit_tel.value = j.telefono_prove || '';
  dlgEdit.showModal();
};
document.getElementById('btnCancelE').onclick = ()=> dlgEdit.close();

/* Editar (guardar) */
document.getElementById('btnSaveE').addEventListener('click', async ()=>{
  const fd = new FormData(formEdit);
  const id = fd.get('id_proveedor');
  const ruc = (fd.get('nuevo_ruc')||'').trim();
  const tel = (fd.get('nuevo_telefono')||'').trim();

  if(!onlyDigits(ruc)){ toast('RUC inválido','Solo números','error'); return; }
  if(!onlyDigits(tel)){ toast('Teléfono inválido','Solo números','error'); return; }

  // Duplicado RUC (excluyendo el propio)
  if(data.some(p => String(p.ruc_prove) === ruc && String(p.id_proveedor) !== String(id))){
    toast('RUC ya registrado','','error'); return;
  }

  const r = await fetch(API_EDIT, {method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo editar', j.message||'', 'error'); return; }
  toast('Proveedor actualizado');
  dlgEdit.close();
  await load();
});

/* Eliminar */
window.delRow = async (id)=>{
  if(!confirm('¿Eliminar proveedor?')) return;
  const r = await fetch(API_DEL + encodeURIComponent(id), {method:'GET'});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo eliminar', j.message||'', 'error'); return; }
  toast('Eliminado');
  await load();
};

/* Init */
load();
</script>
</body>
</html>
