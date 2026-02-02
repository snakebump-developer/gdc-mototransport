<?php
session_start(); // Avviamo la sessione per gestire l'utente loggato

return [
    'db_name' => 'app_professionale.db',
    'db_dir'  => __DIR__ . '/../database',
    
    // Configurazione app
    'app_name' => 'StarterKit',
    'app_url' => 'http://localhost:8000',
    
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
    ]
];