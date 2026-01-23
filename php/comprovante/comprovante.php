<?php
// comprovante.php - Mismo estilo unificado (sin Bootstrap)
// Endpoints (dejamos nombres tal cual tienes):
// Listar   GET  register_comprovante_bi.php  -> array [{id_comprovante,nombre_comprovante}] o {data:[...]}
// Agregar  POST register_comprovante_bi.php  -> nombre_comprovante
// Editar   POST editar.php                   -> id_comprovante, nuevo_nombre_comprovante
// Eliminar POST eliminar.php                 -> id_comprovante
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Comprobantes</title>

  <!-- 🔗 Theme global (ajusta si la carpeta cambia) -->
<link rel="stylesheet" href="../css/app-forms.css?v=20260103-1">

  <!-- Layout mínimo de página -->
  <style>
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      display:flex; align-items:flex-start; justify-content:center;
      padding:24px;
    }
    .app{ width:min(1100px,100%); display:grid; gap:18px }
    .header{ display:flex; align-items:center; justify-content:space-between }
    .title{ font-weight:800; font-size: clamp(18px, 2.2vw, 26px) }
    .grid{ display:grid; grid-template-columns: 380px 1fr; gap:18px }
    @media (max-width: 920px){ .grid{ grid-template-columns: 1fr } }
  </style>
</head>
<body>
<div class="app" role="application" aria-label="Comprobantes">
  <div class="header"><div class="title">Comprobantes</div></div>

  <div class="grid">
    <!-- Panel izquierdo: formulario -->
    <div class="form-card">
      <h2 class="form-title">Agregar comprobante</h2>
      <form id="formAdd" class="app-form" autocomplete="off" novalidate>
        <div class="field">
          <label class="label" for="nombre">Nombre</label>
          <div class="control">
            <input class="input" id="nombre" name="nombre_comprovante" placeholder="Ej: Factura Contado" minlength="2" required>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn" type="submit">Guardar</button>
          <button type="reset" class="btn ghost">Limpiar</button>
        </div>
      </form>
    </div>

    <!-- Panel derecho: listado -->
    <div class="table-wrap" aria-labelledby="titulo-lista">
      <div class="panel">
        <h2 class="form-title" id="titulo-lista">Listado</h2>

        <div class="grid-2">
          <div class="field">
            <label class="label" for="buscar">Buscar nombre</label>
            <div class="control has-icon">
              <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
              </svg>
              <input class="input" id="buscar" placeholder="Ej: Ticket, Nota, Factura…">
            </div>
          </div>

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
        <table class="table" aria-describedby="titulo-lista">
          <thead>
            <tr>
              <th data-key="id_comprovante" class="nowrap">ID ▾</th>
              <th data-key="nombre_comprovante">Nombre</th>
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
  <div class="dialog-header">Editar comprobante</div>
  <div class="dialog-body">
    <form id="formEdit" class="app-form" novalidate>
      <input type="hidden" id="edit_id" name="id_comprovante">
      <div class="field">
        <label class="label" for="edit_nombre">Nombre</label>
        <input class="input" id="edit_nombre" name="nuevo_nombre_comprovante" minlength="2" required>
      </div>
    </form>
  </div>
  <div class="dialog-actions">
    <button class="btn ghost" id="cancelE" type="button">Cancelar</button>
    <button class="btn" id="saveE" type="button">Guardar</button>
  </div>
</dialog>

<script>
const API_LIST='register_comprovante_bi.php',
      API_ADD ='register_comprovante_bi.php',
      API_EDIT='editar.php',
      API_DEL ='eliminar.php';

// DOM
const tbody    = document.getElementById('tbody');
const buscar   = document.getElementById('buscar');
const pageSize = document.getElementById('pageSize');
const info     = document.getElementById('info');
const prev     = document.getElementById('prev');
const next     = document.getElementById('next');

const dlg   = document.getElementById('dlgEdit');
const formE = document.getElementById('formEdit');

let data=[], page=1, key='id_comprovante', asc=true;

