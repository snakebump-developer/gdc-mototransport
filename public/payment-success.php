<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';

$sessionId = $_GET['session_id'] ?? null;
$paymentId = $_GET['payment_id'] ?? null;
$payerId = $_GET['PayerID'] ?? null;

$success = false;
$pageTitle = 'Pagamento Completato - MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/auth.css'];

if ($sessionId) {
    $success = true;
} elseif ($paymentId && $payerId) {
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include 'includes/head.php'; ?>
</head>

<body>
    <div class="auth-container">
        <div class="auth-box" style="text-align: center;">
            <?php if ($success): ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">&#10004;&#65039;</div>
                <h2>Pagamento Completato!</h2>
                <p style="margin: 1rem 0;">Il tuo pagamento è stato elaborato con successo.</p>
                <div class="alert alert-success">
                    <p>Riceverai una conferma via email a breve.</p>
                </div>
                <div style="margin-top: 2rem;">
                    <a href="/dashboard/ordini" class="btn btn-primary">Visualizza Ordini</a>
                    <a href="/" class="btn btn-secondary">Torna alla Home</a>
                </div>
            <?php else: ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">&#9888;&#65039;</div>
                <h2>Errore nel Pagamento</h2>
                <p style="margin: 1rem 0;">Si è verificato un problema durante l'elaborazione del pagamento.</p>
                <div class="alert alert-error">
                    <p>Riprova o contatta il supporto se il problema persiste.</p>
                </div>
                <div style="margin-top: 2rem;">
                    <a href="/pagamento" class="btn btn-primary">Riprova</a>
                    <a href="/" class="btn btn-secondary">Torna alla Home</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>