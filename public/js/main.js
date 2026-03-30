// ===== ACTIVE NAV LINK ON SCROLL =====
(function () {
  'use strict';

  const sections = ['come-funziona', 'vantaggi', 'gallery', 'recensioni', 'chi-siamo'];
  const navLinks = document.querySelectorAll('.nav-menu a[data-section]');
  if (!navLinks.length) return;

  function setActive(id) {
    navLinks.forEach((a) => {
      a.classList.toggle('nav-active', a.dataset.section === id);
    });
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) setActive(entry.target.id);
      });
    },
    { rootMargin: '-40% 0px -55% 0px', threshold: 0 }
  );

  sections.forEach((id) => {
    const el = document.getElementById(id);
    if (el) observer.observe(el);
  });

  // Highlight on direct click
  navLinks.forEach((a) => {
    a.addEventListener('click', function () {
      setActive(this.dataset.section);
    });
  });
})();

// ===== DROPDOWN MENU =====
document.addEventListener('DOMContentLoaded', function () {
  const userButton = document.getElementById('userButton');
  const dropdownMenu = document.getElementById('dropdownMenu');

  if (userButton && dropdownMenu) {
    // Toggle dropdown al click
    userButton.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdownMenu.classList.toggle('show');
    });

    // Chiudi dropdown quando si clicca fuori
    document.addEventListener('click', function (e) {
      if (!userButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove('show');
      }
    });
  }

  // ===== MOBILE MENU =====
  const hamburgerMenu = document.getElementById('hamburgerMenu');
  const mobileMenu = document.getElementById('mobileMenu');
  const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
  const mobileMenuClose = document.getElementById('mobileMenuClose');

  if (hamburgerMenu && mobileMenu && mobileMenuOverlay) {
    // Apri menu mobile
    hamburgerMenu.addEventListener('click', function () {
      hamburgerMenu.classList.toggle('active');
      mobileMenu.classList.toggle('active');
      mobileMenuOverlay.classList.toggle('active');
      document.body.style.overflow = mobileMenu.classList.contains('active')
        ? 'hidden'
        : '';
    });

    // Chiudi menu mobile - bottone X
    if (mobileMenuClose) {
      mobileMenuClose.addEventListener('click', closeMobileMenu);
    }

    // Chiudi menu mobile - click su overlay
    mobileMenuOverlay.addEventListener('click', closeMobileMenu);

    // Chiudi menu mobile - click sui link
    const mobileMenuLinks = mobileMenu.querySelectorAll('a');
    mobileMenuLinks.forEach((link) => {
      link.addEventListener('click', closeMobileMenu);
    });

    // Funzione per chiudere il menu mobile
    function closeMobileMenu() {
      hamburgerMenu.classList.remove('active');
      mobileMenu.classList.remove('active');
      mobileMenuOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }

    // Chiudi menu mobile su resize se si passa a desktop
    window.addEventListener('resize', function () {
      if (window.innerWidth > 768 && mobileMenu.classList.contains('active')) {
        closeMobileMenu();
      }
    });
  }
});

// ===== SMOOTH SCROLL =====
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener('click', function (e) {
    const targetId = this.getAttribute('href');
    // Solo per anchor interni, non per # da solo
    if (targetId !== '#' && targetId.startsWith('#')) {
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        e.preventDefault();
        targetElement.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        });
      }
    }
  });
});

// ===== DETTAGLI ORDINE (Admin) =====
function viewOrderDetails(orderId) {
  alert('Funzionalità dettagli ordine #' + orderId + ' da implementare');
  // Qui puoi aprire un modal o reindirizzare a una pagina di dettaglio
}

// ===== CONFERMA AZIONI =====
document.querySelectorAll('form[onsubmit*="confirm"]').forEach((form) => {
  form.addEventListener('submit', function (e) {
    const message = this.getAttribute('onsubmit').match(/'([^']+)'/);
    if (message && !confirm(message[1])) {
      e.preventDefault();
    }
  });
});

