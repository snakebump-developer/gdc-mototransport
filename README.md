# 🚀 Starter Kit PHP - Sistema di Autenticazione e Gestione Ordini

Un kit completo e professionale per iniziare rapidamente progetti web con PHP vanilla, SQLite, HTML, CSS e JavaScript.

## ✨ Caratteristiche

- ✅ **Sistema di Autenticazione Completo**
  - Registrazione e login con validazione
  - Password criptate con bcrypt
  - Gestione sessioni sicura
  - Validazione lato client e server

- 👤 **Dashboard Utente**
  - Gestione profilo personale
  - Visualizzazione ordini
  - Interfaccia intuitiva con sidebar

- 🔐 **Pannello Admin**
  - Gestione completa utenti
  - Monitoraggio ordini
  - Statistiche in tempo reale
  - Controllo accessi basato su ruoli

- 💳 **Integrazione Pagamenti**
  - Struttura pronta per Stripe
  - Struttura pronta per PayPal
  - Sistema ordini completo

- 🎨 **Design Moderno**
  - Interfaccia responsive
  - Mobile-first design
  - Dropdown interattivi
  - Animazioni fluide

## 📦 Installazione

1. **Clona o scarica il progetto**
   ```bash
   cd test-php
   ```

2. **Inizializza il database**
   ```bash
   php src/setup.php
   ```
   Questo creerà il database SQLite con tutte le tabelle necessarie e un account admin predefinito.

3. **Avvia il server PHP**
   ```bash
   php -S localhost:8000 -t public
   ```

4. **Apri il browser**
   ```
   http://localhost:8000
   ```

## 🔑 Credenziali Predefinite

**Admin:**
- Username: `admin`
- Password: `admin123`

⚠️ **IMPORTANTE**: Cambia queste credenziali dopo il primo accesso!

## 📁 Struttura del Progetto

```
test-php/
├── database/               # Database SQLite
│   └── app_professionale.db
├── public/                 # File pubblici accessibili dal web
│   ├── css/
│   │   └── style.css      # Stili CSS moderni e responsive
│   ├── js/
│   │   └── main.js        # JavaScript per interazioni
│   ├── index.php          # Landing page
│   ├── login.php          # Pagina di login
│   ├── register.php       # Pagina di registrazione
│   ├── logout.php         # Logout
│   ├── dashboard.php      # Dashboard utente
│   ├── admin.php          # Pannello amministratore
│   └── payment.php        # Esempio pagina pagamento
├── src/                   # Logica backend
│   ├── auth.php           # Autenticazione e autorizzazione
│   ├── config.php         # Configurazione
│   ├── db.php             # Connessione database
│   ├── orders.php         # Gestione ordini
│   ├── users.php          # Gestione utenti (admin)
│   ├── setup.php          # Setup database
│   └── payments/          # Integrazioni pagamenti
│       ├── stripe.php     # Integrazione Stripe
│       └── paypal.php     # Integrazione PayPal
└── README.md
```

## 🗄️ Struttura Database

### Tabella `utenti`
- id, username, email, password
- nome, cognome, telefono, indirizzo, città, cap, paese
- ruolo (user/admin)
- creato_il, aggiornato_il

### Tabella `ordini`
- id, user_id, totale
- stato (pending/processing/completed/cancelled)
- metodo_pagamento, transaction_id
- note, creato_il, aggiornato_il

### Tabella `ordini_dettagli`
- id, ordine_id
- descrizione, quantità, prezzo_unitario

## 💳 Configurazione Pagamenti

### Stripe

1. Installa la libreria Stripe:
   ```bash
   composer require stripe/stripe-php
   ```

