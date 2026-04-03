<?php

/**
 * Sidebar per la Dashboard utente.
 * Variabili attese: $section
 */
$section = $section ?? 'profile';
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-avatar-wrapper">
            <?php if (!empty($user['avatar'])): ?>
                <img src="/<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="Avatar" class="sidebar-avatar">
            <?php else: ?>
                <div class="sidebar-avatar sidebar-avatar--initials">
                    <?= htmlspecialchars(strtoupper(substr($user['nome'] ?? $user['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
        </div>
        <h3><?= htmlspecialchars($user['username']) ?></h3>
    </div>
    <nav class="sidebar-nav">
        <a href="/dashboard/profilo"
            class="sidebar-link <?= $section === 'profile' ? 'active' : '' ?>">
            <span class="icon">👤</span> Il Mio Profilo
        </a>
        <a href="/dashboard/moto"
            class="sidebar-link <?= $section === 'motorcycles' ? 'active' : '' ?>">
            <span class="icon">🏍️</span> Le Mie Moto
        </a>
        <a href="/dashboard/ordini"
            class="sidebar-link <?= $section === 'orders' ? 'active' : '' ?>">
            <span class="icon">📦</span> I Miei Ordini
        </a>
        <hr>
        <a href="/" class="sidebar-link">
            <span class="icon">🏠</span> Torna alla Home
        </a>
        <a href="/logout" class="sidebar-link sidebar-link--danger">
            <span class="icon">🚪</span> Esci
        </a>
    </nav>
</aside>