// ===== VALIDAZIONE FORM CLIENT-SIDE =====
const forms = document.querySelectorAll('form.auth-form, form.profile-form');
forms.forEach((form) => {
  form.addEventListener('submit', function (e) {
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;

    inputs.forEach((input) => {
      if (!input.value.trim()) {
        isValid = false;
        input.style.borderColor = 'var(--danger-color)';
      } else {
        input.style.borderColor = '';
      }
    });

    // Validazione password se presente
    const passwordInput = form.querySelector('input[name="password"]');
    if (passwordInput && passwordInput.value) {
      const password = passwordInput.value;
      if (
        password.length < 8 ||
        !/[A-Za-z]/.test(password) ||
        !/\d/.test(password)
      ) {
        alert(
          'La password deve avere almeno 8 caratteri, una lettera e un numero'
        );
        passwordInput.style.borderColor = 'var(--danger-color)';
        e.preventDefault();
        return;
      }
    }

    if (!isValid) {
      e.preventDefault();
      alert('Compila tutti i campi obbligatori');
    }
  });
});

// ===== GALLERY SLIDER =====
(function () {
  'use strict';

  const slider = document.getElementById('gallerySlider');
  const prevBtn = document.getElementById('galleryPrev');
  const nextBtn = document.getElementById('galleryNext');

  if (!slider || !prevBtn || !nextBtn) return;

  let autoSlideTimer = null;
  const AUTO_INTERVAL = 3500;

  function getScrollStep() {
    const item = slider.querySelector('.gallery__item');
    if (!item) return 250;
    const sliderStyle = window.getComputedStyle(slider);
    const gap = parseFloat(sliderStyle.columnGap || sliderStyle.gap) || 24;
    return item.offsetWidth + gap;
  }

  function slideNext() {
    const maxScroll = slider.scrollWidth - slider.clientWidth;
    const target = slider.scrollLeft + getScrollStep();
    if (target >= maxScroll - 1) {
      slider.scrollTo({ left: 0, behavior: 'smooth' });
    } else {
      slider.scrollTo({ left: target, behavior: 'smooth' });
    }
  }

  function slidePrev() {
    const target = slider.scrollLeft - getScrollStep();
    if (target <= 0) {
      const maxScroll = slider.scrollWidth - slider.clientWidth;
      slider.scrollTo({ left: maxScroll, behavior: 'smooth' });
    } else {
      slider.scrollTo({ left: target, behavior: 'smooth' });
    }
  }

  function startAuto() {
    stopAuto();
    autoSlideTimer = setInterval(slideNext, AUTO_INTERVAL);
  }

  function stopAuto() {
    clearInterval(autoSlideTimer);
  }

  nextBtn.addEventListener('click', () => { slideNext(); startAuto(); });
  prevBtn.addEventListener('click', () => { slidePrev(); startAuto(); });

  // Pausa sull'hover, riprende all'uscita
  slider.addEventListener('mouseenter', stopAuto);
  slider.addEventListener('mouseleave', startAuto);

  // Touch: pausa al tocco, riprende al rilascio
  slider.addEventListener('touchstart', stopAuto, { passive: true });
  slider.addEventListener('touchend', startAuto, { passive: true });

  startAuto();
})();

// ===== AUTO-HIDE ALERTS =====
setTimeout(() => {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach((alert) => {
    alert.style.transition = 'opacity 0.5s';
    alert.style.opacity = '0';
    setTimeout(() => alert.remove(), 500);
  });
}, 5000);

// ===== MOBILE MENU (future implementation) =====
// Se vuoi aggiungere un menu hamburger per mobile, qui puoi implementarlo

console.log('StarterKit JS initialized');

