<?php
require_once __DIR__ . '/../src/auth.php';

if (isLogged()) {
    header('Location: /');
    exit;
}

$error = '';
$success = '';
$pageTitle = 'Registrazione - MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/auth.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        registerUser($_POST['username'], $_POST['email'], $_POST['password']);
        $success = "Registrazione completata! Ora puoi accedere.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include 'includes/head.php'; ?>
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h2>Registrati</h2>
                <p>Crea il tuo account gratuito</p>
            </div>

            <?php include 'includes/alerts.php'; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <a href="/login">Vai al login</a>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required
                        pattern="[a-zA-Z0-9_]{3,20}"
                        title="3-20 caratteri alfanumerici"
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    <small>3-20 caratteri alfanumerici</small>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                        minlength="8"
                        pattern="(?=.*[A-Za-z])(?=.*\d).{8,}"
                        title="Minimo 8 caratteri, almeno 1 lettera e 1 numero">
                    <small>Minimo 8 caratteri, almeno 1 lettera e 1 numero</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Registrati</button>
            </form>

            <div class="auth-footer">
                <p>Hai già un account? <a href="/login">Accedi</a></p>
                <p><a href="/">Torna alla home</a></p>
            </div>
        </div>
    </div>
    <?php include 'includes/whatsapp-button.php'; ?>
</body>

</html>