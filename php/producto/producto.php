<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Productos</title>

  <!-- 🔗 Theme global -->
<link rel="stylesheet" href="../css/app-forms.css?v=20260103-1">

  <!-- Layout mínimo -->
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
    .grid{ display:grid; grid-template-columns: 440px 1fr; gap:18px }
    @media (max-width: 1024px){ .grid{ grid-template-columns: 1fr } }
    .img-cell img{ height:42px; width:auto; border-radius:8px; object-fit:cover; border:1px solid var(--border) }
    .star{ font-size:18px; line-height:1 }
  </style>
</head>
<body>
<div class="app" role="application" aria-label="Productos">
  <div class="header"><div class="title">Lista de Productos</div></div>

  <div class="grid">
    <!-- Panel izquierdo: formulario de alta -->
    <div class="form-card">
      <h2 class="form-title">Agregar Producto</h2>
      <form id="formAdd" class="app-form" autocomplete="off" enctype="multipart/form-data" novalidate>
        <div class="field">
          <label class="label" for="nombre_pro">Nombre del Producto</label>
          <input class="input" id="nombre_pro" name="nombre_pro" required>
        </div>

        <div class="grid-2">
          <div class="field">
            <label class="label" for="codigo_barra_pro">Código de Barra</label>
            <input class="input" id="codigo_barra_pro" name="codigo_barra_pro" required>
          </div>
          <div class="field">
            <label class="label" for="uni_caja_pro">Unidades por Caja</label>
            <input class="input" id="uni_caja_pro" name="uni_caja_pro" type="number" required>
          </div>
        </div>

        <div class="grid-2">
          <div class="field">
            <label class="label" for="iva_pro">IVA</label>
            <input class="input" id="iva_pro" name="iva_pro" type="number" step="0.01" required>
          </div>
          <div class="field">
            <label class="label" for="id_cat">ID Categoría</label>
            <input class="input" id="id_cat" name="id_cat" type="number" required>
          </div>
        </div>

        <div class="field">
          <label class="label" for="imagen">Imagen del producto</label>
          <input class="input" id="imagen" name="imagen" type="file" accept="image/*">
          <div class="helper">Se sube a tu endpoint junto con el resto del formulario.</div>
        </div>

        <div class="field">
          <label class="label">Favorito</label>
          <label class="switch">
            <input type="checkbox" id="favorito" name="favorito">
            <span>Marcar como favorito</span>
          </label>
        </div>

        <div class="form-actions">
          <button class="btn" type="submit">Agregar</button>
          <button class="btn ghost" type="reset">Limpiar</button>
        </div>
      </form>
    </div>

    <!-- Panel derecho: listado -->
    <div class="table-wrap" aria-labelledby="titulo-lista">
      <div class="panel">
        <h2 class="form-title" id="titulo-lista">Listado</h2>

        <div class="grid-2">
          <div class="field">
            <label class="label" for="buscar">Buscar</label>
            <div class="control has-icon">
              <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
              </svg>
              <input class="input" id="buscar" placeholder="Nombre, código, categoría, IVA…">
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
              <th data-key="id_pro" class="nowrap">ID ▾</th>
              <th data-key="nombre_pro">Nombre</th>
              <th data-key="codigo_barra_pro">Código de Barra</th>
              <th data-key="uni_caja_pro">Unid/Caja</th>
              <th data-key="iva_pro">IVA</th>
              <th data-key="id_cat">ID Cat</th>
              <th data-key="favorito" class="nowrap">Favorito</th>
              <th class="nowrap">Imagen</th>
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

