<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Monedas</title>

  <!-- 🔗 Theme global -->
  <link rel="stylesheet" href="../css/app-forms.css">

  <!-- Layout mínimo -->
</head>
<body>
<div class="app" role="application" aria-label="Monedas">
  <div class="header"><div class="title">Cotización de Monedas</div></div>

  <div class="grid">
    <!-- Panel izquierdo: formulario -->
    <div class="form-card">
      <h2 class="form-title">Agregar Cotización</h2>
      <form id="formAdd" class="app-form" autocomplete="off" novalidate>
        <div class="field">
          <label class="label" for="guarani">Guaraní (base)</label>
          <input class="input" id="guarani" name="guarani" type="number" step="0.0001" value="1.0000" readonly>
        </div>
        <div class="field">
          <label class="label" for="real">Real (BRL)</label>
          <input class="input" id="real" name="real" type="number" step="0.0001" required placeholder="Ej: 1400.0000">
        </div>
        <div class="field">
          <label class="label" for="dolar">Dólar (USD)</label>
          <input class="input" id="dolar" name="dolar" type="number" step="0.0001" required placeholder="Ej: 7300.0000">
        </div>
        <div class="field">
          <label class="label" for="estado">Estado</label>
          <select class="select" id="estado" name="estado" required>
            <option value="ACTIVO" selected>ACTIVO</option>
            <option value="INACTIVO">INACTIVO</option>
          </select>
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
              <input class="input" id="buscar" placeholder="Nombre, tasa o ID…">
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
              <th data-key="id_mon" class="nowrap">ID ▾</th>
              <th data-key="guarani" class="right">Guaraní</th>
              <th data-key="real" class="right">Real</th>
              <th data-key="dolar" class="right">Dólar</th>
              <th data-key="estado">Estado</th>
              <th data-key="fecha_inicio" class="nowrap">Inicio</th>
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
  <div class="dialog-header">Editar Cotización</div>
  <div class="dialog-body">
    <form id="formEdit" class="app-form" novalidate>
      <input type="hidden" id="edit_id" name="id_mon">
      
      <div class="field">
        <label class="label" for="edit_guarani">Guaraní (base)</label>
        <input class="input" id="edit_guarani" name="guarani" type="number" step="0.0001" value="1.0000" readonly>
      </div>
      <div class="field">
        <label class="label" for="edit_real">Real (BRL)</label>
        <input class="input" id="edit_real" name="real" type="number" step="0.0001" required>
      </div>
      <div class="field">
        <label class="label" for="edit_dolar">Dólar (USD)</label>
        <input class="input" id="edit_dolar" name="dolar" type="number" step="0.0001" required>
      </div>
      <div class="field">
        <label class="label" for="edit_estado">Estado</label>
        <select class="select" id="edit_estado" name="estado" required>
          <option value="ACTIVO">ACTIVO</option>
          <option value="INACTIVO">INACTIVO</option>
        </select>
      </div>

    </form>
     <div class="dialog-actions">
    <button class="btn ghost" id="cancelE" type="button">Cancelar</button>
    <button class="btn" id="saveE" type="button">Guardar</button>
  </div>
  </div>
 
</dialog>

<script>
// Endpoints
const API_LIST = 'register_moneda_bi.php'; // GET -> array [{id_mon, guarani, real, dolar, estado, fecha_inicio}]
const API_ADD  = 'register_moneda_bi.php'; // POST -> real, dolar, estado (guarani base)
const API_EDIT = 'editar.php';             // POST -> id_mon, real, dolar, estado
const API_DEL  = 'eliminar.php?eliminar='; // GET  -> ?eliminar=ID

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
let key  = 'estado';
let asc  = true;

// UI helpers
const toast=(t,m='',type='success')=>{
  const w=document.getElementById('toasts'), el=document.createElement('div');
  el.className='toast '+(type||'');
  el.innerHTML=`<div><div class="t-title">${t}</div>${m?`<div class="t-msg">${m}</div>`:''}</div>`;
  w.appendChild(el);
  setTimeout(()=>{ el.style.opacity=0; setTimeout(()=>w.removeChild(el),300) },3200);
};
const safeJson=async r=>{try{return await r.json()}catch{return null}};
const cmp=(a,b,k)=>{
  // Prioridad: ACTIVO primero cuando se ordena por "estado"
  if(k==='estado'){
    const ra = ((a.estado||'').toString().toUpperCase()==='ACTIVO') ? 0 : 1;
    const rb = ((b.estado||'').toString().toUpperCase()==='ACTIVO') ? 0 : 1;
    if(ra!==rb) return ra-rb;
    // desempate: más nuevo primero por id
    const ia = +a.id_mon||0, ib = +b.id_mon||0;
    return ib-ia;
  }

  const A=(a[k]??'').toString().toLowerCase(), B=(b[k]??'').toString().toLowerCase();
  if(!isNaN(+a[k]) && !isNaN(+b[k])) return +a[k]-+b[k];
  return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'});
};
const setLoad=()=>{tbody.innerHTML=Array.from({length:6}).map(()=>`
  <tr>
    <td><div class="skeleton"></div></td>
    <td class="right"><div class="skeleton" style="width:55%; margin-left:auto"></div></td>
    <td class="right"><div class="skeleton" style="width:55%; margin-left:auto"></div></td>
    <td class="right"><div class="skeleton" style="width:55%; margin-left:auto"></div></td>
    <td><div class="skeleton" style="width:60%"></div></td>
    <td><div class="skeleton" style="width:65%"></div></td>
    <td class="right"><div class="skeleton" style="width:40%; margin-left:auto"></div></td>
  </tr>`).join('')};

