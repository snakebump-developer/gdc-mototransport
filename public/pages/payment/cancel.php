<?php
require_once __DIR__ . '/../../../src/auth.php';
$pageTitle = 'Pagamento Annullato - MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/auth.css'];
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include __DIR__ . '/../../includes/head.php'; ?>
</head>

<body>
    <div class="auth-container">
        <div class="auth-box" style="text-align: center;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">&#10060;</div>
            <h2>Pagamento Annullato</h2>
            <p style="margin: 1rem 0;">Hai annullato il processo di pagamento.</p>
            <div class="alert alert-error">
                <p>Nessun addebito è stato effettuato sul tuo conto.</p>
            </div>
            <div style="margin-top: 2rem;">
                <a href="/pagamento" class="btn btn-primary">Riprova il Pagamento</a>
                <a href="/" class="btn btn-secondary">Torna alla Home</a>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../../includes/whatsapp-button.php'; ?>
</body>

</html>