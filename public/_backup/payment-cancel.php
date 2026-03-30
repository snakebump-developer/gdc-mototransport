<?php
require_once __DIR__ . '/../src/auth.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Annullato - Starter Kit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box" style="text-align: center;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">❌</div>
            <h2>Pagamento Annullato</h2>
            <p style="margin: 1rem 0;">Hai annullato il processo di pagamento.</p>
            
            <div class="alert alert-error">
                <p>Nessun addebito è stato effettuato sul tuo conto.</p>
            </div>
            
            <div style="margin-top: 2rem;">
                <a href="payment.php" class="btn btn-primary">Riprova il Pagamento</a>
                <a href="index.php" class="btn btn-secondary">Torna alla Home</a>
            </div>
        </div>
    </div>
</body>
</html>
