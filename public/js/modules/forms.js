// ===== DETTAGLI ORDINE (Admin) =====
function viewOrderDetails(orderId) {
  alert('Funzionalità dettagli ordine #' + orderId + ' da implementare');
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
