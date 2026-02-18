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
            <!-- Logo -->
            <div class="nav-logo">
                <a href="index.php">
                    <h2>MotoTransport</h2>
                </a>
            </div>

            <!-- Menu Desktop -->
            <div class="nav-menu">
                <a href="#home">Come funziona</a>
                <a href="#features">Vantaggi</a>
                <a href="#pricing">Gallery</a>
                <a href="#reviews">Recensioni</a>
                <a href="#contact">Chi siamo</a>
            </div>

            <!-- Preventivo Button Desktop -->
            <div class="nav-cta-desktop">
                <a href="#preventivo" class="btn btn-primary">Preventivo Gratuito</a>
            </div>

            <!-- Mobile Controls -->
            <div class="nav-mobile-controls">
                <!-- Preventivo Button Mobile -->
                <a href="#preventivo" class="btn btn-primary btn-mobile-cta">Preventivo Gratuito</a>

                <!-- Hamburger Menu -->
                <button class="hamburger-menu" id="hamburgerMenu" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

        <!-- Mobile Menu Sidebar -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-header">
                <h3>Menu</h3>
                <button class="mobile-menu-close" id="mobileMenuClose">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="mobile-menu-content">
                <a href="#home">Come funziona</a>
                <a href="#features">Vantaggi</a>
                <a href="#pricing">Gallery</a>
                <a href="#reviews">Recensioni</a>
                <a href="#contact">Chi siamo</a>
                <?php if ($user): ?>
                    <hr>
                    <a href="dashboard.php">Il Mio Profilo</a>
                    <a href="dashboard.php?section=orders">I Miei Ordini</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php">Pannello Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <hr>
                    <a href="login.php">Accedi</a>
                    <a href="register.php">Registrati</a>
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