// ===== MODALE PREVENTIVO MULTI-STEP =====
(function () {
  'use strict';

  /* ---------- Configurazione prezzi ---------- */
  const BASE_PRICE = 175; // prezzo base stimato
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
  let routeData = null; // { distance_km, duration_text, fuel_cost, toll_cost, total_cost, polyline }
  let pickupCoords = null;  // { lat, lng }
  let deliveryCoords = null; // { lat, lng }

  /* ---------- Elementi DOM ---------- */
  const overlay = document.getElementById('quoteModal');
  const closeBtn = document.getElementById('quoteModalClose');
  const prevBtn = document.getElementById('quotePrevBtn');
  const nextBtn = document.getElementById('quoteNextBtn');
  const confirmBtn = document.getElementById('quoteConfirmBtn');
  const stepperSteps = document.querySelectorAll('.quote-stepper__step');
  const stepperLines = document.querySelectorAll('.quote-stepper__line');

  if (!overlay) return; // modale non presente nella pagina

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
    // Reset campi
    ['motoBrand', 'motoModel', 'motoCc', 'motoBags', 'addressPickup', 'addressDelivery',
      'clientName', 'clientEmail', 'clientPhone', 'clientFiscal'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) { el.value = ''; el.classList.remove('is-error'); }
    });
    // Reset checkbox privacy
    const priv = document.getElementById('privacyAccept');
    if (priv) priv.checked = false;
    // Reset opzione consegna
    const standardOpt = document.querySelector('input[name="deliveryType"][value="Standard"]');
    if (standardOpt) standardOpt.checked = true;
    updateDeliverySelection();
    // Reset stato mappa/tratta
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
    // Reset route polyline se la mappa è inizializzata
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
    // Mostra/nascondi pannelli
    for (let i = 1; i <= TOTAL_STEPS; i++) {
      const panel = document.getElementById('quoteStep' + i);
      if (panel) panel.classList.toggle('quote-step--hidden', i !== step);
    }

    // Aggiorna stepper visivo
    stepperSteps.forEach((el, idx) => {
      const stepNum = idx + 1;
      el.classList.remove('active', 'completed');
      if (stepNum === step) el.classList.add('active');
      if (stepNum < step) el.classList.add('completed');
    });

    // Aggiorna le linee di connessione
    stepperLines.forEach((line, idx) => {
      line.classList.toggle('completed', idx + 1 < step);
    });

    // Mostra/nascondi bottoni
    prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
    nextBtn.style.display = step === TOTAL_STEPS ? 'none' : 'inline-flex';
    confirmBtn.style.display = step === TOTAL_STEPS ? 'inline-flex' : 'none';

    // Al riepilogo: compila il sommario
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

    // Prezzo dinamico basato su calcolo tratta
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
      // validazione email base
      const emailEl = document.getElementById('clientEmail');
      if (emailEl && emailEl.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value)) {
        emailEl.classList.add('is-error');
        valid = false;
      }
    }

    return valid;
  }

  /* ---------- Navigazione avanti ---------- */
  function goNext() {
    if (!validateStep(currentStep)) return;
    if (currentStep < TOTAL_STEPS) {
      currentStep++;
      renderStep(currentStep);
    }
  }

  /* ---------- Navigazione indietro ---------- */
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

  // Pulsanti di apertura
  document.querySelectorAll('.open-quote-modal').forEach((btn) => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      openModal();
    });
  });

  // Chiudi modale
  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closeModal();
  });

  // Naviga avanti/indietro
  nextBtn.addEventListener('click', goNext);
  prevBtn.addEventListener('click', goPrev);

  // Conferma e paga
  confirmBtn.addEventListener('click', function () {
    alert('Reindirizzamento al sistema di pagamento sicuro...\n(integrazione pagamento da configurare)');
  });

  // Tasto ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
  });

  // Rimuovi evidenziazione errori al digitare
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