<!-- Modal Editar -->
<dialog id="dlgEdit">
  <div class="dialog-header">Editar Producto</div>
  <div class="dialog-body">
    <form id="formEdit" class="app-form" enctype="multipart/form-data" novalidate>
      <input type="hidden" id="edit_id" name="id_pro">
      <div class="field">
        <label class="label" for="edit_nombre">Nombre</label>
        <input class="input" id="edit_nombre" name="nombre_pro" required>
      </div>

      <div class="grid-2">
        <div class="field">
          <label class="label" for="edit_codigo">Código de Barra</label>
          <input class="input" id="edit_codigo" name="codigo_barra_pro" required>
        </div>
        <div class="field">
          <label class="label" for="edit_unidades">Unidades por Caja</label>
          <input class="input" id="edit_unidades" name="uni_caja_pro" type="number" required>
        </div>
      </div>

      <div class="grid-2">
        <div class="field">
          <label class="label" for="edit_iva">IVA</label>
          <input class="input" id="edit_iva" name="iva_pro" type="number" step="0.01" required>
        </div>
        <div class="field">
          <label class="label" for="edit_cat">ID Categoría</label>
          <input class="input" id="edit_cat" name="id_cat" type="number" required>
        </div>
      </div>

      <div class="field">
        <label class="label" for="edit_imagen">Imagen (opcional)</label>
        <input class="input" id="edit_imagen" name="imagen" type="file" accept="image/*">
      </div>

      <div class="field">
        <label class="label">Favorito</label>
        <label class="switch">
          <input type="checkbox" id="edit_favorito" name="favorito">
          <span>Marcar como favorito</span>
        </label>
      </div>
    </form>
  </div>
  <div class="dialog-actions">
    <button class="btn ghost" id="cancelE" type="button">Cancelar</button>
    <button class="btn" id="saveE" type="button">Guardar</button>
  </div>
</dialog>

<script>
// Endpoints
const API_LIST = 'register_producto_bi.php';   // GET -> array [{...}]  (o {data:[...]} si así responde)
const API_ADD  = 'register_producto_bi.php';   // POST -> campos del form + imagen + favorito
const API_GET  = 'get_producto.php?id=';       // GET  -> id_pro
const API_EDIT = 'editar.php';                 // POST -> id_pro + campos (y opcional imagen)
const API_DEL  = 'eliminar.php?eliminar=';     // GET  -> ?eliminar=ID

// Ruta base de imágenes para mostrar
const IMG_BASE = '../img/productos/';
const IMG_FALLBACK = 'sin_imagen.jpg';

// DOM
const tbody    = document.getElementById('tbody');
const buscar   = document.getElementById('buscar');
const pageSize = document.getElementById('pageSize');
const info     = document.getElementById('info');
const prev     = document.getElementById('prev');
const next     = document.getElementById('next');

const dlg   = document.getElementById('dlgEdit');
const formE = document.getElementById('formEdit');

let data = [];
let page = 1;
let key  = 'id_pro';
let asc  = true;

// Helpers UI
const toast=(t,m='',type='success')=>{
  const w=document.getElementById('toasts'), el=document.createElement('div');
  el.className='toast '+(type||'');
  el.innerHTML=`<div><div class="t-title">${t}</div>${m?`<div class="t-msg">${m}</div>`:''}</div>`;
  w.appendChild(el);
  setTimeout(()=>{ el.style.opacity=0; setTimeout(()=>w.removeChild(el),300) },3200);
};
const safeJson=async r=>{try{return await r.json()}catch{return null}};
const norm = resp => Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ? resp.data : []);
const cmp  = (a,b,k)=>{
  const A=(a[k]??'').toString().toLowerCase(), B=(b[k]??'').toString().toLowerCase();
  if(!isNaN(+a[k]) && !isNaN(+b[k])) return +a[k]-+b[k];
  return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'});
};
const setLoad=()=>{tbody.innerHTML=Array.from({length:6}).map(()=>`
  <tr>
    <td><div class="skeleton"></div></td>
    <td><div class="skeleton" style="width:60%"></div></td>
    <td><div class="skeleton" style="width:40%"></div></td>
    <td><div class="skeleton" style="width:30%"></div></td>
    <td><div class="skeleton" style="width:30%"></div></td>
    <td><div class="skeleton" style="width:20%"></div></td>
    <td><div class="skeleton" style="width:20%"></div></td>
    <td><div class="skeleton" style="width:42px"></div></td>
    <td class="right"><div class="skeleton" style="width:40%; margin-left:auto"></div></td>
  </tr>`).join('')};

const isFav = v => (v===true || v==='1' || v===1 || v==='t');

const filt=()=>{
  const q=(buscar.value||'').toLowerCase().trim();
  if(!q) return data.slice();
  return data.filter(x=>{
    const t = `${x.id_pro||''} ${x.nombre_pro||''} ${x.codigo_barra_pro||''} ${x.uni_caja_pro||''} ${x.iva_pro||''} ${x.id_cat||''}`.toLowerCase();
    return t.includes(q);
  });
};