// UI helpers
const toast=(t,m='',type='success')=>{
  const w=document.getElementById('toasts'), el=document.createElement('div');
  el.className='toast '+(type||'');
  el.innerHTML=`<div><div class="t-title">${t}</div>${m?`<div class="t-msg">${m}</div>`:''}</div>`;
  w.appendChild(el);
  setTimeout(()=>{ el.style.opacity=0; setTimeout(()=>w.removeChild(el),300) },3200);
};
const safeJson=async r=>{try{return await r.json()}catch{return null}};
const norm=resp=>Array.isArray(resp)?resp:(resp && Array.isArray(resp.data)?resp.data:[]);
const setLoad=()=>{tbody.innerHTML=Array.from({length:6}).map(()=>`
  <tr>
    <td><div class="skeleton"></div></td>
    <td><div class="skeleton" style="width:60%"></div></td>
    <td class="right"><div class="skeleton" style="width:40%; margin-left:auto"></div></td>
  </tr>`).join('')};
const cmp=(a,b,k)=>{
  const A=(a[k]??'').toString().toLowerCase(), B=(b[k]??'').toString().toLowerCase();
  if(!isNaN(+a[k]) && !isNaN(+b[k])) return +a[k]-+b[k];
  return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'});
};
const filt=()=>{
  const q=(buscar.value||'').toLowerCase().trim();
  return q? data.filter(x=>(x.nombre_comprovante||'').toLowerCase().includes(q)) : data.slice();
};

function render(){
  const rows=filt().sort((a,b)=>asc?cmp(a,b,key):cmp(b,a,key)),
        size=+pageSize.value||10,
        total=rows.length,
        max=Math.max(1,Math.ceil(total/size));
  if(page>max) page=max;

  const sl = rows.slice((page-1)*size,(page-1)*size+size);
  tbody.innerHTML = sl.length ? sl.map(r=>`
    <tr class="data">
      <td class="mono nowrap">#${r.id_comprovante??''}</td>
      <td><span class="badge">${(r.nombre_comprovante??'').replace(/</g,'&lt;')}</span></td>
      <td class="right nowrap">
        <button class="btn ghost"  onclick="openE(${Number(r.id_comprovante)})">Editar</button>
        <button class="btn danger" onclick="delRow(${Number(r.id_comprovante)})">Eliminar</button>
      </td>
    </tr>`).join('')
  : `<tr><td colspan="3"><div class="empty">Sin resultados</div></td></tr>`;

  info.textContent = `Página ${page}/${max}`;
  prev.disabled = page<=1;
  next.disabled = page>=max;
}

async function load(){
  setLoad();
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

buscar.oninput   = ()=>{ page=1; render(); };
pageSize.onchange= ()=>{ page=1; render(); };
prev.onclick     = ()=>{ if(page>1){ page--; render(); } };
next.onclick     = ()=>{ page++; render(); };

// Add
document.getElementById('formAdd').addEventListener('submit', async e=>{
  e.preventDefault();
  const f = new FormData(e.target);
  const n = (f.get('nombre_comprovante')||'').trim();
  if(n.length<2){ toast('Nombre muy corto','Mínimo 2 caracteres','error'); return; }

  const r = await fetch(API_ADD,{method:'POST', body:f});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo guardar', j.message||'', 'error');
  }else{
    toast('Comprobante guardado');
    e.target.reset();
    load();
  }
});

// Edit
window.openE = (id)=>{
  const it = data.find(x=>+x.id_comprovante===+id);
  if(!it){ toast('No encontrado','', 'error'); return; }
  formE.edit_id.value    = it.id_comprovante;
  formE.edit_nombre.value= it.nombre_comprovante || '';
  dlg.showModal();
};
document.getElementById('cancelE').onclick = ()=> dlg.close();
document.getElementById('saveE').onclick = async ()=>{
  const f = new FormData(formE);
  const n = (f.get('nuevo_nombre_comprovante')||'').trim();
  if(n.length<2){ toast('Nombre muy corto','Mínimo 2 caracteres','error'); return; }

  const r = await fetch(API_EDIT,{method:'POST', body:f});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo editar', j.message||'', 'error');
  }else{
    toast('Actualizado');
    dlg.close();
    load();
  }
};

// Delete
window.delRow = async id=>{
  if(!confirm('¿Eliminar comprobante?')) return;
  const f = new FormData(); f.set('id_comprovante', id);
  const r = await fetch(API_DEL,{method:'POST', body:f});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo eliminar', j.message||'', 'error');
  }else{
    toast('Eliminado');
    load();
  }
};

// Init
load();
</script>
</body>
</html>