const filt=()=>{
  const q=(buscar.value||'').toLowerCase().trim();
  if(!q) return data.slice();
  return data.filter(x=>{
    const t = `${x.id_mon||''} ${x.estado||''} ${x.real||''} ${x.dolar||''} ${x.fecha_inicio||''}`.toLowerCase();
    return t.includes(q);
  });
};


function applySortArrow(){
  document.querySelectorAll('thead th[data-key]').forEach(t=>{
    const k=t.getAttribute('data-key');
    t.textContent = t.textContent.replace(/[▴▾]/g,'').trim();
    if(k===key) t.textContent += ' ' + (asc?'▴':'▾');
  });
}

function render(){
  const rows=filt().sort((a,b)=>asc?cmp(a,b,key):cmp(b,a,key));
  const size=+pageSize.value||10, total=rows.length, max=Math.max(1,Math.ceil(total/size));
  if(page>max) page=max;
  const sl=rows.slice((page-1)*size,(page-1)*size+size);

  tbody.innerHTML = sl.length ? sl.map(r=>`
    <tr class="data">
      <td class="mono nowrap">#${r.id_mon??''}</td>
      <td class="right">${r.guarani??''}</td>
      <td class="right">${r.real??''}</td>
      <td class="right">${r.dolar??''}</td>
      <td><span class="badge">${(r.estado??'').replace(/</g,'&lt;')}</span></td>
      <td class="mono nowrap">${(r.fecha_inicio??'').toString().slice(0,16).replace('T',' ')}</td>
      <td class="right nowrap">
        <button class="btn ghost" onclick="openEdit(${Number(r.id_mon)})">Editar</button>
        <button class="btn danger" onclick="delRow(${Number(r.id_mon)})">Eliminar</button>
      </td>
    </tr>`).join('')
  : `<tr><td colspan="7"><div class="empty">Sin resultados</div></td></tr>`;

  info.textContent = `Página ${page}/${max}`;
  prev.disabled = page<=1;
  next.disabled = page>=max;
}

async function load(){
  setLoad();
  const r = await fetch(API_LIST, {cache:'no-store'});
  const j = await safeJson(r);
  data = Array.isArray(j) ? j : (j && Array.isArray(j.data) ? j.data : []);
  applySortArrow();
  render();
}

// sort headers
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
  const fd=new FormData(e.target);

  const real  = (fd.get('real')||'').trim();
  const dolar = (fd.get('dolar')||'').trim();
  const estado= (fd.get('estado')||'ACTIVO').trim();

  if(!real || !dolar){ toast('Completa Real y Dólar','', 'error'); return; }

  const r = await fetch(API_ADD,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo guardar', j.message||'', 'error');
  }else{
    toast('Cotización guardada');
    e.target.reset();
    // mantener valores por defecto
    const g = document.getElementById('guarani'); if(g) g.value = '1.0000';
    const es= document.getElementById('estado');  if(es) es.value = 'ACTIVO';
    load();
  }
});

// Edit open
window.openEdit = id=>{
  const it = data.find(x=>+x.id_mon===+id);
  if(!it){ toast('No encontrado','', 'error'); return; }

  formE.edit_id.value      = it.id_mon;
  formE.edit_guarani.value = it.guarani ?? '1.0000';
  formE.edit_real.value    = it.real ?? '';
  formE.edit_dolar.value   = it.dolar ?? '';
  formE.edit_estado.value  = it.estado ?? 'ACTIVO';

  dlg.showModal();
};
document.getElementById('cancelE').onclick = ()=> dlg.close();

// Save edit
document.getElementById('saveE').onclick = async ()=>{
  const fd = new FormData(formE);

  const real  = (fd.get('real')||'').trim();
  const dolar = (fd.get('dolar')||'').trim();
  if(!real || !dolar){ toast('Completa Real y Dólar','', 'error'); return; }

  const r = await fetch(API_EDIT,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo editar', j.message||'', 'error');
  }else{
    toast('Cotización actualizada');
    dlg.close();
    load();
  }
};

// Delete
window.delRow = async id=>{
  if(!confirm('¿Eliminar esta moneda?')) return;
  const r = await fetch(API_DEL + encodeURIComponent(id), {method:'GET'});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('No se pudo eliminar', j.message||'', 'error');
  }else{
    toast('Eliminada');
    load();
  }
};

// Init
load();
</script>
</body>
</html>