function render(){
  const rows=filt().sort((a,b)=>asc?cmp(a,b,key):cmp(b,a,key));
  const size=+pageSize.value||10, total=rows.length, max=Math.max(1,Math.ceil(total/size));
  if(page>max) page=max;
  const sl=rows.slice((page-1)*size,(page-1)*size+size);

  tbody.innerHTML = sl.length ? sl.map(r=>{
    const fav = isFav(r.favorito);
    const img = (r.imagen_pro && r.imagen_pro.trim()) ? r.imagen_pro.trim() : IMG_FALLBACK;
    return `
      <tr class="data">
        <td class="mono nowrap">#${r.id_pro??''}</td>
        <td><span class="badge">${(r.nombre_pro??'').replace(/</g,'&lt;')}</span></td>
        <td>${(r.codigo_barra_pro??'').replace(/</g,'&lt;')}</td>
        <td>${r.uni_caja_pro??''}</td>
        <td>${r.iva_pro??''}</td>
        <td>${r.id_cat??''}</td>
        <td class="nowrap"><span class="star">${fav ? '⭐️ Sí' : '—'}</span></td>
        <td class="img-cell"><img src="${IMG_BASE}${img}" alt="Imagen"></td>
        <td class="right nowrap">
          <button class="btn ghost" onclick="openEdit(${Number(r.id_pro)})">Editar</button>
          <button class="btn danger" onclick="delRow(${Number(r.id_pro)})">Eliminar</button>
        </td>
      </tr>`;
  }).join('') : `<tr><td colspan="9"><div class="empty">Sin resultados</div></td></tr>`;

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
  const fd = new FormData(e.target);
  fd.set('favorito', document.getElementById('favorito').checked ? '1' : '0');

  // Validaciones mínimas
  if(!(fd.get('nombre_pro')||'').trim()){ toast('Falta el nombre','','error'); return; }

  const r = await fetch(API_ADD,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo guardar', j.message||'', 'error');
  }else{
    toast('Producto guardado');
    e.target.reset();
    load();
  }
});

// Edit open
window.openEdit = async (id)=>{
  try{
    const r = await fetch(API_GET + encodeURIComponent(id));
    const it = await safeJson(r);
    if(!it || !it.id_pro) throw new Error('sin datos');
    formE.edit_id.value      = it.id_pro;
    formE.edit_nombre.value  = it.nombre_pro || '';
    formE.edit_codigo.value  = it.codigo_barra_pro || '';
    formE.edit_unidades.value= it.uni_caja_pro || '';
    formE.edit_iva.value     = it.iva_pro || '';
    formE.edit_cat.value     = it.id_cat || '';
    document.getElementById('edit_favorito').checked = isFav(it.favorito);
  }catch(e){
    const it = data.find(x=>+x.id_pro===+id);
    if(!it){ toast('No encontrado','', 'error'); return; }
    formE.edit_id.value      = it.id_pro;
    formE.edit_nombre.value  = it.nombre_pro || '';
    formE.edit_codigo.value  = it.codigo_barra_pro || '';
    formE.edit_unidades.value= it.uni_caja_pro || '';
    formE.edit_iva.value     = it.iva_pro || '';
    formE.edit_cat.value     = it.id_cat || '';
    document.getElementById('edit_favorito').checked = isFav(it.favorito);
  }
  dlg.showModal();
};
document.getElementById('cancelE').onclick = ()=> dlg.close();

// Save edit
document.getElementById('saveE').onclick = async ()=>{
  const fd = new FormData(formE);
  fd.set('favorito', document.getElementById('edit_favorito').checked ? '1' : '0');

  if(!(fd.get('nombre_pro')||'').trim()){ toast('Falta el nombre','','error'); return; }

  const r = await fetch(API_EDIT,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo editar', j.message||'', 'error');
  }else{
    toast('Producto actualizado');
    dlg.close();
    load();
  }
};

// Delete
window.delRow = async id=>{
  if(!confirm('¿Eliminar este producto?')) return;
  const r = await fetch(API_DEL + encodeURIComponent(id), {method:'GET'});
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
