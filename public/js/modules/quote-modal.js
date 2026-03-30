// ===== MODALE PREVENTIVO MULTI-STEP =====
(function () {
  'use strict';

  /* ---------- Configurazione prezzi ---------- */
  const BASE_PRICE = 175;
  const DELIVERY_SURCHARGE = { Standard: 0, Express: 50, Urgente: 100 };
  const BAGS_LABELS = { '0': 'Nessuna borsa laterale', '30': 'Borse smontate (+€30)', '70': 'Borse non smontabili (+€70)' };
  const DELIVERY_LABELS = {
    Standard: 'Standard',
    Express: 'Express',
    Urgente: 'Urgente',
  };
  const DELIVERY_DESC = {
    Standard: 'Consegna in 6-7 giorni',
    Express: 'Consegna in 3-5 giorni',
    Urgente: 'Consegna in 24-48h',
  };

  /* ---------- Stato ---------- */
  let currentStep = 1;
  const TOTAL_STEPS = 5;

  /* ---------- Stato Mappa / Tratta ---------- */
  let routeData = null;
  let pickupCoords = null;
  let deliveryCoords = null;

  /* ---------- Elementi DOM ---------- */
  const overlay = document.getElementById('quoteModal');
  const closeBtn = document.getElementById('quoteModalClose');
  const prevBtn = document.getElementById('quotePrevBtn');
  const nextBtn = document.getElementById('quoteNextBtn');
  const confirmBtn = document.getElementById('quoteConfirmBtn');
  const stepperSteps = document.querySelectorAll('.quote-stepper__step');
  const stepperLines = document.querySelectorAll('.quote-stepper__line');

  if (!overlay) return;

  /* ---------- Apertura modale ---------- */
  function openModal() {
    currentStep = 1;
    renderStep(currentStep);
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  /* ---------- Chiusura modale ---------- */
  function closeModal() {
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    setTimeout(resetForm, 350);
  }

  function resetForm() {
    currentStep = 1;
    ['motoBrand', 'motoModel', 'motoCc', 'motoBags', 'addressPickup', 'addressDelivery',
      'clientName', 'clientEmail', 'clientPhone', 'clientFiscal'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) { el.value = ''; el.classList.remove('is-error'); }
    });
    const priv = document.getElementById('privacyAccept');
    if (priv) priv.checked = false;
    const standardOpt = document.querySelector('input[name="deliveryType"][value="Standard"]');
    if (standardOpt) standardOpt.checked = true;
    updateDeliverySelection();
    routeData = null;
    pickupCoords = null;
    deliveryCoords = null;
    window._quoteRouteData = null;
    window._quotePickupCoords = null;
    window._quoteDeliveryCoords = null;
    const mapContainer = document.getElementById('quoteMapContainer');
    if (mapContainer) mapContainer.style.display = 'none';
    const routeSummary = document.getElementById('routeSummary');
    if (routeSummary) routeSummary.style.display = 'none';
    if (window._quoteRoutePolyline) {
      window._quoteRoutePolyline.setMap(null);
      window._quoteRoutePolyline = null;
    }
    if (window._quoteRouteMarkers) {
      window._quoteRouteMarkers.forEach(function(m) { m.setMap(null); });
      window._quoteRouteMarkers = [];
    }
  }

  /* ---------- Rendering step ---------- */
  function renderStep(step) {
    for (let i = 1; i <= TOTAL_STEPS; i++) {
      const panel = document.getElementById('quoteStep' + i);
      if (panel) panel.classList.toggle('quote-step--hidden', i !== step);
    }

    stepperSteps.forEach((el, idx) => {
      const stepNum = idx + 1;
      el.classList.remove('active', 'completed');
      if (stepNum === step) el.classList.add('active');
      if (stepNum < step) el.classList.add('completed');
    });

    stepperLines.forEach((line, idx) => {
      line.classList.toggle('completed', idx + 1 < step);
    });

    prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
    nextBtn.style.display = step === TOTAL_STEPS ? 'none' : 'inline-flex';
    confirmBtn.style.display = step === TOTAL_STEPS ? 'inline-flex' : 'none';

    if (step === TOTAL_STEPS) populateSummary();
  }

  /* ---------- Compila riepilogo ---------- */
  function populateSummary() {
    const brand = val('motoBrand');
    const model = val('motoModel');
    const cc = val('motoCc');
    const bags = val('motoBags');
    const pickup = val('addressPickup');
    const delivery = val('addressDelivery');
    const deliveryType = selectedDelivery();
    const name = val('clientName');
    const email = val('clientEmail');
    const phone = val('clientPhone');
    const fiscal = val('clientFiscal');

    const motoDesc = [brand, model, cc].filter(Boolean).join(' ');
    setText('summaryMoto', motoDesc || '—');
    setText('summaryMotoType', BAGS_LABELS[bags] || '—');
    setText('summaryRoute', pickup && delivery ? pickup + ' → ' + delivery : '—');
    setText('summaryDelivery', DELIVERY_LABELS[deliveryType] || deliveryType);
    setText('summaryDeliveryDesc', DELIVERY_DESC[deliveryType] || '');
    setText('summaryName', name || '—');
    const contactLines = [email, phone, fiscal].filter(Boolean).join('\n');
    setText('summaryContact', contactLines || '—');

    const bagsPrice = parseInt(bags) || 0;
    const rd = window._quoteRouteData || null;
    const transportCost = rd ? rd.total_cost : BASE_PRICE;
    const total = transportCost + (DELIVERY_SURCHARGE[deliveryType] || 0) + bagsPrice;
    setText('summaryPrice', '€' + total.toFixed(0));
  }

  /* ---------- Validazione per step ---------- */
  function validateStep(step) {
    let valid = true;

    const require = (id) => {
      const el = document.getElementById(id);
      if (!el) return;
      if (!el.value.trim()) {
        el.classList.add('is-error');
        valid = false;
      } else {
        el.classList.remove('is-error');
      }
    };

    if (step === 1) {
      require('motoBrand');
      require('motoModel');
      require('motoCc');
    } else if (step === 2) {
      require('addressPickup');
      require('addressDelivery');
    } else if (step === 3) {
      // la selezione ha sempre un valore default
    } else if (step === 4) {
      require('clientName');
      require('clientEmail');
      require('clientPhone');
      require('clientFiscal');
      const priv = document.getElementById('privacyAccept');
      if (priv && !priv.checked) {
        priv.closest('.quote-form__checkbox').style.color = 'var(--danger-color)';
        valid = false;
      } else if (priv) {
        priv.closest('.quote-form__checkbox').style.color = '';
      }
      const emailEl = document.getElementById('clientEmail');
      if (emailEl && emailEl.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value)) {
        emailEl.classList.add('is-error');
        valid = false;
      }
    }

    return valid;
  }

  /* ---------- Navigazione ---------- */
  function goNext() {
    if (!validateStep(currentStep)) return;
    if (currentStep < TOTAL_STEPS) {
      currentStep++;
      renderStep(currentStep);
    }
  }

  function goPrev() {
    if (currentStep > 1) {
      currentStep--;
      renderStep(currentStep);
    }
  }

  /* ---------- Gestione opzioni consegna ---------- */
  function updateDeliverySelection() {
    const options = document.querySelectorAll('.quote-delivery-option');
    options.forEach((opt) => {
      const radio = opt.querySelector('input[type="radio"]');
      opt.classList.toggle('quote-delivery-option--selected', radio && radio.checked);
    });
  }

  const deliveryOptions = document.querySelectorAll('.quote-delivery-option');
  deliveryOptions.forEach((opt) => {
    opt.addEventListener('click', function () {
      const radio = this.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
      updateDeliverySelection();
    });
  });

  /* ---------- Event listener ---------- */

  document.querySelectorAll('.open-quote-modal').forEach((btn) => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      openModal();
    });
  });

  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closeModal();
  });

  nextBtn.addEventListener('click', goNext);
  prevBtn.addEventListener('click', goPrev);

  confirmBtn.addEventListener('click', function () {
    alert('Reindirizzamento al sistema di pagamento sicuro...\n(integrazione pagamento da configurare)');
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
  });

  document.querySelectorAll('.quote-form__input, .quote-form__select').forEach((el) => {
    el.addEventListener('input', function () {
      this.classList.remove('is-error');
    });
  });

  /* ---------- Utilità ---------- */
  function val(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : '';
  }
  function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  }
  function selectedDelivery() {
    const checked = document.querySelector('input[name="deliveryType"]:checked');
    return checked ? checked.value : 'Standard';
  }
})();
