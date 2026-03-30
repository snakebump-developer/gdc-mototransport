<?php

/**
 * Navbar principale del sito (landing page).
 *
 * Variabili attese:
 *   $user — Utente corrente (null se non loggato)
 */
$user = $user ?? null;
?>
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
            <a href="#come-funziona" data-section="come-funziona">Come funziona</a>
            <a href="#vantaggi" data-section="vantaggi">Vantaggi</a>
            <a href="#gallery" data-section="gallery">Gallery</a>
            <a href="#recensioni" data-section="recensioni">Recensioni</a>
            <a href="#chi-siamo" data-section="chi-siamo">Chi siamo</a>
        </div>

        <!-- Preventivo Button Desktop -->
        <div class="nav-cta-desktop">
            <a href="#" class="nav-cta-btn open-quote-modal">Preventivo Gratuito</a>
        </div>

        <!-- Mobile Controls -->
        <div class="nav-mobile-controls">
            <a href="#" class="nav-cta-btn btn--mobile-cta open-quote-modal">Preventivo Gratuito</a>
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
            <a href="#come-funziona">Come funziona</a>
            <a href="#vantaggi">Vantaggi</a>
            <a href="#gallery">Gallery</a>
            <a href="#recensioni">Recensioni</a>
            <a href="#chi-siamo">Chi siamo</a>
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