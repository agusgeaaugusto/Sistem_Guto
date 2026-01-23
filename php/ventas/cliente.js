// =======================
// CLIENTE: buscar por RUC/CI, abrir modal si no existe, registrar y autocompletar
// =======================
(function(){
  'use strict';
  if (window.__clienteWired) return; // evita doble wiring
  window.__clienteWired = true;

  // Si esta pantalla ya trae el modal de persona (#modal-persona),
  // dejamos que venta_flow.js maneje el flujo para evitar conflictos de ENTER.
  if (document.getElementById('modal-persona')) {
    return;
  }

  const $ = (sel)=> document.querySelector(sel);

  // --- Modal helpers ---
  function openClienteModal(rucInicial=''){
    const m = $('#modal-nuevo-cliente');
    if (!m) return console.warn('[Cliente] Modal no encontrado');
    m.classList.remove('hidden');
    m.setAttribute('aria-hidden','false');
    const inRuc = $('#nc-ruc'); if (inRuc) inRuc.value = rucInicial || '';
    const inNom = $('#nc-nombre'); if (inNom) inNom.value = '';
    setTimeout(()=> $('#nc-nombre')?.focus(), 0);
  }
  function closeClienteModal(){
    const m = $('#modal-nuevo-cliente');
    if (!m) return;
    m.classList.add('hidden');
    m.setAttribute('aria-hidden','true');
  }

  // --- Normalizador de claves (acepta tus nombres de Postgres) ---
  function normalizePersona(p){
    if (!p) return null;
    return {
      id:     p.id ?? p.id_persona ?? p.persona_id ?? p.idcliente ?? p.id_per ?? null,
      ruc:    p.ruc ?? p.ruc_ci ?? p.documento ?? p.ci ?? p.doc ?? p.cedula_per ?? '',
      nombre: p.nombre ?? p.razon_social ?? p.apellido_nombre ?? p.denominacion ?? p.nombre_per ?? ''
    };
  }

  // --- Set UI ---
  function setClienteEnVenta(persona){
    const n = normalizePersona(persona);
    if (!n) return;
    const rucEl = $('#cliente-ruc');
    const nomEl = $('#cliente-nombre');
    const idEl  = $('#cliente-id');
    if (rucEl) { rucEl.value = n.ruc || ''; rucEl.dataset.loadedDoc = n.ruc || ''; }
    if (nomEl) nomEl.value = n.nombre || '';
    if (idEl)  idEl.value  = n.id ?? '';

    if (typeof window.onClienteSeleccionado === 'function') {
      try { window.onClienteSeleccionado(n); } catch(_){}
    }
  }

  // Limpia nombre/id si cambian el RUC manualmente
  function wireCleanOnRucChange(){
    const rucEl = $('#cliente-ruc');
    if (!rucEl) return;
    rucEl.addEventListener('input', ()=>{
      const last = rucEl.dataset.loadedDoc || '';
      if (rucEl.value.trim() !== last){
        const nomEl = $('#cliente-nombre');
        const idEl  = $('#cliente-id');
        if (nomEl) nomEl.value = '';
        if (idEl)  idEl.value  = '';
      }
    });
  }

  // --- Fetch API helpers (simple) ---
  async function apiGet(url){
    try{
      const r = await fetch(url);
      const ct = r.headers.get('content-type')||'';
      const data = ct.includes('application/json') ? await r.json() : null;
      return { ok:r.ok, data, status:r.status };
    }catch(err){ return { ok:false, error:String(err) }; }
  }
  async function apiPost(url, formData){
    try{
      const r = await fetch(url, { method:'POST', body: formData });
      const ct = r.headers.get('content-type')||'';
      const data = ct.includes('application/json') ? await r.json() : null;
      return { ok:r.ok, data, status:r.status };
    }catch(err){ return { ok:false, error:String(err) }; }
  }

  // --- API dominio ---
  async function fetchPersonaByDoc(doc){
    // intenta ?doc= ?ruc= ?ci=
    for (const qp of ['doc','ruc','ci']){
      const r = await apiGet(`get_persona.php?${qp}=${encodeURIComponent(doc)}`);
      if (!r.ok) continue;
      const d = r.data;
      // Formato recomendado: {success:true/false, data:{...}}
      if (d && d.success && (d.data || d.persona)) return normalizePersona(d.data || d.persona);
      // Soporte por si tu PHP responde directo {id_per,...}
      if (d && !d.success && d.message) continue;
      if (d && d.id_per) return normalizePersona(d);
    }
    return null;
  }

  async function registrarNuevoCliente(ruc, nombre){
    const fd = new FormData();
    fd.append('ruc', ruc);
    fd.append('nombre', nombre);
    // Si tu backend usa otros nombres, descomenta:
    // fd.append('ruc_ci', ruc);
    // fd.append('razon_social', nombre);

    const r = await apiPost('register_persona_bi.php', fd);
    if (r.ok && (r.data?.success || r.data?.ok)) {
      const d = r.data.data || r.data.persona || { id:r.data.id, ruc, nombre };
      return normalizePersona(d);
    }
    const msg = r.data?.message || r.error || 'No se pudo registrar el cliente.';
    throw new Error(msg);
  }

  // --- Flow principal ---
  async function onClienteDocEnter(){
    const rucEl = $('#cliente-ruc');
    const doc = rucEl?.value.trim();
    if (!doc) return;

    const persona = await fetchPersonaByDoc(doc);
    if (persona){
      setClienteEnVenta(persona);
    } else {
      openClienteModal(doc);
    }
  }

  // --- Wiring ---
  document.addEventListener('DOMContentLoaded', ()=>{
    const inDoc = $('#cliente-ruc');
    if (!inDoc) { console.warn('[Cliente] #cliente-ruc no encontrado'); return; }

    wireCleanOnRucChange();

    // Si hay <form>, capturá el submit y buscá
    const form = inDoc.closest('form');
    if (form) {
      form.addEventListener('submit', (e)=>{
        e.preventDefault();
        e.stopPropagation();
        safeBuscar();
      }, true);
    }

    let _buscando = false;
    async function safeBuscar(){
      if (_buscando) return;
      _buscando = true;
      try { await onClienteDocEnter(); } finally { _buscando = false; }
    }
    const isEnter = (e)=> (e.key === 'Enter' || e.key === 'NumpadEnter' || e.keyCode === 13);

    // Enter en captura: evita que otros scripts lo roben
    inDoc.addEventListener('keydown', (e)=>{
      if (isEnter(e)) {
        e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation();
        safeBuscar();
      }
    }, true);
    // Fallback
    inDoc.addEventListener('keypress', (e)=>{
      if (isEnter(e)) {
        e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation();
        safeBuscar();
      }
    }, true);
    // Blur
    inDoc.addEventListener('blur', ()=>{
      if (inDoc.value.trim()) safeBuscar();
    });

    // Modal cancelar
    $('#nc-cancelar')?.addEventListener('click', ()=>{
      closeClienteModal();
      $('#cliente-nombre')?.focus();
    });
    // Modal guardar
    $('#form-nuevo-cliente')?.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const ruc = $('#nc-ruc')?.value.trim();
      const nombre = $('#nc-nombre')?.value.trim();
      if (!ruc || !nombre) return alert('Completá RUC/C.I. y Nombre');
      try{
        const persona = await registrarNuevoCliente(ruc, nombre);
        setClienteEnVenta(persona);
        closeClienteModal();
        $('#cliente-nombre')?.focus();
      }catch(err){
        console.error(err);
        alert(err.message || 'Error al registrar el cliente.');
      }
    });
  });

  // API pública opcional (debug)
  window.Cliente = { openClienteModal, closeClienteModal };
})();
