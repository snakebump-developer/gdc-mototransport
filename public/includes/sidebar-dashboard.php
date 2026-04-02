<?php

/**
 * Sidebar per la Dashboard utente.
 * Variabili attese: $section
 */
$section = $section ?? 'profile';
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <h3>Dashboard</h3>
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