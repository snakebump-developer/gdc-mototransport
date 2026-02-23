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
    ['motoType', 'motoBrand', 'motoModel', 'addressPickup', 'addressDelivery',
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
    const motoType = val('motoType');
    const pickup = val('addressPickup');
    const delivery = val('addressDelivery');
    const deliveryType = selectedDelivery();
    const name = val('clientName');
    const email = val('clientEmail');
    const phone = val('clientPhone');
    const fiscal = val('clientFiscal');

    setText('summaryMoto', brand && model ? brand + ' ' + model : brand || model || '—');
    setText('summaryMotoType', motoType || '—');
    setText('summaryRoute', pickup && delivery ? pickup + ' → ' + delivery : '—');
    setText('summaryDelivery', DELIVERY_LABELS[deliveryType] || deliveryType);
    setText('summaryDeliveryDesc', DELIVERY_DESC[deliveryType] || '');
    setText('summaryName', name || '—');
    const contactLines = [email, phone, fiscal].filter(Boolean).join('\n');
    setText('summaryContact', contactLines || '—');

    const total = BASE_PRICE + (DELIVERY_SURCHARGE[deliveryType] || 0);
    setText('summaryPrice', '€' + total);
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
      require('motoType');
      require('motoBrand');
      require('motoModel');
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

