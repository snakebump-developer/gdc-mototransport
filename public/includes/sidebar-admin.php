<?php

/**
 * Sidebar per il pannello Admin.
 *
 * Variabili attese:
 *   $section — Sezione attiva ('overview' | 'orders' | 'users')
 */
$section = $section ?? 'overview';
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <h3>Pannello Admin</h3>
    </div>
    <nav class="sidebar-nav">
        <a href="admin.php?section=overview" class="sidebar-link <?= $section === 'overview' ? 'active' : '' ?>">
            <span class="icon">📊</span>
            Panoramica
        </a>
        <a href="admin.php?section=orders" class="sidebar-link <?= $section === 'orders' ? 'active' : '' ?>">
            <span class="icon">📦</span>
            Tutti gli Ordini
        </a>
        <a href="admin.php?section=users" class="sidebar-link <?= $section === 'users' ? 'active' : '' ?>">
            <span class="icon">👥</span>
            Gestione Utenti
        </a>
        <hr>
        <a href="index.php" class="sidebar-link">
            <span class="icon">🏠</span>
            Torna alla Home
        </a>
    </nav>
</aside>