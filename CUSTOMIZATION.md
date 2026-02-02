# 📘 Guida alla Personalizzazione e Best Practices

## 🎨 Personalizzazione del Design

### Cambiare i Colori del Tema

Modifica le variabili CSS in [public/css/style.css](public/css/style.css#L11-L21):

```css
:root {
    --primary-color: #4f46e5;      /* Colore principale */
    --primary-hover: #4338ca;       /* Hover del colore principale */
    --secondary-color: #6b7280;     /* Colore secondario */
    --success-color: #10b981;       /* Verde per successo */
    --danger-color: #ef4444;        /* Rosso per errori */
    --warning-color: #f59e0b;       /* Giallo per warning */
    /* ... */
}
```

### Aggiungere un Logo

Sostituisci il testo in tutti i file che contengono la navbar:

```html
<!-- Prima -->
<div class="nav-logo">
    <a href="index.php">
        <h2>StarterKit</h2>
    </a>
</div>

<!-- Dopo -->
<div class="nav-logo">
    <a href="index.php">
        <img src="img/logo.png" alt="Il Mio Brand" height="40">
    </a>
</div>
```

### Personalizzare la Landing Page

Modifica [public/index.php](public/index.php):

1. **Hero Section** - Cambia titolo e descrizione
2. **Features** - Modifica le 6 card con le tue caratteristiche
3. **CTA** - Personalizza la call-to-action

## 🔒 Best Practices per Sicurezza

### 1. Variabili d'Ambiente

Non committare mai le API keys! Usa file `.env`:

```bash
# Copia il template
cp .env.example .env

# Modifica con le tue chiavi
nano .env
```

### 2. HTTPS in Produzione

```php
// src/config.php - Force HTTPS
if ($_SERVER['HTTPS'] !== 'on' && getenv('APP_ENV') === 'production') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

### 3. Rate Limiting Login

Aggiungi protezione contro brute force:

```php
// src/auth.php
function checkLoginAttempts($username) {
    // Implementa contatore tentativi falliti
    // Blocca dopo N tentativi
    // Usa Redis o database per tracciare
}
```

### 4. CSRF Protection

Aggiungi token CSRF ai form:

```php
// Genera token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Verifica nel form
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token invalid');
}
```

### 5. Password Policy

Rafforza la policy password:

```php
// src/auth.php
function validatePassword($password) {
    return strlen($password) >= 12 &&  // Min 12 caratteri
           preg_match('/[A-Z]/', $password) &&  // Maiuscola
           preg_match('/[a-z]/', $password) &&  // Minuscola
           preg_match('/[0-9]/', $password) &&  // Numero
           preg_match('/[^A-Za-z0-9]/', $password);  // Carattere speciale
}
```

## 📧 Aggiungere Sistema Email

### Usando PHPMailer

```bash
composer require phpmailer/phpmailer
```

```php
// src/email.php
use PHPMailer\PHPMailer\PHPMailer;

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER');
    $mail->Password = getenv('SMTP_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    $mail->setFrom('noreply@tuosito.com', 'Il Tuo Sito');
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $body;
    
    $mail->send();
}
```

### Email Conferma Registrazione

```php
// public/register.php - dopo registrazione
$token = bin2hex(random_bytes(32));
// Salva token nel database con scadenza

$link = "https://tuosito.com/verify.php?token=$token";
sendEmail(
    $_POST['email'],
    'Conferma il tuo account',
    "Clicca qui per confermare: $link"
);
```

## 🗄️ Migrare a MySQL

### 1. Crea database MySQL

```sql
CREATE DATABASE starter_kit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Modifica src/db.php

```php
// Per MySQL
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'starter_kit';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

$pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $username,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);
```

### 3. Aggiorna setup.php

Cambia `INTEGER PRIMARY KEY AUTOINCREMENT` in `INT AUTO_INCREMENT PRIMARY KEY`

## 📱 Aggiungere Push Notifications

Usa OneSignal o Firebase Cloud Messaging:

```html
<!-- In head -->
<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
<script>
  window.OneSignal = window.OneSignal || [];
  OneSignal.push(function() {
    OneSignal.init({
      appId: "YOUR-APP-ID",
    });
  });
</script>
```

## 🔍 SEO Optimization

### Meta Tags

Aggiungi in ogni pagina:

```html
<head>
    <!-- SEO Base -->
    <meta name="description" content="Descrizione della pagina">
    <meta name="keywords" content="parola1, parola2, parola3">
    <meta name="author" content="Il Tuo Nome">
    
    <!-- Open Graph (Facebook) -->
    <meta property="og:title" content="Titolo della Pagina">
    <meta property="og:description" content="Descrizione">
    <meta property="og:image" content="https://tuosito.com/img/og-image.jpg">
    <meta property="og:url" content="https://tuosito.com/pagina">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Titolo della Pagina">
    <meta name="twitter:description" content="Descrizione">
    <meta name="twitter:image" content="https://tuosito.com/img/twitter-card.jpg">
</head>
```

