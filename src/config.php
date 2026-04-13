<?php
// Avviamo la sessione per gestire l'utente loggato (se non è già attiva)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carica variabili da .env (se esiste e non già caricate)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

// Helper per leggere env vars in qualsiasi contesto PHP (getenv, $_ENV, $_SERVER)
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $val = getenv($key);
        if ($val !== false) return $val;
        if (isset($_ENV[$key])) return $_ENV[$key];
        if (isset($_SERVER[$key])) return $_SERVER[$key];
        return $default;
    }
}

return [
    // Configurazione MySQL
    'db' => [
        'host'     => env('DB_HOST',     '127.0.0.1'),
        'port'     => env('DB_PORT',     '8889'),
        'name'     => env('DB_NAME',     'gdctrasporti_db'),
        'user'     => env('DB_USER',     'root'),
        'password' => env('DB_PASSWORD', 'root'),
        'charset'  => 'utf8mb4',
    ],

    // Configurazione app
    'app_name' => env('APP_NAME', 'GDC MotoTransport'),
    'app_url'  => env('APP_URL',  'http://localhost:8888'),

    // Dati aziendali — footer, WhatsApp, link "Chiama Ora" (modificare via .env)
    'company' => [
        'name'      => env('COMPANY_NAME',      'MotoTransport Italia'),
        'address'   => env('COMPANY_ADDRESS',   'Via Example 123, 20100 Milano (MI)'),
        'phone'     => env('COMPANY_PHONE',     '+39 012 345 6789'),
        'phone_tel' => env('COMPANY_PHONE_TEL', '+390123456789'),
        'email'     => env('COMPANY_EMAIL',     'info@mototransport.it'),
        'whatsapp'  => env('COMPANY_WHATSAPP',  '393282669228'),
    ],

    // Impostazioni sicurezza
    'session_timeout' => 3600, // 1 ora in secondi

    // Configurazione pagamenti (da configurare con le tue chiavi)
    'stripe' => [
        'secret_key'     => env('STRIPE_SECRET_KEY', ''),
        'public_key'     => env('STRIPE_PUBLIC_KEY', ''),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
    ],

    'paypal' => [
        'client_id'     => env('PAYPAL_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        'mode'          => 'sandbox', // 'sandbox' per test, 'live' per produzione
    ],

    // Google Maps
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),

    // Password bypass modalità manutenzione
    'maintenance_password' => env('MAINTENANCE_PASSWORD', 'GDC@Maint2026!'),

    // ── Lettera di Vettura — Dati Vettore ────────────────────────────────────
    'vettore' => [
        'ragione_sociale' => env('VETTORE_RAGIONE_SOCIALE', 'GDC MotoTransport S.r.l.'),
        'piva'            => env('VETTORE_PIVA',            'IT12345678901'),
        'indirizzo'       => env('VETTORE_INDIRIZZO',       'Via del Trasporto 1'),
        'cap'             => env('VETTORE_CAP',             '20100'),
        'citta'           => env('VETTORE_CITTA',           'Milano'),
        'provincia'       => env('VETTORE_PROVINCIA',       'MI'),
        'telefono'        => env('VETTORE_TELEFONO',        '+39 02 1234 5678'),
        'email'           => env('VETTORE_EMAIL',           'info@gdcmototransport.it'),
        'ren'             => env('VETTORE_REN',             'REN-123456-IT'),
    ],

    // ── Lettera di Vettura — Conducente ──────────────────────────────────────
    'conducente' => [
        'nome'         => env('CONDUCENTE_NOME',        'Mario'),
        'cognome'      => env('CONDUCENTE_COGNOME',     'Rossi'),
        'patente'      => env('CONDUCENTE_PATENTE',     'IT-A1B2C3D4E5'),
        'patente_cat'  => env('CONDUCENTE_PATENTE_CAT', 'CE'),
        'telefono'     => env('CONDUCENTE_TELEFONO',    '+39 333 000 1111'),
    ],

    // ── Lettera di Vettura — Mezzo di Trasporto ───────────────────────────────
    'mezzo' => [
        'tipo'             => env('MEZZO_TIPO',             'Furgone chiuso'),
        'targa'            => env('MEZZO_TARGA',            'AB123CD'),
        'marca'            => env('MEZZO_MARCA',            'Mercedes-Benz'),
        'modello'          => env('MEZZO_MODELLO',          'Sprinter 316 CDI'),
        'rimorchio_targa'  => env('MEZZO_RIMORCHIO_TARGA',  ''),
    ],

    // ── Lettera di Vettura — Assicurazione RC Merci ───────────────────────────
    'assicurazione' => [
        'compagnia'   => env('ASSICURAZIONE_COMPAGNIA',  'Sara Assicurazioni S.p.A.'),
        'polizza'     => env('ASSICURAZIONE_POLIZZA',    'RC-MERCI-00123456'),
        'scadenza'    => env('ASSICURAZIONE_SCADENZA',   '31/12/2026'),
        'massimale'   => env('ASSICURAZIONE_MASSIMALE',  '€ 1.000.000,00'),
    ],
];
