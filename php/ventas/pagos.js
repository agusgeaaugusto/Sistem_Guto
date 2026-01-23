// ===== CartUI wiring (corregido y robusto) =====
(function initCartUIWire() {
  if (!window.CartUI) {
    console.warn('CartUI no está cargado. (initCartUIWire)');
    return;
  }

  // Mapear etiquetas (selectors)
  CartUI.configureSelectors({
    // IZQUIERDA = TOTAL (intacto)
    leftGs:   '#espejo-total-guarani',
    leftReal: '#espejo-total-real',
    leftUsd:  '#espejo-total-dolar',

    // DERECHA = RESTANTE/VUELTO
    totGs:   '#restante-guarani',
    totReal: '#restante-real',
    totUsd:  '#restante-dolar',

    // (OPCIONAL) DERECHA = TOTAL espejado
    rightTotalGs:   '#derecha-total-guarani',
    rightTotalReal: '#derecha-total-real',
    rightTotalUsd:  '#derecha-total-dolar'
  });

  // Tasas del día (Gs por 1 R$ y 1 US$)
  CartUI.setRates({ real: 1450, dolar: 7600 });

  // Subtotal inicial (en ₲)
  CartUI.setSubtotalGs(0);

  // ✅ Escuchar cuando tu app diga "cambió el carrito"
  // Espera: detail.subtotalGs (número)
  document.addEventListener('carrito:cambio', (ev) => {
    const sub = Number(ev?.detail?.subtotalGs ?? 0);
    CartUI.setSubtotalGs(Number.isFinite(sub) ? Math.round(sub) : 0);
  });

  // ✅ (Opcional) Si tu CartUI sabe leer el input de recibido/vuelto:
  // cuando el usuario tipee, re-calcular.
  // Ajustá estos IDs si usan otros en tu UI.
  const inputsPago = ['#input-guarani', '#input-real', '#input-dolar'];
  inputsPago.forEach((sel) => {
    const el = document.querySelector(sel);
    if (!el) return;
    el.addEventListener('input', () => {
      // Si tu CartUI tiene un método tipo "recalc()", usalo:
      if (typeof CartUI.recalc === 'function') CartUI.recalc();
      // Si no existe, no hacemos nada; CartUI puede estar recalculando solo.
    });
  });

  console.log('CartUI wire listo ✅');
})();
