<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Detalles de Compra</title>

  <!-- 🔗 Theme global -->
  <link rel="stylesheet" href="../css/app-forms.css?v=20260127-std">

  <!-- Layout mínimo -->
</head>
<body>
<div class="app" role="application" aria-label="Detalles de Compra">
  <div class="header"><div class="title">Lista de Detalles de Compra</div></div>

  <div class="grid">
    <!-- Panel izquierdo: formulario -->
    <div class="form-card">
      <h2 class="form-title">Agregar Detalle de Compra</h2>
      <form id="formAdd" class="app-form" autocomplete="off" novalidate>
        <div class="field">
          <label class="label" for="subtotal_comp_det">Subtotal</label>
          <input class="input" type="number" step="0.01" id="subtotal_comp_det" name="subtotal_comp_det" required>
        </div>
        <div class="grid-2">
          <div class="field">
            <label class="label" for="id_com">ID Compra</label>
            <input class="input" type="number" id="id_com" name="id_com" required>
          </div>
          <div class="field">
            <label class="label" for="id_pro">ID Producto</label>
            <input class="input" type="number" id="id_pro" name="id_pro" required>
          </div>
        </div>
        
      </form>
      <div class="form-actions">
          <button class="btn" type="submit">Agregar detalle</button>
          <button class="btn ghost" type="reset">Limpiar</button>
        </div>
    </div>

    <!-- Panel derecho: listado -->
    <div class="table-wrap" aria-labelledby="titulo-lista">
      <div class="panel">
        <h2 class="form-title" id="titulo-lista">Listado</h2>
        <div class="grid-2">
          <div class="field">
            <label class="label" for="buscar">Buscar por ID detalle / compra / producto</label>
            <div class="control has-icon">
              <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
              </svg>
              <input class="input" id="buscar" placeholder="Ej: 15, #compra 2, #prod 10">
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
              <th data-key="id_comp_det" class="nowrap">ID ▾</th>
              <th data-key="subtotal_comp_det">Subtotal</th>
              <th data-key="id_com">ID Compra</th>
              <th data-key="id_pro">ID Producto</th>
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
  <div class="dialog-header">Editar Detalle</div>
  <div class="dialog-body">
    <form id="formEdit" class="app-form" novalidate>
      <input type="hidden" id="edit_id" name="id_comp_det">
      <div class="field">
        <label class="label" for="edit_subtotal">Subtotal</label>
        <input class="input" type="number" step="0.01" id="edit_subtotal" name="subtotal_comp_det" required>
      </div>
      <div class="grid-2">
        <div class="field">
          <label class="label" for="edit_id_com">ID Compra</label>
          <input class="input" type="number" id="edit_id_com" name="id_com" required>
        </div>
        <div class="field">
          <label class="label" for="edit_id_pro">ID Producto</label>
          <input class="input" type="number" id="edit_id_pro" name="id_pro" required>
        </div>
      </div>
    </form>
  </div>
  <div class="dialog-actions">
    <button class="btn ghost" id="btnCancelE" type="button">Cancelar</button>
    <button class="btn" id="btnSaveE" type="button">Guardar</button>
  </div>
</dialog>

<script>
const API_LIST = 'register_compra_detalle_bi.php';
const API_ADD  = 'register_compra_detalle_bi.php';
const API_EDIT = 'editar.php';     // id_comp_det, subtotal_comp_det, id_com, id_pro
const API_DEL  = 'eliminar.php';   // id_comp_det

// DOM
const tbody = document.getElementById('tbody');
const buscar = document.getElementById('buscar');
const pageSize = document.getElementById('pageSize');
const info = document.getElementById('info');
const prev = document.getElementById('prev');
const next = document.getElementById('next');

const dlg = document.getElementById('dlgEdit');
const formE = document.getElementById('formEdit');

let data = [];
let page = 1;
let key = 'id_comp_det';
let asc = true;

