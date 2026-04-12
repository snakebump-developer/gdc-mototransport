<?php

/**
 * Pagina di manutenzione.
 * Mostrata a tutti quando la modalità manutenzione è attiva.
 * L'admin può sbloccare il sito inserendo la password corretta.
 */
$config = require __DIR__ . '/../../src/config.php';

// Gestione form bypass (POST)
$bypassError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bypass_password'])) {
    $input    = $_POST['bypass_password'] ?? '';
    $expected = hash_hmac('sha256', 'gdcm_bypass_v1', $config['maintenance_password']);

    if (hash_equals($expected, hash_hmac('sha256', 'gdcm_bypass_v1', $input))) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('gdcm_access', $expected, [
            'expires'  => time() + 28800, // 8 ore
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        header('Location: /');
        exit;
    } else {
        $bypassError = 'Password non corretta.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenzione — GDC MotoTransport</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f0f0f;
            color: #f1f1f1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .maint-card {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .maint-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            display: block;
            animation: spin 4s linear infinite;
        }

        @keyframes spin {

            0%,
            100% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(-8deg);
            }

            75% {
                transform: rotate(8deg);
            }
        }

        .maint-logo {
            color: #e85252;
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
        }

        p.subtitle {
            color: #888;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .status-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #111;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 2.5rem;
            font-size: 0.85rem;
            color: #666;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e85252;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        /* Form bypass - nascosto di default */
        .bypass-section {
            margin-top: 2rem;
            border-top: 1px solid #2a2a2a;
            padding-top: 1.5rem;
        }

        .bypass-toggle {
            background: none;
            border: none;
            color: #444;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 0.2s;
        }

        .bypass-toggle:hover {
            color: #666;
        }

        .bypass-form {
            display: none;
            margin-top: 1rem;
        }

        .bypass-form.visible {
            display: block;
        }

        .bypass-form label {
            display: block;
            text-align: left;
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.4rem;
        }

        .bypass-form input[type="password"] {
            width: 100%;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .bypass-form input[type="password"]:focus {
            border-color: #e85252;
        }

        .bypass-form button[type="submit"] {
            width: 100%;
            margin-top: 0.75rem;
            background: #e85252;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.65rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .bypass-form button[type="submit"]:hover {
            background: #d43e3e;
        }

        .error-msg {
            color: #e85252;
            font-size: 0.82rem;
            margin-top: 0.5rem;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="maint-card">
        <span class="maint-icon">🔧</span>
        <div class="maint-logo">GDC MotoTransport</div>
        <h1>Sito in manutenzione</h1>
        <p class="subtitle">
            Stiamo lavorando per migliorare il servizio.<br>
            Torneremo online a breve. Ci scusiamo per il disagio.
        </p>

        <div class="status-bar">
            <span class="status-dot"></span>
            <span>Manutenzione in corso</span>
        </div>

        <!-- Bypass admin - minimal e discreto -->
        <div class="bypass-section">
            <button class="bypass-toggle" onclick="document.getElementById('bypassForm').classList.toggle('visible')">
                Accesso riservato
            </button>
            <form id="bypassForm" class="bypass-form <?= $bypassError ? 'visible' : '' ?>"
                method="POST" action="/manutenzione">
                <label for="bypass_password">Password amministratore</label>
                <input type="password" id="bypass_password" name="bypass_password"
                    placeholder="••••••••" autocomplete="current-password" required>
                <?php if ($bypassError): ?>
                    <p class="error-msg"><?= htmlspecialchars($bypassError) ?></p>
                <?php endif; ?>
                <button type="submit">Accedi</button>
            </form>
        </div>
    </div>
</body>

</html>