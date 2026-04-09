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

return [
    // Configurazione MySQL (MAMP)
    'db' => [
        'host'     => getenv('DB_HOST')     ?: '127.0.0.1',
        'port'     => getenv('DB_PORT')     ?: '8889',
        'name'     => getenv('DB_NAME')     ?: 'gdctrasporti_db',
        'user'     => getenv('DB_USER')     ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: 'root',
        'charset'  => 'utf8mb4',
    ],

    // Configurazione app
    'app_name' => 'GDC MotoTransport',
    'app_url' => 'http://localhost:8888',

    // Impostazioni sicurezza
    'session_timeout' => 3600, // 1 ora in secondi

    // Configurazione pagamenti (da configurare con le tue chiavi)
    'stripe' => [
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
        'public_key' => getenv('STRIPE_PUBLIC_KEY') ?: '',
        'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET') ?: ''
    ],

    'paypal' => [
        'client_id' => getenv('PAYPAL_CLIENT_ID') ?: '',
        'client_secret' => getenv('PAYPAL_CLIENT_SECRET') ?: '',
        'mode' => 'sandbox' // 'sandbox' per test, 'live' per produzione
    ],

    // Google Maps
    'google_maps_api_key' => getenv('GOOGLE_MAPS_API_KEY') ?: ''
];
