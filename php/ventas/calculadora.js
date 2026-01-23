// =======================
// FINALIZAR VENTA (calculadora sin interferencias) — CORREGIDO
// =======================
(function () {
  'use strict';
  if (window.__calcFinalV2) return;
  window.__calcFinalV2 = true;

  // ======== IDs ========
  const ID_GS_SRC  = 'espejo-total-guarani';
  const ID_BR_SRC  = 'espejo-total-real';
  const ID_USD_SRC = 'espejo-total-dolar';

  const ID_GS_DST  = 'derecha-total-guarani';
  const ID_BR_DST  = 'derecha-total-real';
  const ID_USD_DST = 'derecha-total-dolar';

  const ID_IN_GS   = 'input-guarani';
  const ID_IN_BR   = 'input-real';
  const ID_IN_USD  = 'input-dolar';

  // ✅ En tu sistema usás "restante-*"; vos tenías "resta-*"
  // dejamos ambos: primero "restante", fallback a "resta"
  const ID_OUT_RESTA_GS  = 'restante-guarani';
  const ID_OUT_RESTA_BR  = 'restante-real';
  const ID_OUT_RESTA_USD = 'restante-dolar';

  const ID_OUT_RESTA_GS_FB  = 'resta-guarani';
  const ID_OUT_RESTA_BR_FB  = 'resta-real';
  const ID_OUT_RESTA_USD_FB = 'resta-dolar';

  const ID_COT_BR   = 'cotizacion-real';
  const ID_COT_USD  = 'cotizacion-dolar';
  const ID_BTN_PAGAR = 'btn-pagar-vender';
  const ID_ESTADO    = 'estado-pago';

  // ======== Tolerancias ========
  const TOL_GS  = 10;
  const TOL_BRL = 0.01;
  const TOL_USD = 0.01;

  // ======== Utils ========
  const $ = (id) => document.getElementById(id);

  const pickOut = (primaryId, fallbackId) => $(primaryId) || $(fallbackId);

  const fmtGs  = (n) => '₲ '  + Math.round(n).toLocaleString('es-PY');
  const fmtBr  = (n) => 'R$ ' + (Number(n) || 0).toLocaleString('es-PY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmtUsd = (n) => 'US$ ' + (Number(n) || 0).toLocaleString('es-PY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const clampZero = (v, t) => (Math.abs(v) < t ? 0 : v);
  const norm = (s) => String(s || '').trim().replace(/\s/g, '');

  function getInputs() {
    return { g: $(ID_IN_GS), r: $(ID_IN_BR), d: $(ID_IN_USD) };
  }

  // ======== Parseos ========
  // Guaraní: ignora puntos y comas, autocorrige *mil* si subtotal >= 10k
  function parseGsInput(raw, subtotalGs = 0) {
    let t = norm(raw).replace(/[^\d]/g, '');  // solo dígitos
    let n = parseInt(t || '0', 10);
    if (!Number.isFinite(n)) n = 0;

    // autokilo (solo si subtotal grande y el usuario puso muy pocos dígitos)
    if (subtotalGs >= 10000 && n > 0 && n < 1000) n *= 1000;

    return n;
  }

  // Reales / USD: detecta separador decimal correcto
  function parseFxInput(raw) {
    let t = norm(raw);
    if (!t) return 0;

    // deja solo dígitos y separadores
    t = t.replace(/[^\d.,-]/g, '');

    const lastDot = t.lastIndexOf('.');
    const lastCom = t.lastIndexOf(',');
    let decimalSep = null;

    if (lastDot !== -1 || lastCom !== -1) decimalSep = (lastDot > lastCom) ? '.' : ',';

    if (decimalSep) {
      const thousandsSep = decimalSep === '.' ? ',' : '.';
      t = t.split(thousandsSep).join('');
      if (decimalSep === ',') t = t.replace(',', '.');
    } else {
      // sin separador decimal: limpiamos comas
      t = t.replace(/,/g, '');
    }

    const n = parseFloat(t);
    return Number.isFinite(n) ? n : 0;
  }

  function textToNumberGs(txt) {
    const s = String(txt || '').replace(/[^\d]/g, '');
    const n = parseInt(s || '0', 10);
    return Number.isFinite(n) ? n : 0;
  }

  function getSubtotalGs() {
    // prioridad derecha, fallback izquierda
    const a = textToNumberGs($(ID_GS_DST)?.textContent || '');
    return a > 0 ? a : textToNumberGs($(ID_GS_SRC)?.textContent || '');
  }

  function getCotizaciones() {
    const toNum = (txt) => {
      let s = String(txt || '').replace(/[^\d.,-]/g, '');
      if (!s) return 0;
      if (s.includes(',') && s.includes('.')) s = s.replace(/\./g, '').replace(',', '.');
      else if (s.includes(',') && !s.includes('.')) s = s.replace(',', '.');
      const n = parseFloat(s);
      return Number.isFinite(n) ? n : 0;
    };

    // si no hay cotización en el DOM, usamos defaults globales si existen
    const domBR  = toNum($(ID_COT_BR)?.textContent || '0');
    const domUSD = toNum($(ID_COT_USD)?.textContent || '0');

    return {
      gsPorReal:  domBR  || window.COT_BR  || 1450,
      gsPorDolar: domUSD || window.COT_USD || 7600
    };
  }

  // ======== UI espejo / placeholders ========
  function espejoTexto(idSrc, idDst) {
    const src = $(idSrc), dst = $(idDst);
    if (src && dst) dst.textContent = src.textContent || '';
  }

  function sugerirPlaceholder() {
    const gTxt = $(ID_GS_SRC)?.textContent || '';
    const rTxt = $(ID_BR_SRC)?.textContent || '';
    const dTxt = $(ID_USD_SRC)?.textContent || '';
    const { g, r, d } = getInputs();

    if (g) g.placeholder = textToNumberGs(gTxt) > 0 ? String(textToNumberGs(gTxt)) : '';
    if (r) r.placeholder = parseFxInput(rTxt) > 0 ? String(parseFxInput(rTxt)) : '';
    if (d) d.placeholder = parseFxInput(dTxt) > 0 ? String(parseFxInput(dTxt)) : '';
  }

  function pasarSubtotalesADerecha() {
    espejoTexto(ID_GS_SRC, ID_GS_DST);
    espejoTexto(ID_BR_SRC, ID_BR_DST);
    espejoTexto(ID_USD_SRC, ID_USD_DST);
    sugerirPlaceholder();
  }

  function clearInputsOnOpen() {
    const { g, r, d } = getInputs();
    [g, r, d].forEach(el => { if (el) el.value = ''; });
  }

  function clearOutputs() {
    const estado = $(ID_ESTADO);
    const outGs  = pickOut(ID_OUT_RESTA_GS,  ID_OUT_RESTA_GS_FB);
    const outBr  = pickOut(ID_OUT_RESTA_BR,  ID_OUT_RESTA_BR_FB);
    const outUsd = pickOut(ID_OUT_RESTA_USD, ID_OUT_RESTA_USD_FB);

    [estado, outGs, outBr, outUsd].forEach(el => {
      if (!el) return;
      el.textContent = '';
      el.style.color = '#374151';
    });
  }

  // ======== Cálculo principal ========
  function recibidoEnGs() {
    const subtotal = getSubtotalGs();
    const { gsPorReal, gsPorDolar } = getCotizaciones();
    const { g, r, d } = getInputs();

    const vG = parseGsInput(g?.value || '0', subtotal);
    const vR = parseFxInput(r?.value || '0');
    const vD = parseFxInput(d?.value || '0');

    return Math.round(vG + vR * (gsPorReal || 0) + vD * (gsPorDolar || 0));
  }

  function recalcularPago() {
    const estado = $(ID_ESTADO);

    const outGs  = pickOut(ID_OUT_RESTA_GS,  ID_OUT_RESTA_GS_FB);
    const outBr  = pickOut(ID_OUT_RESTA_BR,  ID_OUT_RESTA_BR_FB);
    const outUsd = pickOut(ID_OUT_RESTA_USD, ID_OUT_RESTA_USD_FB);

    const { g, r, d } = getInputs();
    const anyTyped = !!((g && g.value) || (r && r.value) || (d && d.value));

    if (!anyTyped) {
      [estado, outGs, outBr, outUsd].forEach(el => {
        if (!el) return;
        el.textContent = '';
        el.style.color = '#374151';
      });
      return;
    }

    const subtotalGs = getSubtotalGs();
    const recibidoGs = recibidoEnGs();

    const diffGsRaw = recibidoGs - subtotalGs;
    const diffGs    = clampZero(diffGsRaw, TOL_GS);

    if (estado) {
      if (diffGs > 0) { estado.textContent = `Vuelto: ${fmtGs(diffGs)}`; estado.style.color = '#065f46'; }
      else if (diffGs === 0) { estado.textContent = 'Exacto'; estado.style.color = '#374151'; }
      else { estado.textContent = `Falta: ${fmtGs(-diffGs)}`; estado.style.color = '#6b7280'; }
    }

    const { gsPorReal, gsPorDolar } = getCotizaciones();
    let diffBr  = (gsPorReal  > 0) ? (diffGsRaw / gsPorReal)  : 0;
    let diffUsd = (gsPorDolar > 0) ? (diffGsRaw / gsPorDolar) : 0;

    diffBr  = clampZero(diffBr,  TOL_BRL);
    diffUsd = clampZero(diffUsd, TOL_USD);

    if (outGs) {
      outGs.textContent = diffGs > 0 ? `Vuelto: ${fmtGs(diffGs)}` : diffGs < 0 ? `Falta: ${fmtGs(-diffGs)}` : 'Exacto';
      outGs.style.color = diffGs > 0 ? '#065f46' : diffGs < 0 ? '#f80101ff' : '#374151';
    }
    if (outBr) {
      outBr.textContent = diffBr > 0 ? `Vuelto: ${fmtBr(diffBr)}` : diffBr < 0 ? `Falta: ${fmtBr(-diffBr)}` : 'Exacto';
      outBr.style.color = diffBr > 0 ? '#065f46' : diffBr < 0 ? '#f80101ff' : '#374151';
    }
    if (outUsd) {
      outUsd.textContent = diffUsd > 0 ? `Vuelto: ${fmtUsd(diffUsd)}` : diffUsd < 0 ? `Falta: ${fmtUsd(-diffUsd)}` : 'Exacto';
      outUsd.style.color = diffUsd > 0 ? '#065f46' : diffUsd < 0 ? '#f80101ff' : '#374151';
    }
  }

  // ======== Teclado en pantalla (solo clicks) ========
  function handleKeyFor(el, key) {
    if (!el) return;

    if (/^\d$/.test(key)) return insertAtCursor(el, key);

    // decimal solo para BRL/USD, y solo uno
    if (key === '.' || key === ',') {
      if (el.id !== ID_IN_GS) {
        if (!String(el.value).includes('.')) insertAtCursor(el, '.');
      }
      return;
    }

    if (key === 'Backspace' || key === 'BACK') return backspaceAtCursor(el);

    // Enter: recalcular nomás (no dependemos de btn-vuelto inexistente)
    if (key === 'Enter') return recalcularPago();
  }

  function insertAtCursor(el, text) {
    const start = el.selectionStart ?? el.value.length;
    const end   = el.selectionEnd ?? el.value.length;

    el.value = el.value.slice(0, start) + text + el.value.slice(end);
    const newPos = start + text.length;

    try { el.setSelectionRange(newPos, newPos); } catch (_) {}
    el.dispatchEvent(new Event('input', { bubbles: true }));
  }

  function backspaceAtCursor(el) {
    const start = el.selectionStart ?? el.value.length;
    const end   = el.selectionEnd ?? el.value.length;

    if (start !== end) {
      el.value = el.value.slice(0, start) + el.value.slice(end);
      try { el.setSelectionRange(start, start); } catch (_) {}
    } else if (start > 0) {
      el.value = el.value.slice(0, start - 1) + el.value.slice(start);
      try { el.setSelectionRange(start - 1, start - 1); } catch (_) {}
    }

    el.dispatchEvent(new Event('input', { bubbles: true }));
  }

  // ======== Helpers de inputs ========
  function forceTextInputConfig(el, mode) {
    if (!el) return;
    try { el.type = 'text'; } catch (_) { el.setAttribute('type', 'text'); }
    el.setAttribute('autocomplete', 'off');
    el.setAttribute('inputmode', mode === 'numeric' ? 'numeric' : 'decimal');
  }

  function autoScaleGsIfNeeded(el) {
    if (!el) return;
    const subtotal = getSubtotalGs();
    let t = String(el.value || '').replace(/[^\d]/g, '');
    if (!t) return;

    let n = parseInt(t, 10);
    if (!Number.isFinite(n)) n = 0;

    if (subtotal >= 10000 && n > 0 && n < 1000) {
      el.value = String(n * 1000);
      el.dispatchEvent(new Event('input', { bubbles: true }));
    }
  }

  function wireOnScreenKeyboard() {
    const teclado = $('teclado');
    if (!teclado || teclado.__wired) return;

    teclado.addEventListener('mousedown', e => e.preventDefault());
    teclado.addEventListener('touchstart', e => e.preventDefault(), { passive: false });

    const clickLike = (e) => {
      const btn = e.target?.closest?.('[data-key]');
      if (!btn) return;

      const { g, r, d } = getInputs();
      const act = document.activeElement;

      const el =
        (act && [g?.id, r?.id, d?.id].includes(act.id)) ? act :
        (g || r || d);

      el?.focus();
      handleKeyFor(el, btn.getAttribute('data-key'));
    };

    teclado.addEventListener('click', clickLike);
    teclado.addEventListener('touchend', clickLike);
    teclado.__wired = true;
  }

  // ======== Inputs (teclado físico SOLO aquí) ========
  function wireCalcInputs() {
    const { g, r, d } = getInputs();

    // Forzar TEXT para evitar que '.' vacíe con type=number
    forceTextInputConfig(g, 'numeric'); // Gs sin decimales
    forceTextInputConfig(r, 'decimal'); // Real con decimales
    forceTextInputConfig(d, 'decimal'); // USD con decimales

    // Recalcular en vivo
    [g, r, d].forEach(el => {
      if (!el) return;
      el.addEventListener('input', recalcularPago);
    });

    // Gs: solo dígitos; Enter/blur hacen autokilo si aplica
    if (g) {
      g.addEventListener('keydown', (e) => {
        if (['Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'Delete'].includes(e.key)) return;
        if (/^\d$/.test(e.key) || e.key === 'Backspace') return;
        if (e.key === 'Enter') { e.preventDefault(); autoScaleGsIfNeeded(g); recalcularPago(); return; }
        e.preventDefault();
      });
      g.addEventListener('blur', () => autoScaleGsIfNeeded(g));
    }

    // BRL / USD: dígitos + '.' o ',' + backspace + Enter
    [r, d].forEach(el => {
      if (!el) return;
      el.addEventListener('keydown', (e) => {
        if (['Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'Delete'].includes(e.key)) return;
        if (/^\d$/.test(e.key)) return;
        if (e.key === 'Backspace') return;
        if (e.key === '.' || e.key === ',') return;
        if (e.key === 'Enter') { e.preventDefault(); recalcularPago(); return; }
        e.preventDefault();
      });
    });
  }

  // ======== Observadores / Botones ========
  function wireObserversAndButtons() {
    // cambios de cotización => recalcular
    [ID_COT_BR, ID_COT_USD].forEach(id => {
      const el = $(id);
      if (!el) return;
      new MutationObserver(() => recalcularPago()).observe(el, { childList: true, subtree: true, characterData: true });
    });

    // cambios de total (carrito) => recalcular
    [ID_GS_SRC, ID_GS_DST].forEach(id => {
      const el = $(id);
      if (!el) return;
      new MutationObserver(() => recalcularPago()).observe(el, { childList: true, subtree: true, characterData: true });
    });

    // evento estándar de tu app
    document.addEventListener('carrito:cambio', () => recalcularPago());

    $(ID_BTN_PAGAR)?.addEventListener('click', () => {
      const finalBtn = Array.from(document.querySelectorAll('.rp-tab'))
        .find(b => b.dataset.target === 'panel-finalizar');

      finalBtn?.click();
      pasarSubtotalesADerecha();
      clearInputsOnOpen();
      clearOutputs();
      $(ID_IN_GS)?.focus();
    });
  }

  // ======== Init ========
  function init() {
    wireCalcInputs();
    wireOnScreenKeyboard();
    wireObserversAndButtons();
    pasarSubtotalesADerecha();
  }

  document.addEventListener('DOMContentLoaded', init);

  // API pública mínima
  window.Calculadora = { refresh: init, recalcular: recalcularPago };
})();
