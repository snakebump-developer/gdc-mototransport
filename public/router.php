<?php

/**
 * Router per il server PHP built-in.
 * Mappa URL puliti ai file PHP corrispondenti.
 *
 * Avviare con:
 *   php -S host:port -t public public/router.php
 */

$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri   = rtrim($uri, '/') ?: '/';

// ── File statici (CSS, JS, immagini, font, ecc.) ──────────────────────────────
// Se il file fisico esiste nel docroot, il server lo serve direttamente.
if ($uri !== '/' && is_file(__DIR__ . $uri)) {
    return false;
}

// ── Modalità manutenzione ─────────────────────────────────────────────────────
// Le rotte /admin, /manutenzione e le API di sistema restano sempre accessibili.
$_maintExempt = (
    str_starts_with($uri, '/admin') ||
    str_starts_with($uri, '/api/')  ||
    $uri === '/manutenzione'        ||
    $uri === '/login'               ||
    $uri === '/logout'
);

if (!$_maintExempt) {
    $_maintActive = false;
    try {
        $_mcfg = require __DIR__ . '/../src/config.php';
        $_md   = $_mcfg['db'];
        $_mpdo = new PDO(
            "mysql:host={$_md['host']};port={$_md['port']};dbname={$_md['name']};charset={$_md['charset']}",
            $_md['user'],
            $_md['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $_row = $_mpdo->query("SELECT setting_value FROM app_settings WHERE setting_key='maintenance_mode' LIMIT 1")->fetch(PDO::FETCH_NUM);
        $_maintActive = ($_row && $_row[0] === '1');
    } catch (\Exception $_me) { /* in caso di errore DB non blocchiamo */
    }

    if ($_maintActive) {
        // Controlla cookie bypass admin
        $_mcfg   = $_mcfg ?? require __DIR__ . '/../src/config.php';
        $_expect = hash_hmac('sha256', 'gdcm_bypass_v1', $_mcfg['maintenance_password'] ?? '');
        if (($_COOKIE['gdcm_access'] ?? '') !== $_expect) {
            header('Location: /manutenzione');
            exit;
        }
    }
    unset($_maintExempt, $_maintActive, $_mcfg, $_md, $_mpdo, $_row, $_me, $_expect);
}

// ── Rotte dinamiche (con parametro numerico) ──────────────────────────────────

// /admin/utente/{id}
if (preg_match('#^/admin/utente/(\d+)$#', $uri, $m)) {
    $_GET['id'] = (int) $m[1];
    include __DIR__ . '/admin-utente.php';
    return true;
}

// /admin/preventivo/{id}
if (preg_match('#^/admin/preventivo/(\d+)$#', $uri, $m)) {
    $_GET['id'] = (int) $m[1];
    include __DIR__ . '/admin-preventivo.php';
    return true;
}

// ── Rotte statiche ────────────────────────────────────────────────────────────
//   'uri' => [ 'file' => '...', 'get' => [...] ]
$routes = [
    // Pubblica
    '/'                      => ['file' => 'index.php'],
    '/login'                 => ['file' => 'login.php'],
    '/logout'                => ['file' => 'logout.php'],
    '/registrati'            => ['file' => 'register.php'],
    '/pagamento'             => ['file' => 'payment.php'],
    '/pagamento/successo'    => ['file' => 'payment-success.php'],
    '/pagamento/annullato'   => ['file' => 'payment-cancel.php'],

    // Dashboard utente
    '/dashboard'             => ['file' => 'dashboard.php'],
    '/dashboard/profilo'     => ['file' => 'dashboard.php',     'get' => ['section' => 'profile']],
    '/dashboard/moto'        => ['file' => 'dashboard.php',     'get' => ['section' => 'motorcycles']],
    '/dashboard/ordini'      => ['file' => 'dashboard.php',     'get' => ['section' => 'orders']],

    // Dashboard professionista
    '/dashboard/pro'         => ['file' => 'dashboard-pro.php'],
    '/dashboard/pro/profilo' => ['file' => 'dashboard-pro.php', 'get' => ['section' => 'profile']],
    '/dashboard/pro/moto'    => ['file' => 'dashboard-pro.php', 'get' => ['section' => 'motorcycles']],
    '/dashboard/pro/ordini'  => ['file' => 'dashboard-pro.php', 'get' => ['section' => 'orders']],

    // Admin
    '/admin'                 => ['file' => 'admin.php',         'get' => ['sezione' => 'panoramica']],
    '/admin/panoramica'      => ['file' => 'admin.php',         'get' => ['sezione' => 'panoramica']],
    '/admin/utenti'          => ['file' => 'admin.php',         'get' => ['sezione' => 'utenti']],
    '/admin/professionisti'  => ['file' => 'admin.php',         'get' => ['sezione' => 'professionisti']],
    '/admin/preventivi'      => ['file' => 'admin.php',         'get' => ['sezione' => 'preventivi']],
    '/admin/moto-bozze'      => ['file' => 'admin.php',         'get' => ['sezione' => 'moto-bozze']],

    // API
    '/api/percorso'                 => ['file' => 'api/route-calc.php'],
    '/api/preventivo'               => ['file' => 'api/preventivo.php'],
    '/api/salva-bozza'              => ['file' => 'api/save-draft.php'],
    '/api/create-payment-intent'    => ['file' => 'api/create-payment-intent.php'],
    '/api/webhook-stripe'           => ['file' => 'api/webhook-stripe.php'],
    '/api/moto-catalogo'            => ['file' => 'api/moto-catalogo.php'],
    '/api/run-seed'                 => ['file' => 'api/run-seed.php'],
    '/api/run-migrate'              => ['file' => 'api/run-migrate.php'],
    '/api/toggle-maintenance'       => ['file' => 'api/toggle-maintenance.php'],
    '/api/confirm-payment'          => ['file' => 'api/confirm-payment.php'],

    // Manutenzione
    '/manutenzione'                 => ['file' => 'maintenance.php'],
];

if (isset($routes[$uri])) {
    $route = $routes[$uri];
    if (!empty($route['get'])) {
        foreach ($route['get'] as $k => $v) {
            $_GET[$k] = $v;
        }
    }
    include __DIR__ . '/' . $route['file'];
    return true;
}

// ── 404 ───────────────────────────────────────────────────────────────────────
http_response_code(404);
echo '<!DOCTYPE html><html lang="it"><head><meta charset="UTF-8"><title>404</title>';
echo '<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;';
echo 'height:100vh;margin:0;background:#f9fafb;color:#374151;}';
echo 'h1{font-size:4rem;margin:0;}p{color:#6b7280;}</style></head>';
echo '<body><div style="text-align:center"><h1>404</h1><p>Pagina non trovata.</p>';
echo '<a href="/" style="color:#e85252;">← Torna alla home</a></div></body></html>';
