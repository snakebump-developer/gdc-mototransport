<?php

/**
 * Navbar per pagine Dashboard e Admin.
 *
 * Variabili attese:
 *   $user      — Utente corrente (obbligatorio, autenticato)
 *   $isAdmin   — Se true, mostra etichetta "(Admin)" (opzionale)
 */
$isAdmin = $isAdmin ?? false;
?>
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <a href="index.php">
                <h2>MotoTransport</h2>
            </a>
        </div>
        <div class="nav-auth">
            <div class="user-dropdown">
                <button class="user-button" id="userButton">
                    <?= htmlspecialchars($user['username']) ?><?= $isAdmin ? ' (Admin)' : '' ?>
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                        <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" />
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
        </div>
    </div>
</nav>