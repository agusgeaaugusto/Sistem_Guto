<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Productos Detalle</title>

  <!-- 🔗 Theme unificado -->
  <link rel="stylesheet" href="../css/app-forms.css?v=20260127-std">
</head>
<body>
<div class="app" role="application" aria-label="Productos Detalle">

  <!-- Alta de detalle -->
  <div class="form-card">
    <h2 class="form-title">Agregar Detalle de Producto</h2>


    <form id="formAdd" class="app-form" autocomplete="off" novalidate>

      <div class="field f-idcompra">
        <label class="label" for="id_com">ID Compra</label>
        <select class="select" id="id_com" name="id_com" required>
          <option value="">Seleccione una compra</option>
        </select>
      </div>

      <div class="field f-codbarra">
        <label class="label" for="codigo_barra_pro">Código de Barra (Enter para buscar)</label>
        <input class="input" id="codigo_barra_pro" name="codigo_barra_pro" required>
      </div>

      <div class="field">
        <label class="label" for="id_pro">ID Producto</label>
        <input class="input" id="id_pro" name="id_pro" readonly>
      </div>

      <div class="field f-nombre span-2">
        <label class="label" for="nombre_pro">Nombre del Producto</label>
        <input class="input" id="nombre_pro" name="nombre_pro" required>
      </div>

      <div class="field">
        <label class="label" for="uni_caja_pro">Unidades por Caja</label>
        <input class="input" id="uni_caja_pro" name="uni_caja_pro" type="number" required>
      </div>

      <div class="field f-cajas">
        <label class="label" for="cantidad_caja_pro">Cantidad de Cajas</label>
        <input class="input" id="cantidad_caja_pro" name="cantidad_caja_pro" type="number" required>
      </div>

      <div class="field f-uni">
        <label class="label" for="cantidad_uni_pro">Total Unidades (auto, editable)</label>
        <input class="input" id="cantidad_uni_pro" name="cantidad_uni_pro" type="number">
      </div>

      <div class="field f-costocaja">
        <label class="label" for="costo_caja_pro">Costo por Caja</label>
        <input class="input" id="costo_caja_pro" name="costo_caja_pro" type="number" step="0.01" required>
      </div>

      <div class="field f-costouni">
        <label class="label" for="costo_uni_pro">Costo por Unidad (auto, editable)</label>
        <input class="input" id="costo_uni_pro" name="costo_uni_pro" type="number" step="0.01">
      </div>

      <div class="field f-porcen">
        <label class="label" for="porcen_pro">Margen %</label>
        <input class="input" id="porcen_pro" name="porcen_pro" type="number" step="0.01" required>
      </div>

      <!-- <div class="field f-iva">
        <label class="label" for="iva_pro">IVA %</label>
        <input class="input" id="iva_pro" name="iva_pro" type="number" step="0.01" required>
      </div>-->

      <div class="field">
        <label class="label" for="fecha_ven_pro">Fecha de Vencimiento</label>
        <input class="input" id="fecha_ven_pro" name="fecha_ven_pro" type="date">
      </div>

      <div class="field f-p1">
        <label class="label" for="precio1_pro">Precio Venta 1</label>
        <input class="input" id="precio1_pro" name="precio1_pro" type="number" step="0.01" required>
      </div>

      <div class="field f-p2">
        <label class="label" for="precio2_pro">Precio Venta 2</label>
        <input class="input" id="precio2_pro" name="precio2_pro" type="number" step="0.01" required>
      </div>

      <div class="field f-p3">
        <label class="label" for="precio3_pro">Precio Venta 3</label>
        <input class="input" id="precio3_pro" name="precio3_pro" type="number" step="0.01" value="0">
      </div>
    </form>
    <div class="form-actions">
        <button class="btn primary" type="submit" form="formAdd">✔ Agregar</button>
        <button class="btn secondary" type="reset" form="formAdd">Limpiar</button>
      </div>
  </div>

  <!-- Listado (solo lo necesario) -->
  <div class="table-wrap" aria-labelledby="titulo-lista">
    <div class="panel">
      
      <div class="grid-2">
        <div class="field">
          <label class="label" for="buscar">Buscar</label>
          <div class="control has-icon">
            <svg class="icon-left" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M21 21l-4.3-4.3m1.8-4.5a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <input class="input" id="buscar" placeholder="Filtra por ID, compra o código…">
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
            <th data-key="id_det_pro" class="nowrap">ID ▾</th>
            <th data-key="id_com" class="nowrap">Compra</th>
            <th data-key="codigo_barra_pro" class="nowrap">Código</th>
            <th data-key="cantidad_caja_pro" class="right nowrap">Cajas</th>
            <th data-key="cantidad_uni_pro" class="right nowrap">Unid</th>
            <th data-key="costo_uni_pro" class="right nowrap">Costo U</th>
            <th data-key="fecha_ven_pro" class="nowrap">Vence</th>
            <th data-key="precio1_pro" class="right nowrap">Precio 1</th>
            <th class="right nowrap">Acciones</th>
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