// Helpers UI
const toast=(t,m='',type='success')=>{
  const w=document.getElementById('toasts'), el=document.createElement('div');
  el.className='toast '+(type||'');
  el.innerHTML=`<div><div class="t-title">${t}</div>${m?`<div class="t-msg">${m}</div>`:''}</div>`;
  w.appendChild(el);
  setTimeout(()=>{ el.style.opacity=0; setTimeout(()=>w.removeChild(el),300) },3200);
};
const safeJson=async r=>{try{return await r.json()}catch{return null}};
const cmp=(a,b,k)=>{
  const A=(a[k]??'').toString().toLowerCase(), B=(b[k]??'').toString().toLowerCase();
  if(!isNaN(+a[k]) && !isNaN(+b[k])) return +a[k]-+b[k];
  return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'});
};
const setLoad=()=>{tbody.innerHTML=Array.from({length:6}).map(()=>`
  <tr>
    <td><div class="skeleton"></div></td>
    <td><div class="skeleton" style="width:60%"></div></td>
    <td><div class="skeleton" style="width:40%"></div></td>
    <td><div class="skeleton" style="width:40%"></div></td>
    <td class="right"><div class="skeleton" style="width:30%; margin-left:auto"></div></td>
  </tr>`).join('')};

// Filtro: soporta #compra X y #prod Y además de texto libre
const filt=()=>{
  const q=(buscar.value||'').toLowerCase().trim();
  if(!q) return data.slice();
  const mCompra = q.match(/#?compra\s*(\d+)/);
  const mProd   = q.match(/#?prod(ucto)?\s*(\d+)/);
  return data.filter(x=>{
    const hitTxt = `${x.id_comp_det||''} ${x.id_com||''} ${x.id_pro||''} ${x.subtotal_comp_det||''}`.toLowerCase().includes(q);
    const hitCompra = mCompra ? (Number(x.id_com)===Number(mCompra[1])) : true;
    const hitProd   = mProd   ? (Number(x.id_pro)===Number(mProd[2]||mProd[1])) : true;
    return hitTxt && hitCompra && hitProd;
  });
};

function render(){
  const rows=filt().sort((a,b)=>asc?cmp(a,b,key):cmp(b,a,key));
  const size=+pageSize.value||10, total=rows.length, max=Math.max(1,Math.ceil(total/size));
  if(page>max) page=max;
  const sl=rows.slice((page-1)*size,(page-1)*size+size);

  tbody.innerHTML = sl.length ? sl.map(r=>`
    <tr class="data">
      <td class="mono nowrap">#${r.id_comp_det??''}</td>
      <td>${r.subtotal_comp_det??''}</td>
      <td>${r.id_com??''}</td>
      <td>${r.id_pro??''}</td>
      <td class="right nowrap">
        <button class="btn ghost" onclick="editOpen(${Number(r.id_comp_det)})">Editar</button>
        <button class="btn danger" onclick="delRow(${Number(r.id_comp_det)})">Eliminar</button>
      </td>
    </tr>`).join('')
  : `<tr><td colspan="5"><div class="empty">Sin resultados</div></td></tr>`;

  info.textContent=`Página ${page}/${max}`;
  prev.disabled=page<=1; next.disabled=page>=max;
}

async function load(){
  setLoad();
  const r = await fetch(API_LIST,{method:'GET', cache:'no-store'});
  const j = await safeJson(r);
  data = Array.isArray(j) ? j : (j && Array.isArray(j.data) ? j.data : []);
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
  const fd=new FormData(e.target);
  const subtotal=String(fd.get('subtotal_comp_det')||'').trim();
  const idCom   =String(fd.get('id_com')||'').trim();
  const idPro   =String(fd.get('id_pro')||'').trim();
  if(!subtotal||!idCom||!idPro){ toast('Completa los campos','', 'error'); return; }

  const r = await fetch(API_ADD,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.status==='success' || (j && j.success!==false)){
    toast('Detalle guardado');
    e.target.reset();
    load();
  }else{
    toast('Error al guardar', (j&&j.message)||'', 'error');
  }
});

// Edit
window.editOpen = (id)=>{
  const it = data.find(d=>Number(d.id_comp_det)===Number(id));
  if(!it){ toast('No encontrado','', 'error'); return; }
  formE.edit_id.value = it.id_comp_det;
  formE.edit_subtotal.value = it.subtotal_comp_det ?? '';
  formE.edit_id_com.value = it.id_com ?? '';
  formE.edit_id_pro.value = it.id_pro ?? '';
  dlg.showModal();
};
document.getElementById('btnCancelE').onclick = ()=> dlg.close();
document.getElementById('btnSaveE').onclick = async ()=>{
  const fd=new FormData(formE);
  const r = await fetch(API_EDIT,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo editar', j.message||'', 'error');
  }else{
    toast('Detalle actualizado');
    dlg.close();
    load();
  }
};

// Delete
window.delRow = async (id)=>{
  if(!confirm('¿Eliminar detalle?')) return;
  const fd = new FormData(); fd.set('id_comp_det', id);
  const r = await fetch(API_DEL,{method:'POST', body: fd});
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
