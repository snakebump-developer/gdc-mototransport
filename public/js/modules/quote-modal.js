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

  /* ---------- Catalogo moto: combobox con ricerca ---------- */

  // Cache in memoria: caricati una sola volta all'apertura della modale
  let _allMarche  = [];   // ['Aprilia', 'BMW', ...]
  let _allModelli = [];   // ['1200 GS', 'S 1000 RR', ...] della marca attiva
  let _selectedBrandConfirmed = false;  // true = marca scelta dal catalogo
  let _selectedModelConfirmed = false;  // true = modello scelto dal catalogo

  const MIN_CHARS = 1; // inizia a filtrare dal 1° carattere (era 3, ora più reattivo)

  /* --- Utility combobox generica --- */
  function createCombobox(inputId, listId, opts) {
    /*
     * opts.getItems(query)   → array di stringhe filtrate
     * opts.onSelect(value, fromCatalog) → callback quando l'utente sceglie
     * opts.altroLabel        → etichetta voce "altro"
     * opts.placeholder       → hint nel dropdown quando i chars sono pochi
     */
    const input  = document.getElementById(inputId);
    const list   = document.getElementById(listId);
    if (!input || !list) return;

    let activeIndex = -1;
    let isOpen = false;

    function openList() {
      list.hidden = false;
      isOpen = true;
      input.setAttribute('aria-expanded', 'true');
    }

    function closeList() {
      list.hidden = true;
      isOpen = false;
      activeIndex = -1;
      input.setAttribute('aria-expanded', 'false');
    }

    function renderList(query) {
      list.innerHTML = '';
      activeIndex = -1;

      const q = query.trim();

      if (q.length < MIN_CHARS) {
        const hint = document.createElement('li');
        hint.className = 'moto-combobox__hint';
        hint.textContent = opts.placeholder || 'Digita per cercare…';
        list.appendChild(hint);
        openList();
        return;
      }

      const items = opts.getItems(q);

      items.forEach(function(item) {
        const li = document.createElement('li');
        li.className = 'moto-combobox__item';
        li.setAttribute('role', 'option');
        // Evidenzia i caratteri corrispondenti
        const idx = item.toLowerCase().indexOf(q.toLowerCase());
        if (idx !== -1) {
          li.innerHTML =
            escHtml(item.slice(0, idx)) +
            '<strong>' + escHtml(item.slice(idx, idx + q.length)) + '</strong>' +
            escHtml(item.slice(idx + q.length));
        } else {
          li.textContent = item;
        }
        li.addEventListener('mousedown', function(e) {
          e.preventDefault();
          selectItem(item, true);
        });
        list.appendChild(li);
      });

      // Voce "Altro" in fondo
      if (opts.altroLabel) {
        const sep = document.createElement('li');
        sep.className = 'moto-combobox__item moto-combobox__item--altro';
        sep.setAttribute('role', 'option');
        sep.textContent = opts.altroLabel;
        sep.addEventListener('mousedown', function(e) {
          e.preventDefault();
          selectItem(q, false); // usa il testo digitato, non catalogo
        });
        list.appendChild(sep);
      }

      if (items.length === 0 && !opts.altroLabel) {
        const empty = document.createElement('li');
        empty.className = 'moto-combobox__hint';
        empty.textContent = 'Nessun risultato';
        list.appendChild(empty);
      }

      openList();
    }

    function selectItem(value, fromCatalog) {
      input.value = value;
      closeList();
      if (opts.onSelect) opts.onSelect(value, fromCatalog);
    }

    function highlightItem(index) {
      const items = list.querySelectorAll('.moto-combobox__item');
      items.forEach(function(el, i) {
        el.setAttribute('aria-selected', i === index ? 'true' : 'false');
      });
      if (items[index]) {
        items[index].scrollIntoView({ block: 'nearest' });
      }
    }

    input.addEventListener('input', function() {
      this.classList.remove('is-error');
      renderList(this.value);
    });

    input.addEventListener('focus', function() {
      if (this.value.length >= MIN_CHARS) renderList(this.value);
    });

    input.addEventListener('blur', function() {
      // Piccolo delay per permettere il mousedown sull'item
      setTimeout(closeList, 150);
    });

    input.addEventListener('keydown', function(e) {
      if (!isOpen) return;
      const items = list.querySelectorAll('.moto-combobox__item');
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = Math.min(activeIndex + 1, items.length - 1);
        highlightItem(activeIndex);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(activeIndex - 1, 0);
        highlightItem(activeIndex);
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (activeIndex >= 0 && items[activeIndex]) {
          items[activeIndex].dispatchEvent(new MouseEvent('mousedown'));
        }
      } else if (e.key === 'Escape') {
        closeList();
      }
    });
  }

  function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  /* --- Marche --- */
  function loadMarche() {
    if (_allMarche.length > 0) return; // già caricate
    fetch('/api/moto-catalogo.php?action=marche')
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success && Array.isArray(data.marche)) {
          _allMarche = data.marche;
        }
      })
      .catch(function() {});
  }

  /* --- Modelli --- */
  function loadModelliForBrand(marca) {
    _allModelli = [];
    const inp = document.getElementById('motoModelInput');
    if (inp) { inp.value = ''; inp.disabled = true; inp.placeholder = 'Caricamento…'; }

    fetch('/api/moto-catalogo.php?action=modelli&marca=' + encodeURIComponent(marca))
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success && Array.isArray(data.modelli)) {
          _allModelli = data.modelli;
        }
        if (inp) { inp.disabled = false; inp.placeholder = 'Scrivi il modello…'; }
      })
      .catch(function() {
        if (inp) { inp.disabled = false; inp.placeholder = 'Scrivi il modello…'; }
      });
  }

  /* --- Aggiorna campi hidden e stato UI --- */
  function syncMotoHiddenFields() {
    const brandInp = document.getElementById('motoBrandInput');
    const modelInp = document.getElementById('motoModelInput');
    const hidBrand = document.getElementById('motoBrand');
    const hidModel = document.getElementById('motoModel');
    if (!hidBrand || !hidModel) return;

    hidBrand.value = brandInp ? brandInp.value.trim() : '';
    hidModel.value = modelInp ? modelInp.value.trim() : '';

    // Banner "Altro": appare solo quando entrambi marca e modello sono compilati
    // e almeno uno dei due non proviene dal catalogo ufficiale
    const altroSec = document.getElementById('motoAltroSection');
    if (altroSec) {
      const hasBrand = hidBrand.value !== '';
      const hasModel = hidModel.value !== '';
      const isCustom = !_selectedBrandConfirmed || !_selectedModelConfirmed;
      altroSec.style.display = hasBrand && hasModel && isCustom ? '' : 'none';
    }
  }

  /* --- Inizializza i due combobox --- */
  createCombobox('motoBrandInput', 'motoBrandList', {
    placeholder: 'Digita per cercare la marca…',
    altroLabel:  '✏️  Non in lista — usa questo nome',
    getItems: function(q) {
      return _allMarche.filter(function(m) {
        return m.toLowerCase().indexOf(q.toLowerCase()) !== -1;
      }).slice(0, 40);
    },
    onSelect: function(value, fromCatalog) {
      _selectedBrandConfirmed = fromCatalog;
      _selectedModelConfirmed = false;
      _allModelli = [];

      const modelGrp = document.getElementById('motoModelGroup');
      const modelInp = document.getElementById('motoModelInput');
      if (modelInp) modelInp.value = '';

      if (fromCatalog) {
        if (modelGrp) modelGrp.style.display = '';
        loadModelliForBrand(value);
      } else {
        // Marca libera: mostra ugualmente il campo modello ma senza catalogo
        _allModelli = [];
        if (modelGrp) modelGrp.style.display = '';
        const inp = document.getElementById('motoModelInput');
        if (inp) inp.placeholder = 'Scrivi il modello…';
      }
      syncMotoHiddenFields();
    },
  });

  createCombobox('motoModelInput', 'motoModelList', {
    placeholder: 'Digita per cercare il modello…',
    altroLabel:  '✏️  Non in lista — usa questo nome',
    getItems: function(q) {
      return _allModelli.filter(function(m) {
        return m.toLowerCase().indexOf(q.toLowerCase()) !== -1;
      }).slice(0, 40);
    },
    onSelect: function(value, fromCatalog) {
      _selectedModelConfirmed = fromCatalog;
      syncMotoHiddenFields();
    },
  });

  // Aggiorna hidden fields anche quando l'utente smette di digitare (blur)
  ['motoBrandInput', 'motoModelInput'].forEach(function(id) {
    const el = document.getElementById(id);
    if (el) el.addEventListener('blur', function() {
      setTimeout(syncMotoHiddenFields, 200);
    });
    if (el) el.addEventListener('input', function() {
      // Se il testo cambia dopo una selezione, la selezione non è più confermata
      if (id === 'motoBrandInput') _selectedBrandConfirmed = false;
      if (id === 'motoModelInput') _selectedModelConfirmed = false;
      syncMotoHiddenFields();
    });
  });

  function openModal() {
    currentStep = 1;
    renderStep(currentStep);
    prefillFromUserData();
    loadMarche();
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
    // Reset combobox marche/modelli
    _allModelli = [];
    _selectedBrandConfirmed = false;
    _selectedModelConfirmed = false;
    const brandInp = document.getElementById('motoBrandInput');
    const modelInp = document.getElementById('motoModelInput');
    if (brandInp) { brandInp.value = ''; brandInp.classList.remove('is-error'); var e1 = document.getElementById('motoBrandInput-error'); if (e1) e1.textContent = ''; }
    if (modelInp) { modelInp.value = ''; modelInp.classList.remove('is-error'); var e2 = document.getElementById('motoModelInput-error'); if (e2) e2.textContent = ''; }
    const modelGrp = document.getElementById('motoModelGroup');
    if (modelGrp) modelGrp.style.display = 'none';
    const altroSec = document.getElementById('motoAltroSection');
    if (altroSec) altroSec.style.display = 'none';
    // Chiudi eventuali dropdown aperti
    ['motoBrandList', 'motoModelList'].forEach(function(id) {
      const el = document.getElementById(id);
      if (el) el.hidden = true;
    });

    ['motoBrand', 'motoModel', 'motoCc', 'motoBags', 'addressPickup', 'addressDelivery',
      'pickupDate', 'clientName', 'clientEmail', 'clientPhone', 'clientFiscal'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) { el.value = ''; el.classList.remove('is-error'); }
      const errEl = document.getElementById(id + '-error');
      if (errEl) errEl.textContent = '';
    });
    const priv = document.getElementById('privacyAccept');
    if (priv) priv.checked = false;
    const privErr = document.getElementById('privacyAccept-error');
    if (privErr) privErr.textContent = '';
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
    setText('summaryPickup', pickup || '—');
    setText('summaryDeliveryAddr', delivery || '—');
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
    setText('summaryEmail', email || '—');
    setText('summaryPhone', phone || '—');
    setText('summaryFiscal', fiscal || '—');

    const total = calcTotal();
    setText('summaryPrice', '€' + total.toFixed(0));
  }

  /* ---------- Helper di validazione ---------- */
  function showFieldError(inputId, message) {
    const el    = document.getElementById(inputId);
    const errEl = document.getElementById(inputId + '-error');
    if (el)    el.classList.add('is-error');
    if (errEl) errEl.textContent = message;
  }

  function clearFieldError(inputId) {
    const el    = document.getElementById(inputId);
    const errEl = document.getElementById(inputId + '-error');
    if (el)    el.classList.remove('is-error');
    if (errEl) errEl.textContent = '';
  }

  function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
  }

  function validatePhone(phone) {
    const c = phone.replace(/[\s\-\.]/g, '');
    return /^(\+39|0039)?3\d{9}$/.test(c) || /^(\+39|0039)?0\d{6,10}$/.test(c);
  }

  function validateCodiceFiscale(cf) {
    return /^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i.test(cf);
  }

  function validateCilindrata(cc) {
    var m = cc.match(/^(\d+)\s*(cc|cm3)?$/i);
    if (!m) return false;
    var n = parseInt(m[1], 10);
    return n >= 50 && n <= 2999;
  }

  /* ---------- Validazione per step ---------- */
  function validateStep(step) {
    var valid = true;

    if (step === 1) {
      var brandInp = document.getElementById('motoBrandInput');
      var modelInp = document.getElementById('motoModelInput');
      var modelGrp = document.getElementById('motoModelGroup');
      var ccEl     = document.getElementById('motoCc');

      // Marca
      if (!brandInp || !brandInp.value.trim()) {
        showFieldError('motoBrandInput', 'Inserisci la marca della moto');
        valid = false;
      } else {
        clearFieldError('motoBrandInput');
      }

      // Modello (richiede che la marca sia compilata per mostrare il campo)
      if (brandInp && brandInp.value.trim()) {
        if (modelGrp && modelGrp.style.display === 'none') modelGrp.style.display = '';
        if (!modelInp || !modelInp.value.trim()) {
          showFieldError('motoModelInput', 'Inserisci il modello della moto');
          valid = false;
        } else {
          clearFieldError('motoModelInput');
        }
      }

      // Cilindrata
      if (!ccEl || !ccEl.value.trim()) {
        showFieldError('motoCc', 'Inserisci la cilindrata della moto');
        valid = false;
      } else if (!validateCilindrata(ccEl.value.trim())) {
        showFieldError('motoCc', 'Formato non valido — inserisci un numero tra 50 e 2999 (es. 1000 o 1000cc)');
        valid = false;
      } else {
        clearFieldError('motoCc');
      }

    } else if (step === 2) {
      var pickupEl   = document.getElementById('addressPickup');
      var deliveryEl = document.getElementById('addressDelivery');

      if (!pickupEl || !pickupEl.value.trim()) {
        showFieldError('addressPickup', "Inserisci l'indirizzo di ritiro");
        valid = false;
      } else if (pickupEl.value.trim().length < 10) {
        showFieldError('addressPickup', 'Indirizzo troppo breve — includi via, numero civico e città');
        valid = false;
      } else {
        clearFieldError('addressPickup');
      }

      if (!deliveryEl || !deliveryEl.value.trim()) {
        showFieldError('addressDelivery', "Inserisci l'indirizzo di consegna");
        valid = false;
      } else if (deliveryEl.value.trim().length < 10) {
        showFieldError('addressDelivery', 'Indirizzo troppo breve — includi via, numero civico e città');
        valid = false;
      } else if (pickupEl && pickupEl.value.trim() === deliveryEl.value.trim()) {
        showFieldError('addressDelivery', "L'indirizzo di consegna deve essere diverso da quello di ritiro");
        valid = false;
      } else {
        clearFieldError('addressDelivery');
      }

    } else if (step === 3) {
      var dateEl = document.getElementById('pickupDate');
      if (!dateEl || !dateEl.value) {
        showFieldError('pickupDate', 'Seleziona una data di ritiro');
        valid = false;
      } else {
        var chosen  = new Date(dateEl.value + 'T00:00:00');
        var today   = new Date(); today.setHours(0, 0, 0, 0);
        var maxDate = new Date(); maxDate.setMonth(maxDate.getMonth() + 6);
        if (chosen <= today) {
          showFieldError('pickupDate', 'La data deve essere almeno domani');
          valid = false;
        } else if (chosen > maxDate) {
          showFieldError('pickupDate', 'La data non può superare i 6 mesi da oggi');
          valid = false;
        } else {
          clearFieldError('pickupDate');
        }
      }

    } else if (step === 4) {
      var nameEl   = document.getElementById('clientName');
      var emailEl  = document.getElementById('clientEmail');
      var phoneEl  = document.getElementById('clientPhone');
      var fiscalEl = document.getElementById('clientFiscal');
      var privEl   = document.getElementById('privacyAccept');

      // Nome e cognome
      if (!nameEl || !nameEl.value.trim()) {
        showFieldError('clientName', 'Inserisci il tuo nome e cognome');
        valid = false;
      } else if (nameEl.value.trim().split(/\s+/).length < 2) {
        showFieldError('clientName', 'Inserisci sia il nome che il cognome');
        valid = false;
      } else {
        clearFieldError('clientName');
      }

      // Email
      if (!emailEl || !emailEl.value.trim()) {
        showFieldError('clientEmail', 'Inserisci la tua email');
        valid = false;
      } else if (!validateEmail(emailEl.value.trim())) {
        showFieldError('clientEmail', 'Email non valida — es. nome@dominio.it');
        valid = false;
      } else {
        clearFieldError('clientEmail');
      }

      // Telefono
      if (!phoneEl || !phoneEl.value.trim()) {
        showFieldError('clientPhone', 'Inserisci il numero di telefono');
        valid = false;
      } else if (!validatePhone(phoneEl.value.trim())) {
        showFieldError('clientPhone', 'Numero non valido — es. 3285449887 oppure +393285449887');
        valid = false;
      } else {
        clearFieldError('clientPhone');
      }

      // Codice fiscale
      if (!fiscalEl || !fiscalEl.value.trim()) {
        showFieldError('clientFiscal', 'Inserisci il codice fiscale');
        valid = false;
      } else if (!validateCodiceFiscale(fiscalEl.value.trim())) {
        showFieldError('clientFiscal', 'Codice fiscale non valido — deve essere di 16 caratteri (es. RSSMRA85M01H501Z)');
        valid = false;
      } else {
        clearFieldError('clientFiscal');
      }

      // Privacy
      if (privEl && !privEl.checked) {
        var privErrEl = document.getElementById('privacyAccept-error');
        if (privErrEl) privErrEl.textContent = 'Devi accettare i Termini e la Privacy Policy per continuare';
        valid = false;
      } else if (privEl) {
        var privErrEl2 = document.getElementById('privacyAccept-error');
        if (privErrEl2) privErrEl2.textContent = '';
      }
    }

    return valid;
  }

  /* ---------- Navigazione ---------- */
  function goNext() {
    if (currentStep === 1) syncMotoHiddenFields();
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
    syncMotoHiddenFields();
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
    syncMotoHiddenFields();
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
    el.addEventListener('input', function () {
      this.classList.remove('is-error');
      var errEl = document.getElementById(this.id + '-error');
      if (errEl) errEl.textContent = '';
    });
  });

  /* Validazione in tempo reale all'uscita dal campo (blur) */
  (function addBlurValidators() {
    // Cilindrata
    var ccEl = document.getElementById('motoCc');
    if (ccEl) ccEl.addEventListener('blur', function () {
      if (!this.value.trim()) return;
      if (!validateCilindrata(this.value.trim())) {
        showFieldError('motoCc', 'Formato non valido — inserisci un numero tra 50 e 2999 (es. 1000 o 1000cc)');
      } else {
        clearFieldError('motoCc');
      }
    });

    // Indirizzi
    ['addressPickup', 'addressDelivery'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('blur', function () {
        if (!this.value.trim()) return;
        if (this.value.trim().length < 10) {
          showFieldError(id, 'Indirizzo troppo breve — includi via, numero civico e città');
        } else {
          clearFieldError(id);
        }
      });
    });

    // Data di ritiro
    var dateEl = document.getElementById('pickupDate');
    if (dateEl) dateEl.addEventListener('change', function () {
      if (!this.value) return;
      var chosen  = new Date(this.value + 'T00:00:00');
      var today   = new Date(); today.setHours(0, 0, 0, 0);
      var maxDate = new Date(); maxDate.setMonth(maxDate.getMonth() + 6);
      if (chosen <= today) {
        showFieldError('pickupDate', 'La data deve essere almeno domani');
      } else if (chosen > maxDate) {
        showFieldError('pickupDate', 'La data non può superare i 6 mesi da oggi');
      } else {
        clearFieldError('pickupDate');
      }
    });

    // Nome (almeno 2 parole)
    var nameEl = document.getElementById('clientName');
    if (nameEl) nameEl.addEventListener('blur', function () {
      if (!this.value.trim()) return;
      if (this.value.trim().split(/\s+/).length < 2) {
        showFieldError('clientName', 'Inserisci sia il nome che il cognome');
      } else {
        clearFieldError('clientName');
      }
    });

    // Email
    var emailEl = document.getElementById('clientEmail');
    if (emailEl) emailEl.addEventListener('blur', function () {
      if (!this.value.trim()) return;
      if (!validateEmail(this.value.trim())) {
        showFieldError('clientEmail', 'Email non valida — es. nome@dominio.it');
      } else {
        clearFieldError('clientEmail');
      }
    });

    // Telefono
    var phoneEl = document.getElementById('clientPhone');
    if (phoneEl) phoneEl.addEventListener('blur', function () {
      if (!this.value.trim()) return;
      if (!validatePhone(this.value.trim())) {
        showFieldError('clientPhone', 'Numero non valido — es. 3285449887 oppure +393285449887');
      } else {
        clearFieldError('clientPhone');
      }
    });

    // Codice fiscale
    var fiscalEl = document.getElementById('clientFiscal');
    if (fiscalEl) fiscalEl.addEventListener('blur', function () {
      if (!this.value.trim()) return;
      if (!validateCodiceFiscale(this.value.trim())) {
        showFieldError('clientFiscal', 'Codice fiscale non valido — deve essere di 16 caratteri (es. RSSMRA85M01H501Z)');
      } else {
        clearFieldError('clientFiscal');
      }
    });

    // Privacy checkbox
    var privEl = document.getElementById('privacyAccept');
    if (privEl) privEl.addEventListener('change', function () {
      if (this.checked) {
        var privErrEl = document.getElementById('privacyAccept-error');
        if (privErrEl) privErrEl.textContent = '';
      }
    });
  })();

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