<!-- Toasts -->
<div class="toast-wrap" id="toasts" aria-live="polite" aria-atomic="true"></div>

<!-- Modal: Alta rápida de Producto cuando no existe por código -->
<dialog id="dlgProd">
  <div class="dialog-header">Agregar Producto (rápido)</div>
  <div class="dialog-body">
    <form id="formProd" class="app-form" novalidate>
      <div class="field span-2">
        <label class="label" for="p_nombre">Nombre</label>
        <input class="input" id="p_nombre" name="nombre_pro" required>
      </div>
      <div class="field">
        <label class="label" for="p_codigo">Código de Barra</label>
        <input class="input" id="p_codigo" name="codigo_barra_pro" required>
      </div>
      <div class="field">
        <label class="label" for="p_unicaja">Unidades por Caja</label>
        <input class="input" id="p_unicaja" name="uni_caja_pro" type="number" required>
      </div>
      <div class="field">
        <label class="label" for="p_iva">IVA</label>
        <input class="input" id="p_iva" name="iva_pro" type="number" step="0.01" required>
      </div>
      <div class="field">
        <label class="label" for="p_cat">ID Categoría</label>
        <input class="input" id="p_cat" name="id_cat" type="number" required>
      </div>
    </form>
  </div>
  <div class="dialog-actions">
    <button class="btn ghost" id="p_cancel" type="button">Cancelar</button>
    <button class="btn" id="p_save" type="button">Guardar</button>
  </div>
</dialog>

<script>
// ===== Config =====
const API_LIST_DET = 'register_producto_detalle_bi.php'; // GET lista / POST crear
const API_GET_COMP = 'get_compra.php';                    // GET compras
const API_GET_PROD = 'get_producto_completo.php';         // GET producto por código (+id_com)
const API_SAVE_PROD = 'register_producto_bi.php';         // POST alta rápida producto

// ===== DOM =====
const tbody = document.getElementById('tbody');
const buscar = document.getElementById('buscar');
const pageSize = document.getElementById('pageSize');
const info = document.getElementById('info');
const prev = document.getElementById('prev');
const next = document.getElementById('next');

const formAdd = document.getElementById('formAdd');
const id_com = document.getElementById('id_com');
const codigo_barra_pro = document.getElementById('codigo_barra_pro');
const id_pro = document.getElementById('id_pro');
const nombre_pro = document.getElementById('nombre_pro');
const uni_caja_pro = document.getElementById('uni_caja_pro');
const cantidad_caja_pro = document.getElementById('cantidad_caja_pro');
const cantidad_uni_pro = document.getElementById('cantidad_uni_pro');
const fecha_ven_pro = document.getElementById('fecha_ven_pro');
const costo_caja_pro = document.getElementById('costo_caja_pro');
const costo_uni_pro = document.getElementById('costo_uni_pro');
const porcen_pro = document.getElementById('porcen_pro');
const iva_pro = document.getElementById('iva_pro');
const precio1_pro = document.getElementById('precio1_pro');
const precio2_pro = document.getElementById('precio2_pro');
const precio3_pro = document.getElementById('precio3_pro');

