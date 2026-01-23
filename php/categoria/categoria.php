<?php
// categoria.php - UI moderna (sin Bootstrap) con el mismo estilo de cargo.php
// Endpoints:
//   Listar   GET  register_categoria_bi.php  -> {success:true,data:[{id_cat,nombre_cat}]}
//   Agregar  POST register_categoria_bi.php  -> nombre_cat
//   Editar   POST editar.php                 -> id_cat, nuevo_nombre_categoria
//   Eliminar POST eliminar.php               -> id_cat
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Categorías</title>

  <!-- 🔗 Estilo global (ruta correcta desde /php/categoria/ → /assets/css/) -->
<link rel="stylesheet" href="../css/app-forms.css?v=20260103-1">

  <!-- Layout mínimo de la página (estructura nada más) -->
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
<div class="app" role="application" aria-label="Gestión de Categorías">
  <div class="header"><div class="title">Gestión de Categorías</div></div>

  <div class="grid">
    <!-- Panel izquierdo: formulario -->
    <div class="form-card">
      <h2 class="form-title">Agregar nueva categoría</h2>
      <form id="formAdd" class="app-form" autocomplete="off" novalidate>
        <div class="field">
          <label class="label" for="nombre_cat">Nombre de la categoría</label>
          <div class="control">
            <input class="input" type="text" id="nombre_cat" name="nombre_cat"
                   placeholder="Ej: Bebidas" minlength="2" required>
          </div>
          
        </div>

        <div class="form-actions">
          <button class="btn" id="btnGuardar" type="submit">Guardar</button>
          <button type="reset" class="btn ghost">Limpiar</button>
        </div>
      </form>
    </div>

    <!-- Panel derecho: listado -->
    <div class="table-wrap" aria-labelledby="titulo-lista">
      <div class="panel">
        <h2 class="form-title" id="titulo-lista">Listado de categorías</h2>

        <div class="grid-2">
          <!-- Buscador -->
          <div class="field">
            <label class="label" for="buscar">Buscar por nombre</label>
            <div class="control has-icon">
              <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
              </svg>
              <input class="input" id="buscar" placeholder="Buscar por nombre...">
            </div>
          </div>

          <!-- Tamaño de página -->
          <div class="field">
            <label class="label" for="pageSize">Tamaño de página</label>
            <select id="pageSize" class="select">
              <option value="5">5 / pág</option>
              <option value="10" selected>10 / pág</option>
              <option value="20">20 / pág</option>
              <option value="50">50 / pág</option>
            </select>
          </div>
        </div>
      </div>

      <div class="scroll-x">
        <table id="tabla" class="table" aria-label="tabla de categorías">
          <thead>
            <tr>
              <th data-key="id_cat" class="nowrap">ID ▾</th>
              <th data-key="nombre_cat">Nombre</th>
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
  <div class="dialog-header">Editar categoría</div>
  <div class="dialog-body">
    <form id="formEdit" class="app-form" novalidate>
      <input type="hidden" id="edit_id" name="id_cat">
      <div class="field">
        <label class="label" for="edit_nombre">Nombre</label>
        <input class="input" type="text" id="edit_nombre" name="nuevo_nombre_categoria" minlength="2" required>
      </div>
    </form>
  </div>
  <div class="dialog-actions">
    <button class="btn ghost" id="btnCancelE" type="button">Cancelar</button>
    <button class="btn" id="btnSaveE" type="button">Guardar</button>
  </div>
</dialog>

<script>
const API_LIST='register_categoria_bi.php',
      API_ADD ='register_categoria_bi.php',
      API_EDIT='editar.php',
      API_DEL ='eliminar.php';

const tbody = document.getElementById('tbody'),
      buscar = document.getElementById('buscar'),
      pageSize = document.getElementById('pageSize'),
      info = document.getElementById('info'),
      prev = document.getElementById('prev'),
      next = document.getElementById('next');

const dlg = document.getElementById('dlgEdit'),
      formE = document.getElementById('formEdit');

let data=[], page=1, key='id_cat', asc=true;

// UI helpers
const toast=(t,m='',type='success')=>{
  const w=document.getElementById('toasts'), el=document.createElement('div');
  el.className='toast '+(type||'');
  el.innerHTML=`<div><div class="t-title">${t}</div>${m?`<div class="t-msg">${m}</div>`:''}</div>`;
  w.appendChild(el);
  setTimeout(()=>{ el.style.opacity=0; setTimeout(()=>w.removeChild(el),300) },3200);
};
const safeJson=async r=>{try{return await r.json()}catch{return null}};
const norm=resp=>Array.isArray(resp)?resp:(resp&&Array.isArray(resp.data))?resp.data:[];
const setLoading=()=>{tbody.innerHTML=Array.from({length:6}).map(()=>`
  <tr>
    <td><div class="skeleton"></div></td>
    <td><div class="skeleton" style="width:60%"></div></td>
    <td class="right"><div class="skeleton" style="width:40%;margin-left:auto"></div></td>
  </tr>`).join('')};
