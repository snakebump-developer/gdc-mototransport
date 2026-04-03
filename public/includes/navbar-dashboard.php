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
            <a href="/">
                <h2>MotoTransport</h2>
            </a>
        </div>
        <div class="nav-auth">
            <div class="user-dropdown">
                <button class="user-button" id="userButton">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="/<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>"
                             alt="Avatar" class="nav-avatar">
                    <?php else: ?>
                        <span class="nav-avatar nav-avatar--initials">
                            <?= htmlspecialchars(strtoupper(substr($user['nome'] ?? $user['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                    <?= htmlspecialchars($user['username']) ?><?= $isAdmin ? ' (Admin)' : '' ?>
                    <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                        <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2" />
                    </svg>
                </button>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="/dashboard">Il Mio Profilo</a>
                    <a href="/dashboard/ordini">I Miei Ordini</a>
                    <?php if (isAdmin()): ?>
                        <hr>
                        <a href="/admin">Pannello Admin</a>
                    <?php endif; ?>
                    <hr>
                    <a href="/logout">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>