// Modal producto rápido
const dlgProd = document.getElementById('dlgProd');
const formProd = document.getElementById('formProd');
const p_codigo = document.getElementById('p_codigo');
const p_nombre = document.getElementById('p_nombre');
const p_unicaja = document.getElementById('p_unicaja');
const p_iva = document.getElementById('p_iva');
const p_cat = document.getElementById('p_cat');
document.getElementById('p_cancel').onclick = ()=> dlgProd.close();

// ===== Estado =====
let rows = [];
let page = 1;
let key = 'id_det_pro';
let asc = true;

// Si el usuario edita manualmente, evitamos pisarle con cálculos automáticos
let manual = { cantidadUni: false, costoUni: false };

// ===== Utils =====
const toast=(t,m='',type='success')=>{
  const w=document.getElementById('toasts'), el=document.createElement('div');
  el.className='toast '+(type||''); el.innerHTML=`<div><div class="t-title">${t}</div>${m?`<div class="t-msg">${m}</div>`:''}</div>`;
  w.appendChild(el); setTimeout(()=>{ el.style.opacity=0; setTimeout(()=>w.removeChild(el),300) },3200);
};
const safeJson=async r=>{try{return await r.json()}catch{return null}};
const normList = resp => (Array.isArray(resp) ? resp : (resp && Array.isArray(resp.data) ? resp.data : []));
const cmp=(a,b,k)=>{
  const A=(a[k]??'').toString().toLowerCase(), B=(b[k]??'').toString().toLowerCase();
  if(!isNaN(+a[k]) && !isNaN(+b[k])) return +a[k] - +b[k];
  return A.localeCompare(B,'es',{numeric:true,sensitivity:'base'});
};
const nf = (n, d=0) => new Intl.NumberFormat('es-PY', {minimumFractionDigits:d, maximumFractionDigits:d, useGrouping:true}).format(+n||0);

// ===== Cálculos =====
function calcCantidadTotal(){
  const uxc = +uni_caja_pro.value || 0;
  const cajas = +cantidad_caja_pro.value || 0;
  if(!manual.cantidadUni) cantidad_uni_pro.value = uxc * cajas;
}
function calcCostoUni(){
  const caja = +costo_caja_pro.value || 0;
  const uxc = (+uni_caja_pro.value || 1);
  if(!manual.costoUni) costo_uni_pro.value = (caja / uxc).toFixed(2);
}
function calcPrecios(){
  const cu = +costo_uni_pro.value || 0;
  const cc = +costo_caja_pro.value || 0;
  const p  = +porcen_pro.value || 0;
  precio1_pro.value = (cu * (1 + p/100)).toFixed(2);
  precio2_pro.value = (cc * (1 + p/100)).toFixed(2);
  if(!precio3_pro.value) precio3_pro.value = 0;
}

uni_caja_pro.addEventListener('input', ()=>{ calcCantidadTotal(); calcCostoUni(); calcPrecios(); });
cantidad_caja_pro.addEventListener('input', ()=>{ calcCantidadTotal(); });
costo_caja_pro.addEventListener('input', ()=>{ calcCostoUni(); calcPrecios(); });
porcen_pro.addEventListener('input', ()=>{ calcPrecios(); });
costo_uni_pro.addEventListener('input', ()=>{ calcPrecios(); });

cantidad_uni_pro.addEventListener('input', ()=>{ manual.cantidadUni = true; });
costo_uni_pro.addEventListener('input', ()=>{ manual.costoUni = true; });

