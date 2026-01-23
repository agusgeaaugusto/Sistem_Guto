<?php
// compra.php - Mismo estilo que cargo/categoría.
// Endpoints:
//   Listar   GET  register_compra_bi.php
//   Agregar  POST register_compra_bi.php
//   Eliminar POST eliminar.php (id_com)
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Compras</title>

  <!-- 🔗 Theme global (ajusta la ruta si cambia la carpeta) -->
 <link rel="stylesheet" href="../css/app-forms.css?v=20260103-1">

  <!-- Solo layout mínimo de página -->
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
    .grid{ display:grid; grid-template-columns: 480px 1fr; gap:18px }
    @media (max-width: 1024px){ .grid{ grid-template-columns: 1fr } }
  </style>
</head>
<body>
<div class="app">
  <div class="header"><div class="title">Compras</div></div>

  <div class="grid">
    <!-- Panel izquierdo: formulario -->
    <div class="form-card">
      <h2 class="form-title">Agregar compra</h2>

      <form id="formAdd" class="app-form cols-3" autocomplete="off" novalidate>
        <div class="field">
          <label class="label">Fecha de Compra</label>
          <input class="input" type="date" name="fecha_com" required>
        </div>
        <div class="field">
          <label class="label">ID Proveedor</label>
          <input class="input" type="number" name="id_proveedor" required>
        </div>
        <div class="field">
          <label class="label">ID Moneda</label>
          <input class="input" type="number" name="id_mon" required>
        </div>

        <div class="field">
          <label class="label">Timbrado</label>
          <input class="input" name="timbrado_com" required>
        </div>
        <div class="field">
          <label class="label">Documento</label>
          <input class="input" name="documento_com" required>
        </div>
        <div class="field">
          <label class="label">Fecha Emisión</label>
          <input class="input" type="date" name="fecha_emision_comp" required>
        </div>

        <div class="field span-2">
          <label class="label">Histórico</label>
          <textarea class="textarea" name="historico_com"></textarea>
        </div>
        <div class="field">
          <label class="label">Valor Documento</label>
          <input class="input" type="number" step="0.01" name="valor_documento_com" required>
        </div>

        <div class="form-actions span-3">
          <button class="btn" type="submit">Guardar</button>
          <button class="btn ghost" type="reset">Limpiar</button>
        </div>
      </form>

      
    </div>

    <!-- Panel derecho: listado -->
    <div class="table-wrap">
      <div class="panel">
        <h2 class="form-title">Listado de compras</h2>

        <div class="grid-2">
          <div class="field">
            <label class="label" for="buscar">Buscar</label>
            <div class="control has-icon">
              <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
              </svg>
              <input class="input" id="buscar" placeholder="Proveedor / Documento / Timbrado...">
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
        <table class="table">
          <thead>
            <tr>
              <th data-key="id_com" class="nowrap">ID ▾</th>
              <th data-key="fecha_com">Fecha</th>
              <th data-key="id_proveedor">Proveedor</th>
              <th data-key="id_mon">Moneda</th>
              <th data-key="timbrado_com">Timbrado</th>
              <th data-key="documento_com">Documento</th>
              <th data-key="fecha_emision_comp">Emisión</th>
              <th data-key="historico_com">Histórico</th>
              <th data-key="valor_documento_com" class="right">Valor</th>
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

<script>
const API_LIST='register_compra_bi.php',
      API_ADD ='register_compra_bi.php',
      API_DEL ='eliminar.php';

const tbody = document.getElementById('tbody'),
      buscar = document.getElementById('buscar'),
      pageSize = document.getElementById('pageSize'),
      info = document.getElementById('info'),
      prev = document.getElementById('prev'),
      next = document.getElementById('next');

let data=[], page=1, key='id_com', asc=true;

// UI helpers
const toast=(t,m='',type='success')=>{
  const w=document.getElementById('toasts'), el=document.createElement('div');
  el.className='toast '+(type||'');
  el.innerHTML=`<div><div class="t-title">${t}</div>${m?`<div class="t-msg">${m}</div>`:''}</div>`;
  w.appendChild(el);
  setTimeout(()=>{ el.style.opacity=0; setTimeout(()=>w.removeChild(el),300) },3200);
};
const safeJson=async r=>{try{return await r.json()}catch{return null}};
const setLoad=()=>{tbody.innerHTML=Array.from({length:6}).map(()=>`
  <tr>
    <td><div class="skeleton"></div></td>
    <td colspan="8"><div class="skeleton" style="width:90%"></div></td>
    <td class="right"><div class="skeleton" style="width:40%;margin-left:auto"></div></td>
  </tr>`).join('')};
const cmp=(a,b,k)=>{
  const A=(a[k]??'').toString().toLowerCase(), B=(b[k]??'').toString().toLowerCase();
  if(!isNaN(+a[k]) && !isNaN(+b[k])) return +a[k]-+b[k];
  return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'});
};
const filt=()=>{const q=(buscar.value||'').toLowerCase().trim();
  return q ? data.filter(x=>`${x.id_proveedor||''} ${x.documento_com||''} ${x.timbrado_com||''}`.toLowerCase().includes(q))
           : data.slice();
};

function render(){
  const rows=filt().sort((a,b)=>asc?cmp(a,b,key):cmp(b,a,key)),
        size=+pageSize.value||10,
        total=rows.length,
        max=Math.max(1,Math.ceil(total/size));
  if(page>max) page=max;
  const sl=rows.slice((page-1)*size,(page-1)*size+size);

  tbody.innerHTML = sl.length ? sl.map(r=>`
    <tr class="data">
      <td class="mono nowrap">#${r.id_com??''}</td>
      <td>${r.fecha_com??''}</td>
      <td>${r.id_proveedor??''}</td>
      <td>${r.id_mon??''}</td>
      <td>${r.timbrado_com??''}</td>
      <td>${r.documento_com??''}</td>
      <td>${r.fecha_emision_comp??''}</td>
      <td>${(r.historico_com??'').replace(/</g,'&lt;')}</td>
      <td class="right">${r.valor_documento_com??''}</td>
      <td class="right nowrap">
        <button class="btn danger" onclick="delRow(${Number(r.id_com)})">Eliminar</button>
      </td>
    </tr>`).join('')
  : `<tr><td colspan="10"><div class="empty">Sin resultados</div></td></tr>`;

  info.textContent = `Página ${page}/${max}`;
  prev.disabled = page<=1;
  next.disabled = page>=max;
}

async function load(){
  setLoad();
  const r = await fetch(API_LIST,{cache:'no-store'});
  const j = await safeJson(r);
  data = Array.isArray(j) ? j : [];
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
  const r = await fetch(API_ADD,{method:'POST', body:new FormData(e.target)});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('Error al guardar', j.message||'', 'error'); }
  else { toast('Compra guardada'); e.target.reset(); load(); }
});

// Delete
window.delRow = async id=>{
  if(!confirm('¿Eliminar compra?')) return;
  const f = new FormData(); f.set('id_com', id);
  const r = await fetch(API_DEL,{method:'POST', body:f});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo eliminar', j.message||'', 'error'); }
  else { toast('Eliminada'); load(); }
};

load();
</script>
</body>
</html>
