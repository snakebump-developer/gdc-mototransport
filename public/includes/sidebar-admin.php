<?php

/**
 * Sidebar per il pannello Admin.
 * Variabili attese: $section
 */
$section = $section ?? 'overview';
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-header__badge badge badge--admin">Admin</div>
        <h3>Pannello Admin</h3>
    </div>
    <nav class="sidebar-nav">
        <a href="admin.php?section=overview"
            class="sidebar-link <?= $section === 'overview' ? 'active' : '' ?>">
            <span class="icon">📊</span> Panoramica
        </a>
        <a href="admin.php?section=users"
            class="sidebar-link <?= $section === 'users' ? 'active' : '' ?>">
            <span class="icon">👥</span> Clienti
        </a>
        <a href="admin.php?section=professionals"
            class="sidebar-link <?= $section === 'professionals' ? 'active' : '' ?>">
            <span class="icon">🏢</span> Professionisti
        </a>
        <a href="admin.php?section=orders"
            class="sidebar-link <?= $section === 'orders' ? 'active' : '' ?>">
            <span class="icon">📦</span> Tutti gli Ordini
        </a>
        <hr>
        <a href="index.php" class="sidebar-link">
            <span class="icon">🏠</span> Home
        </a>
        <a href="logout.php" class="sidebar-link sidebar-link--danger">
            <span class="icon">🚪</span> Esci
        </a>
    </nav>
</aside>