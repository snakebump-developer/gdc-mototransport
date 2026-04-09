<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/orders.php';
require_once __DIR__ . '/../../src/users.php';

header('Content-Type: application/json');

// Solo admin possono accedere
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$preventiviStats  = getPreventiviStats();
$userStats        = getUserStats();
$ultimi_preventivi = getLastPreventivi(5);
$ultimi_utenti    = getAllUsers(5);

// Formatta gli ultimi preventivi per il JSON
$preventivi = [];
foreach ($ultimi_preventivi as $p) {
    $preventivi[] = [
        'id'           => (int)$p['id'],
        'cliente'      => $p['cliente'] ?? 'N/A',
        'creato_il'    => $p['creato_il'],
        'prezzo_finale' => (float)($p['prezzo_finale'] ?? 0),
        'stato'        => $p['stato'],
    ];
}

// Formatta gli ultimi utenti per il JSON
$utenti = [];
foreach ($ultimi_utenti as $u) {
    $utenti[] = [
        'username' => $u['username'],
        'email'    => $u['email'],
        'ruolo'    => $u['ruolo'],
    ];
}

echo json_encode([
    'preventivi_stats'  => array_merge($preventiviStats, $userStats),
    'ultimi_preventivi' => $preventivi,
    'ultimi_utenti'     => $utenti,
]);