2. Ottieni le API keys da [Stripe Dashboard](https://dashboard.stripe.com/apikeys)

3. Configura le variabili d'ambiente:
   ```bash
   STRIPE_SECRET_KEY=sk_test_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

4. Decommented il codice in `src/payments/stripe.php`

### PayPal

1. Installa la libreria PayPal:
   ```bash
   composer require paypal/rest-api-sdk-php
   ```

2. Ottieni le credenziali da [PayPal Developer](https://developer.paypal.com/)

3. Configura le variabili d'ambiente:
   ```bash
   PAYPAL_CLIENT_ID=...
   PAYPAL_CLIENT_SECRET=...
   ```

4. Decommenta il codice in `src/payments/paypal.php`

## 🎯 Funzionalità Principali

### Autenticazione
- Registrazione con validazione (username 3-20 caratteri, password min 8 caratteri)
- Login con username o email
- Gestione sessioni sicura
- Protezione CSRF

### Dashboard Utente
- Modifica dati personali
- Visualizzazione storico ordini
- Interfaccia responsive

### Pannello Admin
- Panoramica con statistiche
- Gestione tutti gli ordini
- Aggiornamento stato ordini
- Gestione utenti (visualizza, elimina)

### Sistema Ordini
- Creazione ordini con dettagli
- Stati ordini (pending, processing, completed, cancelled)
- Tracciamento transazioni
- Storico completo

## 🔒 Sicurezza

- ✅ Password hashate con `password_hash()` (bcrypt)
- ✅ Prepared statements per prevenire SQL injection
- ✅ Validazione input lato client e server
- ✅ Protezione accessi con controllo sessioni
- ✅ Separazione ruoli utente/admin
- ✅ HTMLspecialchars per prevenire XSS

## 🎨 Personalizzazione

### Colori
Modifica le variabili CSS in `public/css/style.css`:
```css
:root {
    --primary-color: #4f46e5;
    --secondary-color: #6b7280;
    /* ... */
}
```

### Logo
Sostituisci il testo "StarterKit" nel file `public/index.php` con il tuo logo:
```html
<div class="nav-logo">
    <a href="index.php">
        <img src="img/logo.png" alt="Logo">
    </a>
</div>
```

### Contenuti
Modifica le sezioni nella landing page (`public/index.php`) per adattarle al tuo progetto.

## 📱 Responsive Design

Il design è ottimizzato per:
- 📱 Mobile (< 768px)
- 💻 Tablet (768px - 1024px)
- 🖥️ Desktop (> 1024px)

## 🚀 Deployment

### Prerequisiti per Produzione
- PHP 7.4+
- Modulo PDO SQLite abilitato
- HTTPS obbligatorio per pagamenti
- Composer per le dipendenze

### Checklist Deployment
1. ✅ Cambia credenziali admin predefinite
2. ✅ Configura variabili d'ambiente per API keys
3. ✅ Abilita HTTPS
4. ✅ Configura permessi file/directory corretti
5. ✅ Testa tutte le funzionalità
6. ✅ Configura backup database
7. ✅ Attiva modalità produzione per Stripe/PayPal

## 🤝 Contributi

Questo è uno starter kit generico. Sentiti libero di:
- Personalizzarlo per il tuo progetto
- Aggiungere nuove funzionalità
- Migliorare la sicurezza
- Estendere le integrazioni

## 📝 Note

- Il database SQLite è ottimo per sviluppo e piccoli progetti. Per progetti più grandi, considera MySQL/PostgreSQL
- Le API keys non devono MAI essere committate nel repository
- Testa sempre i pagamenti in modalità sandbox prima di andare in produzione
- Implementa un sistema di log per tracciare errori e attività

## 🆘 Troubleshooting

**Problema: Database non si crea**
```bash
# Verifica permessi directory
chmod 755 database/
php src/setup.php
```

**Problema: Sessioni non funzionano**
```bash
# Verifica che session_start() sia chiamato
# Controlla i permessi della directory sessioni PHP
```

**Problema: CSS/JS non caricati**
```
# Verifica di avviare il server dalla root del progetto
# Controlla i percorsi nei file HTML
```

## 📄 Licenza

Starter kit libero per uso personale e commerciale.

## 📧 Supporto

Per domande o problemi, apri una issue o contatta lo sviluppatore.

---

**Buon sviluppo! 🚀**
