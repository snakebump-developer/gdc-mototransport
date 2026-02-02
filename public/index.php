<?php
require_once __DIR__ . '/../src/auth.php';
$user = isLogged() ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starter Kit - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">
                    <h2>StarterKit</h2>
                </a>
            </div>
            
            <div class="nav-menu">
                <a href="#home">Home</a>
                <a href="#features">Features</a>
                <a href="#pricing">Pricing</a>
                <a href="#contact">Contatti</a>
            </div>
            
            <div class="nav-auth">
                <?php if ($user): ?>
                    <div class="user-dropdown">
                        <button class="user-button" id="userButton">
                            <?= htmlspecialchars($user['username']) ?>
                            <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                                <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="dashboard.php">Il Mio Profilo</a>
                            <a href="dashboard.php?section=orders">I Miei Ordini</a>
                            <?php if (isAdmin()): ?>
                                <hr>
                                <a href="admin.php">Pannello Admin</a>
                            <?php endif; ?>
                            <hr>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Accedi</a>
                    <a href="register.php" class="btn btn-primary">Registrati</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h1>Benvenuto nello Starter Kit</h1>
                <p class="hero-subtitle">
                    Una soluzione completa per creare il tuo progetto web con autenticazione,
                    gestione utenti e sistema di pagamenti integrato.
                </p>
                <div class="hero-buttons">
                    <?php if (!$user): ?>
                        <a href="register.php" class="btn btn-primary btn-large">Inizia Ora</a>
                        <a href="#features" class="btn btn-secondary btn-large">Scopri di più</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-primary btn-large">Dashboard</a>
                        <a href="#features" class="btn btn-secondary btn-large">Esplora Features</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Caratteristiche Principali</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>Autenticazione Sicura</h3>
                    <p>Sistema di login e registrazione con validazione avanzata e password criptate.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👤</div>
                    <h3>Gestione Profilo</h3>
                    <p>Dashboard personale per modificare i propri dati e gestire le informazioni.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <h3>Gestione Ordini</h3>
                    <p>Sistema completo per tracciare e gestire gli ordini in modo efficiente.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Pagamenti Integrati</h3>
                    <p>Supporto per Stripe e PayPal per transazioni sicure e veloci.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚙️</div>
                    <h3>Pannello Admin</h3>
                    <p>Area amministrativa per gestire utenti, ordini e impostazioni del sito.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Responsive Design</h3>
                    <p>Interfaccia ottimizzata per tutti i dispositivi, mobile-first.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Pronto per iniziare?</h2>
            <p>Crea il tuo account e scopri tutte le funzionalità</p>
            <?php if (!$user): ?>
                <a href="register.php" class="btn btn-primary btn-large">Registrati Gratuitamente</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 StarterKit. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>