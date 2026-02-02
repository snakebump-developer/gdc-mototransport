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