const cmp=(a,b,k)=>{
  const A=(a[k]??'').toString().toLowerCase(), B=(b[k]??'').toString().toLowerCase();
  if(!isNaN(+a[k]) && !isNaN(+b[k])) return +a[k]-+b[k];
  return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'});
};
const filt=()=>{const q=(buscar.value||'').toLowerCase().trim(); return q?data.filter(x=>(x.nombre_cat||'').toLowerCase().includes(q)):data.slice();};

function render(){
  const rows=filt().sort((a,b)=>asc?cmp(a,b,key):cmp(b,a,key)),
        size=+pageSize.value||10,
        total=rows.length,
        max=Math.max(1,Math.ceil(total/size));
  if(page>max) page=max;
  const slice=rows.slice((page-1)*size,(page-1)*size+size);

  tbody.innerHTML = slice.length ? slice.map(r=>`
    <tr class="data">
      <td class="mono nowrap">#${r.id_cat??''}</td>
      <td><span class="badge">${(r.nombre_cat??'').replace(/</g,'&lt;')}</span></td>
      <td class="right nowrap">
        <button class="btn ghost" onclick="editOpen(${Number(r.id_cat)})">Editar</button>
        <button class="btn danger" onclick="delCat(${Number(r.id_cat)})">Eliminar</button>
      </td>
    </tr>`).join('')
  : `<tr><td colspan="3"><div class="empty">Sin resultados</div></td></tr>`;

  info.textContent=`Página ${page}/${max}`;
  prev.disabled = page<=1;
  next.disabled = page>=max;
}

async function load(){
  setLoading();
  const r = await fetch(API_LIST,{cache:'no-store'});
  const j = await safeJson(r);
  data = norm(j);
  render();
}

// Sort headers
document.querySelectorAll('thead th[data-key]').forEach(th=>{
  th.addEventListener('click', ()=>{
    const k=th.getAttribute('data-key');
    if(key===k) asc=!asc; else { key=k; asc=true; }
    document.querySelectorAll('thead th[data-key]').forEach(t=>{
      t.textContent = t.textContent.replace(/[▴▾]/g,'').trim();
      if(t.getAttribute('data-key')===key) t.textContent += ' ' + (asc?'▴':'▾');
    });
    render();
  });
});

buscar.oninput = ()=>{ page=1; render(); };
pageSize.onchange = ()=>{ page=1; render(); };
prev.onclick = ()=>{ if(page>1){ page--; render(); } };
next.onclick = ()=>{ page++; render(); };

// Add
document.getElementById('formAdd').addEventListener('submit', async e=>{
  e.preventDefault();
  const f = new FormData(e.target);
  const n = (f.get('nombre_cat')||'').trim();
  if(n.length<2){ toast('Nombre muy corto','Mínimo 2 caracteres','error'); return; }
  const r = await fetch(API_ADD,{method:'POST', body:f});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo guardar', j.message||'', 'error'); }
  else { toast('Categoría guardada'); e.target.reset(); load(); }
});

// Edit
window.editOpen = id=>{
  const item = data.find(x=>+x.id_cat===+id);
  if(!item){ toast('No encontrado','', 'error'); return; }
  formE.edit_id.value = id;
  formE.edit_nombre.value = item.nombre_cat || '';
  dlg.showModal();
};
document.getElementById('btnCancelE').onclick = ()=> dlg.close();
document.getElementById('btnSaveE').onclick = async ()=>{
  const f = new FormData(formE);
  const n = (f.get('nuevo_nombre_categoria')||'').trim();
  if(n.length<2){ toast('Nombre muy corto','Mínimo 2 caracteres','error'); return; }
  const r = await fetch(API_EDIT,{method:'POST', body:f});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo editar', j.message||'', 'error'); }
  else { toast('Categoría actualizada'); dlg.close(); load(); }
};

// Delete
window.delCat = async id=>{
  if(!confirm('¿Eliminar categoría?')) return;
  const f = new FormData();
  f.set('id_cat', id);
  const r = await fetch(API_DEL,{method:'POST', body:f});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo eliminar', j.message||'', 'error'); }
  else { toast('Eliminada'); load(); }
};

load();
</script>
</body>
</html>
