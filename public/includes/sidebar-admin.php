<?php

/**
 * Sidebar per il pannello Admin.
 * Variabili attese: $section
 */
$section = $section ?? 'panoramica';
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-header__badge badge badge--admin">Admin</div>
        <h3>Pannello Admin</h3>
    </div>
    <nav class="sidebar-nav">
        <a href="/admin/panoramica"
            class="sidebar-link <?= $section === 'panoramica' ? 'active' : '' ?>">
            <span class="icon">📊</span> Panoramica
        </a>
        <a href="/admin/utenti"
            class="sidebar-link <?= $section === 'utenti' ? 'active' : '' ?>">
            <span class="icon">👥</span> Clienti
        </a>
        <a href="/admin/professionisti"
            class="sidebar-link <?= $section === 'professionisti' ? 'active' : '' ?>">
            <span class="icon">🏢</span> Professionisti
        </a>
        <a href="/admin/preventivi"
            class="sidebar-link <?= $section === 'preventivi' ? 'active' : '' ?>">
            <span class="icon">�</span> Preventivi
        </a>
        <hr>
        <a href="/" class="sidebar-link">
            <span class="icon">🏠</span> Home
        </a>
        <a href="/logout" class="sidebar-link sidebar-link--danger">
            <span class="icon">🚪</span> Esci
        </a>
    </nav>
</aside>