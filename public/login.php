<?php
require_once __DIR__ . '/../src/auth.php';

if (isLogged()) {
    header('Location: /');
    exit;
}

$error = '';
$pageTitle = 'Login - MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/auth.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (loginUser($_POST['username'], $_POST['password'])) {
        header('Location: ' . getDashboardUrl());
        exit;
    } else {
        $error = "Credenziali errate. Verifica username/email e password.";
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
                <h2>Accedi</h2>
                <p>Benvenuto! Inserisci le tue credenziali</p>
            </div>

            <?php include 'includes/alerts.php'; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Username o Email</label>
                    <input type="text" id="username" name="username" required
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Accedi</button>
            </form>

            <div class="auth-footer">
                <p>Non hai un account? <a href="/registrati">Registrati</a></p>
                <p><a href="/">Torna alla home</a></p>
            </div>
        </div>
    </div>
</body>

</html>