// Buscar producto por código (Enter)
codigo_barra_pro.addEventListener('keypress', async (ev)=>{
  if(ev.key !== 'Enter') return;
  ev.preventDefault();

  const code = (codigo_barra_pro.value||'').trim();
  if(!code){ toast('Ingrese un código válido','','error'); return; }

  try{
    const comp = (id_com.value||'').trim();
    const url = API_GET_PROD + '?codigo_barra=' + encodeURIComponent(code) + (comp ? ('&id_com=' + encodeURIComponent(comp)) : '');
    const r = await fetch(url, {cache:'no-store'});
    const j = await safeJson(r);

    if(j && j.existe){
      manual = { cantidadUni:false, costoUni:false };
      id_pro.value       = j.id_pro || '';
      nombre_pro.value   = j.nombre_pro || '';
      uni_caja_pro.value = j.uni_caja_pro || '';
      iva_pro.value      = (j.iva_pro ?? iva_pro.value ?? '') ;

      cantidad_caja_pro.value = j.cantidad_caja_pro ?? '';
      cantidad_uni_pro.value  = j.cantidad_uni_pro  ?? ( (Number(uni_caja_pro.value)||0) * (Number(cantidad_caja_pro.value)||0) );

      costo_caja_pro.value = j.costo_caja_pro ?? '';
      costo_uni_pro.value  = (j.costo_uni_pro ?? ( (Number(costo_caja_pro.value)||0) / (Number(uni_caja_pro.value)||1) )).toFixed(2);

      porcen_pro.value   = j.porcen_pro   ?? '';
      precio1_pro.value  = j.precio1_pro  ?? ( (Number(costo_uni_pro.value)||0) * (1 + (Number(porcen_pro.value)||0)/100) ).toFixed(2);
      precio2_pro.value  = j.precio2_pro  ?? ( (Number(costo_caja_pro.value)||0) * (1 + (Number(porcen_pro.value)||0)/100) ).toFixed(2);
      precio3_pro.value  = j.precio3_pro  ?? 0;

      fecha_ven_pro.value = j.fecha_ven_pro ?? '';

      calcCantidadTotal(); calcCostoUni(); calcPrecios();
      toast('Producto cargado', '', 'success');

    } else {
      p_codigo.value  = code;
      p_nombre.value  = '';
      p_unicaja.value = '';
      p_iva.value     = '';
      p_cat.value     = '';
      dlgProd.showModal();
    }
  }catch(err){
    console.error(err);
    toast('Error al buscar producto','', 'error');
  }
});

