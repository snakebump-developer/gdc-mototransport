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
        </a> <a href="/admin/moto-bozze"
            class="sidebar-link <?= $section === 'moto-bozze' ? 'active' : '' ?>">
            <span class="icon">🏍️</span> Moto in bozza
            <?php
            // Badge contatore bozze in attesa
            try {
                require_once __DIR__ . '/../../src/db.php';
                $cnt = (int)$pdo->query("SELECT COUNT(*) FROM moto_bozze WHERE stato='in_attesa'")->fetchColumn();
                if ($cnt > 0) echo '<span class="sidebar-badge">' . $cnt . '</span>';
            } catch (Exception $e) {
            }
            ?>
        </a>
        <hr>
        <?php
        // Legge stato manutenzione corrente
        $_maintOn = false;
        try {
            require_once __DIR__ . '/../../src/db.php';
            $pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT NOT NULL DEFAULT '') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $pdo->exec("INSERT IGNORE INTO app_settings (setting_key, setting_value) VALUES ('maintenance_mode', '0')");
            $_maintRow = $pdo->query("SELECT setting_value FROM app_settings WHERE setting_key='maintenance_mode' LIMIT 1")->fetch(PDO::FETCH_NUM);
            $_maintOn  = ($_maintRow && $_maintRow[0] === '1');
        } catch (\Exception $_e) {}
        ?>
        <button id="maintenanceToggle"
                class="sidebar-link sidebar-link--maint <?= $_maintOn ? 'sidebar-link--maint-on' : '' ?>"
                title="Attiva/disattiva modalità manutenzione">
            <span class="icon" id="maintIcon"><?= $_maintOn ? '🔴' : '🟢' ?></span>
            <span id="maintText">Manutenzione <?= $_maintOn ? 'ON' : 'OFF' ?></span>
        </button>
        <script>
        document.getElementById('maintenanceToggle').addEventListener('click', function () {
            const btn = this;
            btn.disabled = true;
            fetch('/api/toggle-maintenance', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    const on = d.maintenance_mode;
                    document.getElementById('maintIcon').textContent = on ? '🔴' : '🟢';
                    document.getElementById('maintText').textContent = 'Manutenzione ' + (on ? 'ON' : 'OFF');
                    btn.classList.toggle('sidebar-link--maint-on', on);
                } else {
                    alert('Errore: ' + (d.error || 'sconosciuto'));
                }
            })
            .catch(() => alert('Errore di rete.'))
            .finally(() => { btn.disabled = false; });
        });
        </script>
        <hr>
        <a href="/" class="sidebar-link">
            <span class="icon">🏠</span> Home
        </a>
        <a href="/logout" class="sidebar-link sidebar-link--danger">
            <span class="icon">🚪</span> Esci
        </a>
    </nav>
</aside>