// ===== GOOGLE MAPS: AUTOCOMPLETE, MAPPA INTERATTIVA, CALCOLO TRATTA =====
function initGoogleMaps() {
  'use strict';

  var pickupInput = document.getElementById('addressPickup');
  var deliveryInput = document.getElementById('addressDelivery');
  if (!pickupInput || !deliveryInput) return;

  // --- Stato locale ---
  var activeField = null;       // 'pickup' o 'delivery'
  var map = null;
  var routePreviewMap = null;
  var geocoder = null;
  var reverseTimeout = null;

  // Riferimenti globali per reset
  window._quoteRoutePolyline = null;
  window._quoteRouteMarkers = [];

  // --- Elementi DOM ---
  var mapContainer = document.getElementById('quoteMapContainer');
  var mapDiv = document.getElementById('quoteMap');
  var mapLabel = document.getElementById('quoteMapLabel');
  var mapAddress = document.getElementById('quoteMapAddress');
  var mapConfirmBtn = document.getElementById('quoteMapConfirmBtn');
  var mapCloseBtn = document.getElementById('quoteMapCloseBtn');
  var pickupMapBtn = document.getElementById('pickupMapBtn');
  var deliveryMapBtn = document.getElementById('deliveryMapBtn');
  var routeSummary = document.getElementById('routeSummary');
  var routePreviewDiv = document.getElementById('routePreviewMap');

  // --- Google Places Autocomplete ---
  var autocompleteOptions = {
    componentRestrictions: { country: 'it' },
    fields: ['formatted_address', 'geometry']
  };

  var pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput, autocompleteOptions);
  var deliveryAutocomplete = new google.maps.places.Autocomplete(deliveryInput, autocompleteOptions);

  pickupAutocomplete.addListener('place_changed', function () {
    var place = pickupAutocomplete.getPlace();
    if (place && place.geometry) {
      // Accedi alla IIFE tramite variabili globali esposte
      window._quotePickupCoords = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng()
      };
      pickupInput.value = place.formatted_address;
      pickupInput.classList.remove('is-error');
      tryCalculateRoute();
    }
  });

  deliveryAutocomplete.addListener('place_changed', function () {
    var place = deliveryAutocomplete.getPlace();
    if (place && place.geometry) {
      window._quoteDeliveryCoords = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng()
      };
      deliveryInput.value = place.formatted_address;
      deliveryInput.classList.remove('is-error');
      tryCalculateRoute();
    }
  });

  // --- Esponi le coordinate alla IIFE della modale ---
  // La IIFE legge pickupCoords/deliveryCoords; li agganciamo alla window per comunicazione
  window._quotePickupCoords = null;
  window._quoteDeliveryCoords = null;

  // --- Mappa interattiva per selezione indirizzo ---
  geocoder = new google.maps.Geocoder();

  function openMapForField(field) {
    activeField = field;
    mapLabel.textContent = field === 'pickup' ? 'Seleziona indirizzo di ritiro' : 'Seleziona indirizzo di consegna';
    mapAddress.textContent = 'Sposta la mappa per selezionare l\'indirizzo';
    mapContainer.style.display = 'block';

    if (!map) {
      map = new google.maps.Map(mapDiv, {
        center: { lat: 41.9028, lng: 12.4964 }, // Roma default
        zoom: 6,
        disableDefaultUI: true,
        zoomControl: true,
        gestureHandling: 'greedy',
        styles: [
          { featureType: 'poi', stylers: [{ visibility: 'off' }] },
          { featureType: 'transit', stylers: [{ visibility: 'off' }] }
        ]
      });

      // Reverse geocoding quando la mappa smette di muoversi
      map.addListener('idle', function () {
        clearTimeout(reverseTimeout);
        reverseTimeout = setTimeout(function () {
          reverseGeocode(map.getCenter());
        }, 400);
      });
    }

    // Se il campo ha già coordinate, centra lì
    var existingCoords = field === 'pickup' ? window._quotePickupCoords : window._quoteDeliveryCoords;
    if (existingCoords) {
      map.setCenter(existingCoords);
      map.setZoom(15);
    } else {
      // Prova geolocalizzazione browser
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function (pos) {
            map.setCenter({ lat: pos.coords.latitude, lng: pos.coords.longitude });
            map.setZoom(15);
          },
          function () {
            // Fallback: centro Italia
            map.setCenter({ lat: 41.9028, lng: 12.4964 });
            map.setZoom(6);
          },
          { timeout: 5000 }
        );
      }
    }

    // Scroll alla mappa
    mapContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function reverseGeocode(latlng) {
    geocoder.geocode({ location: latlng }, function (results, status) {
      if (status === 'OK' && results[0]) {
        mapAddress.textContent = results[0].formatted_address;
      } else {
        mapAddress.textContent = 'Indirizzo non trovato — sposta la mappa';
      }
    });
  }

  function confirmMapSelection() {
    var center = map.getCenter();
    var coords = { lat: center.lat(), lng: center.lng() };
    var address = mapAddress.textContent;

    if (address === 'Sposta la mappa per selezionare l\'indirizzo' || address === 'Indirizzo non trovato — sposta la mappa') {
      return; // Non confermare se non c'è un indirizzo valido
    }

    if (activeField === 'pickup') {
      pickupInput.value = address;
      pickupInput.classList.remove('is-error');
      window._quotePickupCoords = coords;
      // Chiudi mappa e apri mappa per consegna se vuoto
      mapContainer.style.display = 'none';
      if (!deliveryInput.value.trim()) {
        setTimeout(function () { openMapForField('delivery'); }, 300);
      } else {
        tryCalculateRoute();
      }
    } else {
      deliveryInput.value = address;
      deliveryInput.classList.remove('is-error');
      window._quoteDeliveryCoords = coords;
      mapContainer.style.display = 'none';
      tryCalculateRoute();
    }
  }

  // --- Event listeners mappa ---
  if (pickupMapBtn) {
    pickupMapBtn.addEventListener('click', function () { openMapForField('pickup'); });
  }
  if (deliveryMapBtn) {
    deliveryMapBtn.addEventListener('click', function () { openMapForField('delivery'); });
  }
  if (mapConfirmBtn) {
    mapConfirmBtn.addEventListener('click', confirmMapSelection);
  }
  if (mapCloseBtn) {
    mapCloseBtn.addEventListener('click', function () { mapContainer.style.display = 'none'; });
  }

  // --- Calcolo rotta ---
  function tryCalculateRoute() {
    var pCoords = window._quotePickupCoords;
    var dCoords = window._quoteDeliveryCoords;
    if (!pCoords || !dCoords) return;

    // Sincronizza con lo stato della IIFE modale (tramite variabili nel DOM scope)
    routeSummary.style.display = 'block';
    routeSummary.classList.add('quote-route-summary--loading');

    var url = 'api/route-calc.php?origin_lat=' + pCoords.lat
      + '&origin_lng=' + pCoords.lng
      + '&dest_lat=' + dCoords.lat
      + '&dest_lng=' + dCoords.lng;

    fetch(url)
      .then(function (res) { return res.json(); })
      .then(function (data) {
        routeSummary.classList.remove('quote-route-summary--loading');

        if (data.error) {
          document.getElementById('routeDistance').textContent = 'Errore';
          document.getElementById('routeDuration').textContent = data.error;
          return;
        }

        // Aggiorna UI costi
        document.getElementById('routeDistance').textContent = data.distance_km + ' km';
        document.getElementById('routeDuration').textContent = data.duration_text;
        document.getElementById('routeFuelCost').textContent = '€' + data.fuel_cost.toFixed(2);
        document.getElementById('routeTollCost').textContent = '€' + data.toll_cost.toFixed(2);
        document.getElementById('routeTotalCost').textContent = '€' + data.total_cost.toFixed(2);

        // Mostra nota minimo se applicato
        var minNote = document.getElementById('routeMinNote');
        if (minNote) {
          minNote.style.display = (data.fuel_cost + data.toll_cost) < 50 ? 'block' : 'none';
        }

        // Salva nello stato globale per il riepilogo (Step 5)
        // Accediamo all'IIFE tramite l'event system: settiamo un data attribute
        window._quoteRouteData = data;

        // Disegna rotta sulla mappa preview
        drawRoutePreview(data, pCoords, dCoords);
      })
      .catch(function () {
        routeSummary.classList.remove('quote-route-summary--loading');
        document.getElementById('routeDistance').textContent = 'Errore';
        document.getElementById('routeDuration').textContent = 'Riprova più tardi';
      });
  }

  function drawRoutePreview(data, origin, destination) {
    if (!routePreviewDiv) return;

    // Inizializza mappa preview se serve
    if (!routePreviewMap) {
      routePreviewMap = new google.maps.Map(routePreviewDiv, {
        center: { lat: 41.9028, lng: 12.4964 },
        zoom: 6,
        disableDefaultUI: true,
        gestureHandling: 'cooperative',
        styles: [
          { featureType: 'poi', stylers: [{ visibility: 'off' }] },
          { featureType: 'transit', stylers: [{ visibility: 'off' }] }
        ]
      });
    }

    // Pulisci precedenti
    if (window._quoteRoutePolyline) {
      window._quoteRoutePolyline.setMap(null);
    }
    if (window._quoteRouteMarkers) {
      window._quoteRouteMarkers.forEach(function (m) { m.setMap(null); });
    }
    window._quoteRouteMarkers = [];

    // Decodifica e disegna polyline
    if (data.polyline) {
      var path = google.maps.geometry.encoding.decodePath(data.polyline);
      window._quoteRoutePolyline = new google.maps.Polyline({
        path: path,
        geodesic: true,
        strokeColor: '#0284c7',
        strokeOpacity: 0.8,
        strokeWeight: 4
      });
      window._quoteRoutePolyline.setMap(routePreviewMap);

      // Fit bounds alla rotta
      var bounds = new google.maps.LatLngBounds();
      path.forEach(function (p) { bounds.extend(p); });
      routePreviewMap.fitBounds(bounds, 30);
    }

    // Marker origine
    var markerA = new google.maps.Marker({
      position: origin,
      map: routePreviewMap,
      label: { text: 'A', color: '#fff', fontWeight: '700' },
      title: 'Ritiro'
    });
    window._quoteRouteMarkers.push(markerA);

    // Marker destinazione
    var markerB = new google.maps.Marker({
      position: destination,
      map: routePreviewMap,
      label: { text: 'B', color: '#fff', fontWeight: '700' },
      title: 'Consegna'
    });
    window._quoteRouteMarkers.push(markerB);
  }
}