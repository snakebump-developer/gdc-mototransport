// ===== MODALE PREVENTIVO MULTI-STEP + PAGAMENTO STRIPE =====
try {
(function () {
  'use strict';

  /* ---------- Configurazione prezzi (mirror del server) ---------- */
  const BASE_PRICE = 175;
  const DELIVERY_SURCHARGE = { Standard: 0, Express: 50, Urgente: 100 };
  const BAGS_LABELS = { '0': 'Nessuna borsa laterale', '30': 'Borse smontate (+€30)', '70': 'Borse non smontabili (+€70)' };
  const DELIVERY_LABELS  = { Standard: 'Standard', Express: 'Express', Urgente: 'Urgente' };
  const DELIVERY_DESC    = { Standard: 'Consegna in 6-7 giorni', Express: 'Consegna in 3-5 giorni', Urgente: 'Consegna in 24-48h' };

  /* ---------- Stato ---------- */
  let currentStep = 1;
  const SUMMARY_STEP  = 5;
  const PAYMENT_STEP  = 6;
  const SUCCESS_STEP  = 7;
  const TOTAL_STEPS   = SUCCESS_STEP; // step visivi nello stepper: 1-6 + success hidden

  /* ---------- Stato Mappa / Tratta ---------- */
  let routeData     = null;
  let pickupCoords  = null;
  let deliveryCoords = null;

  /* ---------- Stato Stripe ---------- */
  let stripeInstance = null;
  let stripeElements = null;
  let stripePaymentElement = null;
  let currentClientSecret = null;
  let currentPreventivoId = null;
  let currentImporto = null;

  /* ---------- Elementi DOM ---------- */
  const overlay       = document.getElementById('quoteModal');
  const closeBtn      = document.getElementById('quoteModalClose');
  const prevBtn       = document.getElementById('quotePrevBtn');
  const nextBtn       = document.getElementById('quoteNextBtn');
  const confirmBtn    = document.getElementById('quoteConfirmBtn');
  const payBtn        = document.getElementById('quotePayBtn');
  const saveDraftBtn  = document.getElementById('quoteSaveDraftBtn');
  const stepperSteps  = document.querySelectorAll('.quote-stepper__step');
  const stepperLines  = document.querySelectorAll('.quote-stepper__line');

  if (!overlay) return;

  /* ---- API pubblica esposta subito (prima che qualsiasi cosa possa crashare) ---- */
  window._quoteModal = {
    goToPaymentStep: function (clientSecret, preventivoId, importo, motoLabel) {
      currentClientSecret = clientSecret;
      currentPreventivoId = preventivoId;
      currentImporto      = importo;
      setText('paymentSummaryMoto',  motoLabel || 'Trasporto moto');
      setText('paymentSummaryTotal', '\u20ac' + parseFloat(importo).toFixed(2));
      mountStripeElement(clientSecret);
      currentStep = PAYMENT_STEP;
      renderStep(currentStep);
    },
  };

  /* ---------- Inizializza Stripe ---------- */
  function initStripe() {
    const pk = window.STRIPE_PUBLIC_KEY || '';
    if (!pk || pk.indexOf('INSERISCI') !== -1) return;
    if (typeof Stripe === 'undefined') return;
    stripeInstance = Stripe(pk);
  }
  initStripe();

  /* ---------- Apertura modale ---------- */
  function openModal() {
    currentStep = 1;
    renderStep(currentStep);
    prefillFromUserData();
    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  /* Prefill campi da dati utente loggato (solo se il campo è vuoto) */
  function prefillFromUserData() {
    const ud = window.QUOTE_USER_DATA;
    if (!ud) return;
    const setIfEmpty = (id, val) => {
      const el = document.getElementById(id);
      if (el && !el.value && val) el.value = val;
    };
    setIfEmpty('clientName',   ud.nome);
    setIfEmpty('clientEmail',  ud.email);
    setIfEmpty('clientPhone',  ud.telefono);
    setIfEmpty('clientFiscal', ud.cf);
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
      'pickupDate', 'clientName', 'clientEmail', 'clientPhone', 'clientFiscal'].forEach((id) => {
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
    // Reset Stripe
    if (stripePaymentElement) {
      stripePaymentElement.unmount();
      stripePaymentElement = null;
    }
    stripeElements = null;
    currentClientSecret = null;
    currentPreventivoId = null;
    currentImporto = null;
    const errEl = document.getElementById('stripePaymentError');
    if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }
    const draftSaved = document.getElementById('quoteDraftSaved');
    if (draftSaved) draftSaved.hidden = true;
  }

  /* ---------- Rendering step ---------- */
  function renderStep(step) {
    // Mostra il pannello corretto (1-7)
    for (let i = 1; i <= SUCCESS_STEP; i++) {
      const panel = document.getElementById('quoteStep' + i);
      if (panel) panel.classList.toggle('quote-step--hidden', i !== step);
    }

    // Stepper visivo: solo step 1-6
    stepperSteps.forEach((el, idx) => {
      const stepNum = idx + 1;
      el.classList.remove('active', 'completed');
      if (stepNum === step) el.classList.add('active');
      if (stepNum < step)  el.classList.add('completed');
    });

    stepperLines.forEach((line, idx) => {
      line.classList.toggle('completed', idx + 1 < step);
    });

    // Pulsanti footer
    const onSummary = step === SUMMARY_STEP;
    const onPayment = step === PAYMENT_STEP;
    const onSuccess = step === SUCCESS_STEP;
    const hidePrev  = step === 1 || onPayment || onSuccess;

    prevBtn.style.display    = hidePrev    ? 'none' : 'inline-flex';
    nextBtn.style.display    = (onSummary || onPayment || onSuccess) ? 'none' : 'inline-flex';
    confirmBtn.style.display = onSummary   ? 'inline-flex' : 'none';
    payBtn.style.display     = onPayment   ? 'inline-flex' : 'none';
    if (saveDraftBtn) saveDraftBtn.style.display = onSummary ? 'inline-flex' : 'none';

    // Nascondi pannello bozza salvata quando si esce dallo step riepilogo
    if (!onSummary) {
      const draftSaved = document.getElementById('quoteDraftSaved');
      if (draftSaved) draftSaved.hidden = true;
    }

    // Stepper nascosto su step successo
    const stepper = document.getElementById('quoteStepper');
    if (stepper) stepper.style.display = onSuccess ? 'none' : '';

    if (onSummary) populateSummary();
  }

  /* ---------- Calcolo totale ---------- */
  function calcTotal() {
    const bagsPrice   = parseInt(val('motoBags')) || 0;
    const deliveryType = selectedDelivery();
    const rd = window._quoteRouteData || null;
    const transportCost = rd ? rd.total_cost : BASE_PRICE;
    return transportCost + (DELIVERY_SURCHARGE[deliveryType] || 0) + bagsPrice;
  }

  /* ---------- Compila riepilogo ---------- */
  function populateSummary() {
    const brand = val('motoBrand');
    const model = val('motoModel');
    const cc    = val('motoCc');
    const bags  = val('motoBags');
    const pickup    = val('addressPickup');
    const delivery  = val('addressDelivery');
    const deliveryType = selectedDelivery();
    const pickupDate   = val('pickupDate');
    const name  = val('clientName');
    const email = val('clientEmail');
    const phone = val('clientPhone');
    const fiscal = val('clientFiscal');

    const motoDesc = [brand, model, cc].filter(Boolean).join(' ');
    setText('summaryMoto', motoDesc || '—');
    setText('summaryMotoType', BAGS_LABELS[bags] || '—');
    setText('summaryRoute', pickup && delivery ? pickup + ' → ' + delivery : '—');
    setText('summaryDelivery', DELIVERY_LABELS[deliveryType] || deliveryType);
    setText('summaryDeliveryDesc', DELIVERY_DESC[deliveryType] || '');
    if (pickupDate) {
      const d = new Date(pickupDate + 'T00:00:00');
      setText('summaryDate', d.toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
    } else {
      setText('summaryDate', '—');
    }
    setText('summaryName', name || '—');
    const contactLines = [email, phone, fiscal].filter(Boolean).join('\n');
    setText('summaryContact', contactLines || '—');

    const total = calcTotal();
    setText('summaryPrice', '€' + total.toFixed(0));
  }

  /* ---------- Validazione per step ---------- */
  function validateStep(step) {
    let valid = true;

    const requireField = (id) => {
      const el = document.getElementById(id);
      if (!el) return;
      if (!el.value.trim()) { el.classList.add('is-error'); valid = false; }
      else el.classList.remove('is-error');
    };

    if (step === 1) {
      requireField('motoBrand'); requireField('motoModel'); requireField('motoCc');
    } else if (step === 2) {
      requireField('addressPickup'); requireField('addressDelivery');
    } else if (step === 3) {
      requireField('pickupDate');
      const dateEl = document.getElementById('pickupDate');
      if (dateEl && dateEl.value) {
        const chosen = new Date(dateEl.value + 'T00:00:00');
        const today  = new Date(); today.setHours(0, 0, 0, 0);
        if (chosen <= today) { dateEl.classList.add('is-error'); valid = false; }
      }
    } else if (step === 4) {
      requireField('clientName'); requireField('clientEmail');
      requireField('clientPhone'); requireField('clientFiscal');
      const priv = document.getElementById('privacyAccept');
      if (priv && !priv.checked) {
        priv.closest('.quote-form__checkbox').style.color = 'var(--danger-color)';
        valid = false;
      } else if (priv) {
        priv.closest('.quote-form__checkbox').style.color = '';
      }
      const emailEl = document.getElementById('clientEmail');
      if (emailEl && emailEl.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value)) {
        emailEl.classList.add('is-error'); valid = false;
      }
    }

    return valid;
  }

  /* ---------- Navigazione ---------- */
  function goNext() {
    if (!validateStep(currentStep)) return;
    if (currentStep < SUMMARY_STEP) {
      currentStep++;
      renderStep(currentStep);
    }
  }

  function goPrev() {
    if (currentStep > 1 && currentStep !== PAYMENT_STEP && currentStep !== SUCCESS_STEP) {
      currentStep--;
      renderStep(currentStep);
    }
  }

  /* ---------- STEP 5 → STEP 6: salva preventivo + crea PaymentIntent ---------- */
  function handleConfirm() {
    if (!stripeInstance) {
      alert('Il sistema di pagamento non è disponibile al momento. Configura la chiave pubblica Stripe.');
      return;
    }

    const rd = window._quoteRouteData || null;
    const deliveryType = selectedDelivery();
    const bagsPrice    = parseInt(val('motoBags')) || 0;
    const transportCost = rd ? rd.total_cost : BASE_PRICE;
    const total = transportCost + (DELIVERY_SURCHARGE[deliveryType] || 0) + bagsPrice;

    const payload = {
      marca_moto:             val('motoBrand'),
      modello_moto:           val('motoModel'),
      cilindrata:             val('motoCc'),
      borse_laterali:         bagsPrice,
      indirizzo_ritiro:       val('addressPickup'),
      indirizzo_consegna:     val('addressDelivery'),
      distanza_km:            rd ? rd.distance_km : null,
      tipo_consegna:          deliveryType,
      data_ritiro:            val('pickupDate'),
      nome_cliente:           val('clientName'),
      email_cliente:          val('clientEmail'),
      telefono_cliente:       val('clientPhone'),
      codice_fiscale_cliente: val('clientFiscal'),
      prezzo_base:            transportCost,
      prezzo_finale:          total,
    };

    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Caricamento...';

    fetch('/api/create-payment-intent', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    })
      .then((r) => r.json())
      .then((data) => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg> Vai al pagamento <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>';

        if (!data.success) {
          alert('Errore: ' + (data.error || 'Riprova più tardi.'));
          return;
        }

        currentClientSecret  = data.clientSecret;
        currentPreventivoId  = data.preventivoId;
        currentImporto       = data.importo;

        // Popola riepilogo importo nello step pagamento
        const motoLabel = [val('motoBrand'), val('motoModel'), val('motoCc')].filter(Boolean).join(' ');
        setText('paymentSummaryMoto', motoLabel || 'Trasporto moto');
        setText('paymentSummaryTotal', '€' + parseFloat(data.importo).toFixed(2));

        // Monta Stripe Payment Element
        mountStripeElement(data.clientSecret);

        currentStep = PAYMENT_STEP;
        renderStep(currentStep);
      })
      .catch(() => {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg> Vai al pagamento <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>';
        alert('Errore di rete. Controlla la connessione e riprova.');
      });
  }

  /* ---------- Monta Stripe Payment Element ---------- */
  function mountStripeElement(clientSecret) {
    if (!stripeInstance) {
      alert('Sistema di pagamento non disponibile. Assicurati che la chiave Stripe sia configurata.');
      return;
    }
    if (stripePaymentElement) {
      stripePaymentElement.unmount();
      stripePaymentElement = null;
    }

    stripeElements = stripeInstance.elements({
      clientSecret: clientSecret,
      appearance: {
        theme: 'stripe',
        variables: {
          colorPrimary:       '#0284c7',
          colorBackground:    '#ffffff',
          colorText:          '#1e293b',
          colorDanger:        '#ef4444',
          fontFamily:         'Inter, system-ui, sans-serif',
          borderRadius:       '8px',
          spacingUnit:        '4px',
        },
      },
      locale: 'it',
    });

    stripePaymentElement = stripeElements.create('payment', {
      layout: 'tabs',
    });
    stripePaymentElement.mount('#stripePaymentElement');
  }

  /* ---------- STEP 6: esegui pagamento ---------- */
  function handlePay() {
    if (!stripeInstance || !stripeElements || !currentClientSecret) return;

    const errEl = document.getElementById('stripePaymentError');
    if (errEl) { errEl.textContent = ''; errEl.style.display = 'none'; }

    payBtn.disabled = true;
    payBtn.innerHTML = '<svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Elaborazione...';

    stripeInstance.confirmPayment({
      elements: stripeElements,
      confirmParams: {
        return_url: window.location.origin + '/payment-success.php?preventivo_id=' + (currentPreventivoId || ''),
      },
      redirect: 'if_required',
    }).then(function (result) {
      payBtn.disabled = false;
      payBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg> Paga ora';

      if (result.error) {
        if (errEl) {
          errEl.textContent = result.error.message || 'Pagamento non riuscito. Riprova.';
          errEl.style.display = 'block';
        }
        return;
      }

      // Pagamento completato in-modal (no redirect)
      if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
        showSuccess();
      }
    });
  }

  /* ---------- Mostra step successo ---------- */
  function showSuccess() {
    setText('successEmail', val('clientEmail'));
    setText('successPreventivoId', '#' + (currentPreventivoId || '—'));
    setText('successImporto', currentImporto ? '€' + parseFloat(currentImporto).toFixed(2) : '—');
    currentStep = SUCCESS_STEP;
    renderStep(currentStep);
  }

  /* ---------- STEP 5: salva preventivo come bozza ---------- */
  function handleSaveDraft() {
    const rd           = window._quoteRouteData || null;
    const deliveryType = selectedDelivery();
    const bagsPrice    = parseInt(val('motoBags')) || 0;
    const transportCost = rd ? rd.total_cost : BASE_PRICE;
    const total        = transportCost + (DELIVERY_SURCHARGE[deliveryType] || 0) + bagsPrice;

    const payload = {
      marca_moto:             val('motoBrand'),
      modello_moto:           val('motoModel'),
      cilindrata:             val('motoCc'),
      borse_laterali:         bagsPrice,
      indirizzo_ritiro:       val('addressPickup'),
      indirizzo_consegna:     val('addressDelivery'),
      distanza_km:            rd ? rd.distance_km : null,
      tipo_consegna:          deliveryType,
      data_ritiro:            val('pickupDate'),
      nome_cliente:           val('clientName'),
      email_cliente:          val('clientEmail'),
      telefono_cliente:       val('clientPhone'),
      codice_fiscale_cliente: val('clientFiscal'),
      prezzo_finale:          total,
      route_data_json:        rd ? JSON.stringify(rd) : null,
    };

    if (saveDraftBtn) {
      saveDraftBtn.disabled = true;
      saveDraftBtn.textContent = 'Salvataggio...';
    }

    fetch('/api/salva-bozza', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload),
    })
      .then((r) => r.json())
      .then((data) => {
        if (saveDraftBtn) {
          saveDraftBtn.disabled = false;
          saveDraftBtn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg> Salva per dopo';
        }
        if (!data.success) {
          alert('Errore: ' + (data.error || 'Impossibile salvare la bozza.'));
          return;
        }
        const savedPanel = document.getElementById('quoteDraftSaved');
        if (savedPanel) {
          if (data.scadenza_il) {
            const d = new Date(data.scadenza_il.replace(' ', 'T'));
            const formatted = d.toLocaleDateString('it-IT', { day: 'numeric', month: 'long', year: 'numeric' });
            const expiryEl = document.getElementById('quoteDraftExpiry');
            if (expiryEl) expiryEl.textContent = formatted;
          }
          savedPanel.hidden = false;
          savedPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      })
      .catch(() => {
        if (saveDraftBtn) {
          saveDraftBtn.disabled = false;
          saveDraftBtn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg> Salva per dopo';
        }
        alert('Errore di rete. Controlla la connessione e riprova.');
      });
  }

  /* ---------- Gestione opzioni consegna ---------- */
  function updateDeliverySelection() {
    document.querySelectorAll('.quote-delivery-option').forEach((opt) => {
      const radio = opt.querySelector('input[type="radio"]');
      opt.classList.toggle('quote-delivery-option--selected', radio && radio.checked);
    });
  }

  document.querySelectorAll('.quote-delivery-option').forEach((opt) => {
    opt.addEventListener('click', function () {
      const radio = this.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
      updateDeliverySelection();
    });
  });

  /* ---------- Event listener ---------- */
  document.querySelectorAll('.open-quote-modal').forEach((btn) => {
    btn.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
  });

  closeBtn && closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
  });

  nextBtn    && nextBtn.addEventListener('click', goNext);
  prevBtn    && prevBtn.addEventListener('click', goPrev);
  confirmBtn && confirmBtn.addEventListener('click', handleConfirm);
  payBtn     && payBtn.addEventListener('click', handlePay);
  if (saveDraftBtn) saveDraftBtn.addEventListener('click', handleSaveDraft);

  const successCloseBtn = document.getElementById('quoteSuccessClose');
  if (successCloseBtn) successCloseBtn.addEventListener('click', closeModal);

  document.querySelectorAll('.quote-form__input, .quote-form__select').forEach((el) => {
    el.addEventListener('input', function () { this.classList.remove('is-error'); });
  });

  /* Gestisce la ripresa di una bozza avviata da window.resumeDraft() */
  document.addEventListener('quote:resumePayment', function (e) {
    const { clientSecret, preventivoId, importo, motoLabel } = e.detail;
    currentClientSecret = clientSecret;
    currentPreventivoId = preventivoId;
    currentImporto      = importo;
    setText('paymentSummaryMoto',  motoLabel || 'Trasporto moto');
    setText('paymentSummaryTotal', '€' + parseFloat(importo).toFixed(2));
    mountStripeElement(clientSecret);
    currentStep = PAYMENT_STEP;
    renderStep(currentStep);
  });

  /* API pubblica per window.resumeDraft */
  window._quoteModal = {
    goToPaymentStep: function (clientSecret, preventivoId, importo, motoLabel) {
      currentClientSecret = clientSecret;
      currentPreventivoId = preventivoId;
      currentImporto      = importo;
      setText('paymentSummaryMoto',  motoLabel || 'Trasporto moto');
      setText('paymentSummaryTotal', '€' + parseFloat(importo).toFixed(2));
      mountStripeElement(clientSecret);
      currentStep = PAYMENT_STEP;
      renderStep(currentStep);
    },
  };

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
} catch (e) {
  console.error('[quote-modal] Errore inizializzazione:', e);
}

