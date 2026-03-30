<?php

/**
 * Sidebar per la Dashboard utente.
 *
 * Variabili attese:
 *   $section — Sezione attiva ('profile' | 'orders')
 */
$section = $section ?? 'profile';
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <h3>Dashboard</h3>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php?section=profile" class="sidebar-link <?= $section === 'profile' ? 'active' : '' ?>">
            <span class="icon">👤</span>
            Il Mio Profilo
        </a>
        <a href="dashboard.php?section=orders" class="sidebar-link <?= $section === 'orders' ? 'active' : '' ?>">
            <span class="icon">📦</span>
            I Miei Ordini
        </a>
        <hr>
        <a href="index.php" class="sidebar-link">
            <span class="icon">🏠</span>
            Torna alla Home
        </a>
    </nav>
</aside>