// Guardar producto rápido
document.getElementById('p_save').addEventListener('click', async ()=>{
  const fd = new FormData(formProd);
  if(!(fd.get('nombre_pro')||'').trim()){ toast('Falta nombre','','error'); return; }
  const r = await fetch(API_SAVE_PROD,{method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success){
    toast('Producto registrado');
    dlgProd.close();
    codigo_barra_pro.value = j.codigo_barra_pro || p_codigo.value;
    id_pro.value = j.id_pro || '';
    nombre_pro.value = j.nombre_pro || p_nombre.value;
    uni_caja_pro.value = j.uni_caja_pro || p_unicaja.value;
  }else{
    toast('No se pudo crear', (j && j.message)||'', 'error');
  }
});

// ===== Listado / UI =====
function filt(){
  const q = (buscar.value||'').toLowerCase().trim();
  if(!q) return rows.slice();
  return rows.filter(r=>{
    const t = `${r.id_det_pro||''} ${r.id_com||''} ${r.codigo_barra_pro||''}`.toLowerCase();
    return t.includes(q);
  });
}
function render(){
  const arr = filt().sort((a,b)=> asc ? cmp(a,b,key) : cmp(b,a,key));
  const size = +pageSize.value || 10;
  const total = arr.length, max = Math.max(1, Math.ceil(total/size));
  if(page>max) page = max;
  const sl = arr.slice((page-1)*size, (page-1)*size+size);

  tbody.innerHTML = sl.length ? sl.map(x=>`
    <tr class="data">
      <td class="mono nowrap">#${x.id_det_pro??''}</td>
      <td class="nowrap">${x.id_com??''}</td>
      <td class="nowrap">${(x.codigo_barra_pro??'').replace(/</g,'&lt;')}</td>
      <td class="right">${nf(x.cantidad_caja_pro)}</td>
      <td class="right">${nf(x.cantidad_uni_pro)}</td>
      <td class="right">${nf(x.costo_uni_pro,2)}</td>
      <td class="nowrap">${x.fecha_ven_pro || '—'}</td>
      <td class="right">${nf(x.precio1_pro,2)}</td>
      <td class="right nowrap">
        <button class="btn danger" onclick="delRow(${x.id_det_pro})">Eliminar</button>
      </td>
    </tr>
  `).join('') : `<tr><td colspan="9"><div class="empty">Sin resultados</div></td></tr>`;

  info.textContent = `Página ${page}/${max}`;
  prev.disabled = page<=1; next.disabled = page>=max;
}

// Sort headers
document.querySelectorAll('thead th[data-key]').forEach(th=>{
  th.addEventListener('click', ()=>{
    const k = th.getAttribute('data-key');
    if(key===k) asc = !asc; else { key = k; asc = true; }
    document.querySelectorAll('thead th[data-key]').forEach(t=>{
      t.textContent = t.textContent.replace(/[▴▾]/g,'').trim();
      if(t.getAttribute('data-key')===key) t.textContent += ' ' + (asc ? '▴' : '▾');
    });
    render();
  });
});

buscar.oninput = ()=>{ page=1; render(); };
pageSize.onchange = ()=>{ page=1; render(); };
prev.onclick = ()=>{ if(page>1){ page--; render(); } };
next.onclick = ()=>{ page++; render(); };

// ===== Data I/O =====
async function loadCompras(){
  const r = await fetch(API_GET_COMP, {cache:'no-store'});
  const j = await safeJson(r);
  const items = Array.isArray(j) ? j : (j && j.data) ? j.data : [];
  id_com.innerHTML = `<option value="">Seleccione una compra</option>` + items.map(c=>(
    `<option value="${c.id_com}">${c.id_com} - ${c.fecha_com||''}</option>`
  )).join('');
  if(items.length) id_com.value = items[0].id_com;
}

async function loadDetalles(){
  const comp = (id_com.value||'').trim();
  const url = comp ? (API_LIST_DET + '?id_com=' + encodeURIComponent(comp)) : API_LIST_DET;
  const r = await fetch(url, {cache:'no-store'});
  const j = await safeJson(r);
  if(j && j.success === false){ toast('Error al listar', j.error||'', 'error'); return; }
  rows = normList(j);
  render();
}

id_com.addEventListener('change', async ()=>{ await loadDetalles(); });

window.delRow = async (id)=>{
  if(!confirm('¿Eliminar este detalle?')) return;
  const fd = new FormData(); fd.set('id_det_pro', id); fd.set('action','delete');
  const r = await fetch(API_LIST_DET, {method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){ toast('No se pudo eliminar', j.message||'', 'error'); }
  await loadDetalles();
};

// Alta detalle
formAdd.addEventListener('submit', async (ev)=>{
  ev.preventDefault();
  const fd = new FormData(formAdd);
  fd.set('cantidad_uni_pro', cantidad_uni_pro.value);
  fd.set('costo_uni_pro', costo_uni_pro.value);

  const r = await fetch(API_LIST_DET, {method:'POST', body: fd});
  const j = await safeJson(r);
  if(j && j.success===false){
    toast('Error al guardar', j.message||'', 'error');
  }else{
    toast('Detalle guardado');
    formAdd.reset();
    manual = { cantidadUni:false, costoUni:false };
    await loadDetalles();
  }
});
formAdd.addEventListener('reset', ()=>{ manual = { cantidadUni:false, costoUni:false }; });

// ===== Init =====
(async function init(){
  await loadCompras();
  await loadDetalles();
})();
</script>
</body>
</html>
