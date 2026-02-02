<?php
require_once __DIR__ . '/../src/auth.php';

if (isLogged()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (loginUser($_POST['username'], $_POST['password'])) {
        header('Location: index.php');
        exit;
    } else {
        $error = "Credenziali errate. Verifica username/email e password.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Starter Kit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h2>Accedi</h2>
                <p>Benvenuto! Inserisci le tue credenziali</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
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
                <p>Non hai un account? <a href="register.php">Registrati</a></p>
                <p><a href="index.php">Torna alla home</a></p>
            </div>
        </div>
    </div>
</body>
</html>