/* ========== Riprende una bozza dalla dashboard ========== */
window.resumeDraft = function (draftId, draftData) {
  console.log('[resumeDraft] chiamata per bozza id=' + draftId);

  var overlay = document.getElementById('quoteModal');
  if (!overlay) {
    console.error('[resumeDraft] #quoteModal non trovato nel DOM. Verifica che quote-modal.php sia incluso nella pagina.');
    alert('Errore: il sistema di pagamento non è disponibile. Ricarica la pagina e riprova.');
    return;
  }

  // Nascondi tutti gli step e i pulsanti footer
  for (var i = 1; i <= 7; i++) {
    var p = document.getElementById('quoteStep' + i);
    if (p) p.classList.add('quote-step--hidden');
  }
  ['quotePrevBtn', 'quoteNextBtn', 'quoteConfirmBtn', 'quotePayBtn', 'quoteSaveDraftBtn'].forEach(function (id) {
    var b = document.getElementById(id);
    if (b) b.style.display = 'none';
  });

  // Spinner di caricamento — inserito PRIMA del footer
  var spinner = document.createElement('div');
  spinner.id = 'quoteDraftLoadingPanel';
  spinner.style.cssText = 'display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;padding:3rem 1rem;flex:1;min-height:160px;';
  spinner.innerHTML = '<svg class="spin" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg><p style="color:#64748b;margin:0;">Preparazione pagamento in corso&hellip;</p>';

  var modalInner = overlay.querySelector('.quote-modal');
  var footer = modalInner ? modalInner.querySelector('.quote-modal__footer') : null;
  if (footer) {
    modalInner.insertBefore(spinner, footer);
  } else if (modalInner) {
    modalInner.appendChild(spinner);
  }

  // Apri l'overlay
  overlay.classList.add('is-open');
  overlay.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';

  function closeOnError() {
    if (spinner.parentNode) spinner.parentNode.removeChild(spinner);
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  fetch('/api/create-payment-intent', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ preventivo_id: draftId }),
  })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      if (spinner.parentNode) spinner.parentNode.removeChild(spinner);

      if (!data.success) {
        closeOnError();
        alert('Errore: ' + (data.error || 'Impossibile riprendere la bozza.'));
        return;
      }

      if (!window._quoteModal) {
        closeOnError();
        alert('Errore interno: sistema modale non inizializzato. Ricarica la pagina.');
        return;
      }

      var motoLabel = [draftData.marca_moto, draftData.modello_moto, draftData.cilindrata]
        .filter(Boolean).join(' ');
      window._quoteModal.goToPaymentStep(data.clientSecret, data.preventivoId, data.importo, motoLabel);
    })
    .catch(function () {
      closeOnError();
      alert('Errore di rete. Controlla la connessione e riprova.');
    });
};