### Sitemap

Crea `public/sitemap.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://tuosito.com/</loc>
        <lastmod>2026-01-29</lastmod>
        <priority>1.0</priority>
    </url>
    <!-- Aggiungi altre pagine -->
</urlset>
```

## 📊 Analytics

### Google Analytics

```html
<!-- In head di tutte le pagine -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>
```

## 🚀 Performance Optimization

### 1. Minify CSS/JS

Usa tool come:
- [CSS Minifier](https://cssminifier.com/)
- [JavaScript Minifier](https://javascript-minifier.com/)

### 2. Lazy Loading Immagini

```html
<img src="placeholder.jpg" data-src="immagine-reale.jpg" loading="lazy" alt="...">
```

### 3. Cache Headers

```php
// public/.htaccess (se usi Apache)
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

## 🐳 Docker Setup

Crea `docker-compose.yml`:

```yaml
version: '3.8'
services:
  php:
    image: php:8.1-apache
    ports:
      - "8000:80"
    volumes:
      - ./public:/var/www/html
      - ./src:/var/www/src
      - ./database:/var/www/database
    environment:
      - STRIPE_SECRET_KEY=${STRIPE_SECRET_KEY}
      - PAYPAL_CLIENT_ID=${PAYPAL_CLIENT_ID}
```

Avvia con:
```bash
docker-compose up -d
```

## 📝 Logging Avanzato

Crea `src/logger.php`:

```php
function logAction($level, $message, $context = []) {
    $logFile = __DIR__ . '/../logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $userId = $_SESSION['user_id'] ?? 'guest';
    
    $logEntry = sprintf(
        "[%s] %s [User: %s] %s %s\n",
        $timestamp,
        strtoupper($level),
        $userId,
        $message,
        !empty($context) ? json_encode($context) : ''
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Uso
logAction('info', 'User logged in', ['ip' => $_SERVER['REMOTE_ADDR']]);
logAction('error', 'Payment failed', ['order_id' => 123]);
```

## 🧪 Testing

### Setup PHPUnit

```bash
composer require --dev phpunit/phpunit
```

Crea `tests/AuthTest.php`:

```php
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase {
    public function testValidateEmail() {
        require_once __DIR__ . '/../src/auth.php';
        
        $this->assertTrue(validateEmail('test@example.com'));
        $this->assertFalse(validateEmail('invalid-email'));
    }
    
    public function testValidatePassword() {
        $this->assertTrue(validatePassword('Password123'));
        $this->assertFalse(validatePassword('weak'));
    }
}
```

Esegui:
```bash
./vendor/bin/phpunit tests
```

## 🔐 2FA (Two-Factor Authentication)

Usa Google Authenticator:

```bash
composer require pragmarx/google2fa
```

```php
use PragmaRX\Google2FA\Google2FA;

$google2fa = new Google2FA();

// Setup
$secret = $google2fa->generateSecretKey();
$qrCodeUrl = $google2fa->getQRCodeUrl(
    'TuoSito',
    $user['email'],
    $secret
);

// Verifica
$valid = $google2fa->verifyKey($secret, $userInputCode);
```

## 📦 Backup Automatico

Crea script di backup:

```bash
#!/bin/bash
# backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="backups"

# Backup database
cp database/app_professionale.db "$BACKUP_DIR/db_$DATE.db"

# Backup file
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" public/ src/

# Mantieni solo ultimi 7 giorni
find $BACKUP_DIR -mtime +7 -delete

echo "Backup completato: $DATE"
```

Aggiungi a crontab:
```bash
0 2 * * * /path/to/backup.sh
```

## 🌍 Multi-lingua (i18n)

Crea `src/i18n.php`:

```php
function __($key) {
    $lang = $_SESSION['lang'] ?? 'it';
    $translations = include __DIR__ . "/lang/$lang.php";
    return $translations[$key] ?? $key;
}

// lang/it.php
return [
    'welcome' => 'Benvenuto',
    'login' => 'Accedi',
    'register' => 'Registrati',
];

// lang/en.php
return [
    'welcome' => 'Welcome',
    'login' => 'Login',
    'register' => 'Sign Up',
];
```

Usa nei file:
```php
<h1><?= __('welcome') ?></h1>
```

---

## 💡 Consigli Finali

1. **Versioning**: Usa Git per tracciare le modifiche
2. **Documentation**: Documenta le funzioni complesse
3. **Code Review**: Fai revisionare il codice da altri
4. **Security Audit**: Testa regolarmente le vulnerabilità
5. **Monitoring**: Usa strumenti come New Relic o Datadog
6. **Staging**: Testa sempre in ambiente staging prima di produzione

## 📚 Risorse Utili

- [PHP The Right Way](https://phptherightway.com/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Stripe Documentation](https://stripe.com/docs)
- [PayPal Documentation](https://developer.paypal.com/docs/)
- [SQLite Best Practices](https://www.sqlite.org/bestpractice.html)

---

**Buon coding! 🚀**
