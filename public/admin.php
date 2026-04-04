<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';
require_once __DIR__ . '/../src/users.php';

requireAdmin();

$user = getCurrentUser();
$section = $_GET['sezione'] ?? 'panoramica';
$success = '';
$error = '';
$pageTitle = 'Admin Panel - MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/dashboard.css'];
$isAdmin = true;

// Gestione aggiornamento stato ordine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'update_order_status') {
            updateOrderStatus($_POST['order_id'], $_POST['status']);
            $success = "Stato ordine aggiornato!";
        } elseif ($_POST['action'] === 'update_preventivo_stato') {
            updatePreventivoStato($_POST['preventivo_id'], $_POST['stato']);
            $success = "Stato preventivo aggiornato!";
        } elseif ($_POST['action'] === 'delete_user') {
            deleteUser($_POST['user_id']);
            $success = "Utente eliminato!";
        } elseif ($_POST['action'] === 'update_user_role') {
            updateUserRole($_POST['user_id'], $_POST['role']);
            $success = "Ruolo utente aggiornato!";
        }
    } catch (Exception $e) {
        $error = "Errore: " . $e->getMessage();
    }
}

// Carica dati in base alla sezione
$stats = null;
$ordini = [];
$utenti = [];

$professionisti = [];
$preventivi = [];

if ($section === 'panoramica') {
    $orderStats = getOrderStats();
    $userStats = getUserStats();
    $stats = array_merge($orderStats, $userStats);
    $ultimi_ordini = getAllOrders(5);
    $ultimi_utenti = getAllUsers(5);
} elseif ($section === 'preventivi') {
    $preventivi = getAllPreventivi();
    $calendarData = getPreventiviCountByDate();
} elseif ($section === 'utenti') {
    $utenti = getAllUsers();
} elseif ($section === 'professionisti') {
    $professionisti = getAllProfessionals();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include 'includes/head.php'; ?>
</head>

<body>
    <?php include 'includes/navbar-dashboard.php'; ?>

    <div class="dashboard-container">
        <?php include 'includes/sidebar-admin.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <?php include 'includes/alerts.php'; ?>

            <?php if ($section === 'panoramica'): ?>
                <!-- Panoramica -->
                <div class="dashboard-section">
                    <div class="overview-header">
                        <div>
                            <h1>Panoramica</h1>
                            <p class="section-description">Riepilogo attività del <?= date('d/m/Y') ?></p>
                        </div>
                    </div>

                    <!-- Stat Cards -->
                    <div class="overview-grid">
                        <div class="ov-card ov-card--blue">
                            <div class="ov-card__icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <div class="ov-card__body">
                                <span class="ov-card__value"><?= (int)$stats['totale_utenti'] ?></span>
                                <span class="ov-card__label">Utenti totali</span>
                            </div>
                            <div class="ov-card__sub">
                                <span class="ov-badge ov-badge--green">+<?= (int)$stats['nuovi_oggi'] ?> oggi</span>
                            </div>
                        </div>

                        <div class="ov-card ov-card--purple">
                            <div class="ov-card__icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" />
                                    <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" />
                                </svg>
                            </div>
                            <div class="ov-card__body">
                                <span class="ov-card__value"><?= (int)($stats['totale_professionisti'] ?? 0) ?></span>
                                <span class="ov-card__label">Professionisti</span>
                            </div>
                            <div class="ov-card__sub">
                                <span class="ov-badge ov-badge--gray"><?= (int)$stats['totale_clienti'] ?> clienti</span>
                            </div>
                        </div>

                        <div class="ov-card ov-card--orange">
                            <div class="ov-card__icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                                    <line x1="3" y1="6" x2="21" y2="6" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                            </div>
                            <div class="ov-card__body">
                                <span class="ov-card__value"><?= (int)$stats['totale_ordini'] ?></span>
                                <span class="ov-card__label">Ordini totali</span>
                            </div>
                            <div class="ov-card__sub">
                                <span class="ov-badge ov-badge--orange"><?= (int)$stats['ordini_pending'] ?> in attesa</span>
                                <span class="ov-badge ov-badge--blue"><?= (int)$stats['ordini_processing'] ?> in lavorazione</span>
                            </div>
                        </div>

                        <div class="ov-card ov-card--green">
                            <div class="ov-card__icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23" />
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                                </svg>
                            </div>
                            <div class="ov-card__body">
                                <span class="ov-card__value">&euro;<?= number_format((float)$stats['totale_vendite'], 2, ',', '.') ?></span>
                                <span class="ov-card__label">Fatturato totale</span>
                            </div>
                            <div class="ov-card__sub">
                                <span class="ov-badge ov-badge--green"><?= (int)$stats['ordini_completati'] ?> completati</span>
                            </div>
                        </div>
                    </div>

                    <!-- Attività recente -->
                    <div class="overview-recent">
                        <!-- Ultimi ordini -->
                        <div class="recent-block">
                            <div class="recent-block__header">
                                <h3>Ultimi ordini</h3>
                                <a href="/admin/preventivi" class="recent-block__link">Vedi tutti →</a>
                            </div>
                            <?php if (empty($ultimi_ordini)): ?>
                                <p class="recent-block__empty">Nessun ordine ancora.</p>
                            <?php else: ?>
                                <div class="recent-list">
                                    <?php foreach ($ultimi_ordini as $o): ?>
                                        <div class="recent-item">
                                            <div class="recent-item__info">
                                                <span class="recent-item__title">#<?= $o['id'] ?> — <?= htmlspecialchars($o['username'] ?? 'N/A') ?></span>
                                                <span class="recent-item__date"><?= date('d/m/Y H:i', strtotime($o['creato_il'])) ?></span>
                                            </div>
                                            <div class="recent-item__right">
                                                <span class="recent-item__amount">&euro;<?= number_format((float)($o['totale'] ?? 0), 2, ',', '.') ?></span>
                                                <span class="ov-status ov-status--<?= $o['stato'] ?>"><?= ucfirst($o['stato']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Ultimi utenti -->
                        <div class="recent-block">
                            <div class="recent-block__header">
                                <h3>Ultimi utenti registrati</h3>
                                <a href="/admin/utenti" class="recent-block__link">Vedi tutti →</a>
                            </div>
                            <?php if (empty($ultimi_utenti)): ?>
                                <p class="recent-block__empty">Nessun utente ancora.</p>
                            <?php else: ?>
                                <div class="recent-list">
                                    <?php foreach ($ultimi_utenti as $u): ?>
                                        <div class="recent-item">
                                            <div class="recent-item__avatar"><?= mb_strtoupper(mb_substr($u['username'], 0, 1)) ?></div>
                                            <div class="recent-item__info">
                                                <span class="recent-item__title"><?= htmlspecialchars($u['username']) ?></span>
                                                <span class="recent-item__date"><?= htmlspecialchars($u['email']) ?></span>
                                            </div>
                                            <div class="recent-item__right">
                                                <span class="ov-role ov-role--<?= $u['ruolo'] ?>"><?= ucfirst($u['ruolo']) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($section === 'preventivi'): ?>
                <!-- Gestione Preventivi -->
                <div class="dashboard-section">
                    <h1>Tutti i Preventivi</h1>
                    <p class="section-description">Gestisci tutti i preventivi di trasporto</p>

                    <!-- ===== CALENDARIO RITIRI ===== -->
                    <div class="cal-wrapper">
                        <div class="cal-header">
                            <button class="cal-nav" id="calPrev" aria-label="Mese precedente">&#8249;</button>
                            <span class="cal-month-label" id="calMonthLabel"></span>
                            <button class="cal-nav" id="calNext" aria-label="Mese successivo">&#8250;</button>
                            <button class="cal-reset btn btn-ghost btn-sm" id="calReset" style="display:none">
                                Mostra tutti
                            </button>
                        </div>
                        <div class="cal-grid" id="calGrid"></div>
                    </div>

                    <?php if (empty($preventivi)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <h3>Nessun preventivo presente</h3>
                        </div>
                    <?php else: ?>
                        <div class="orders-table" id="preventiviTable">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data ritiro</th>
                                        <th>Cliente</th>
                                        <th>Telefono</th>
                                        <th>Moto</th>
                                        <th>Cilindrata</th>
                                        <th>Borse</th>
                                        <th>Ritiro</th>
                                        <th>Consegna</th>
                                        <th>Km</th>
                                        <th>Tipo</th>
                                        <th>Prezzo</th>
                                        <th>Stato</th>
                                        <th>Pagamento</th>
                                        <th>Ricevuto</th>
                                    </tr>
                                </thead>
                                <tbody id="preventiviTbody">
                                    <?php foreach ($preventivi as $p): ?>
                                        <tr data-date="<?= htmlspecialchars($p['data_ritiro'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <td><a href="/admin/preventivo/<?= $p['id'] ?>" class="table-link table-link--id">#<?= $p['id'] ?></a></td>
                                            <td class="td-date">
                                                <?php if (!empty($p['data_ritiro'])): ?>
                                                    <strong><?= date('d/m/Y', strtotime($p['data_ritiro'])) ?></strong>
                                                    <span class="td-date__day"><?= (new IntlDateFormatter('it_IT', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE'))->format(strtotime($p['data_ritiro'])) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($p['user_id'])): ?>
                                                    <a href="/admin/utente/<?= $p['user_id'] ?>" class="table-link"><?= htmlspecialchars($p['nome_cliente'] ?? ($p['username'] ?? 'Anonimo')) ?></a>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($p['nome_cliente'] ?? 'Anonimo') ?>
                                                <?php endif; ?>
                                                <?php if (!empty($p['email_cliente'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($p['email_cliente']) ?></small>
                                                <?php endif; ?>
                                                <?php if (!empty($p['codice_fiscale_cliente'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($p['codice_fiscale_cliente']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($p['telefono_cliente'] ?? '—') ?></td>
                                            <td><?= htmlspecialchars(trim(($p['marca_moto'] ?? '') . ' ' . ($p['modello_moto'] ?? ''))) ?></td>
                                            <td><?= htmlspecialchars($p['cilindrata'] ?? '—') ?></td>
                                            <td>
                                                <?php
                                                $borseVal = (float)($p['borse_laterali'] ?? 0);
                                                if ($borseVal > 0) {
                                                    echo '+€' . number_format($borseVal, 0);
                                                } else {
                                                    echo '<span class="text-muted">No</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="td-wrap"><?= htmlspecialchars($p['indirizzo_ritiro']) ?></td>
                                            <td class="td-wrap"><?= htmlspecialchars($p['indirizzo_consegna']) ?></td>
                                            <td><?= $p['distanza_km'] ? number_format((float)$p['distanza_km'], 0, ',', '.') . ' km' : '—' ?></td>
                                            <td>
                                                <?php
                                                $tipoClass = ['Standard' => 'badge-standard', 'Express' => 'badge-express', 'Urgente' => 'badge-urgente'];
                                                $tipo = $p['tipo_consegna'] ?? 'Standard';
                                                ?>
                                                <span class="badge <?= $tipoClass[$tipo] ?? '' ?>"><?= htmlspecialchars($tipo) ?></span>
                                            </td>
                                            <td>&euro;<?= number_format((float)($p['prezzo_finale'] ?? 0), 2, ',', '.') ?></td>
                                            <td>
                                                <form method="POST" class="inline-form">
                                                    <input type="hidden" name="action" value="update_preventivo_stato">
                                                    <input type="hidden" name="preventivo_id" value="<?= $p['id'] ?>">
                                                    <select name="stato" onchange="this.form.submit()" class="status-select">
                                                        <?php foreach (['bozza', 'inviato', 'confermato', 'in_lavorazione', 'completato', 'annullato'] as $s): ?>
                                                            <option value="<?= $s ?>" <?= $p['stato'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($p['creato_il'])) ?></td>
                                            <td>
                                                <?php
                                                $pgStato = $p['pagamento_stato'] ?? 'non_pagato';
                                                $pgClass = [
                                                    'non_pagato' => 'badge-muted',
                                                    'pagato'     => 'badge-success',
                                                    'fallito'    => 'badge-danger',
                                                    'rimborsato' => 'badge-warning',
                                                ];
                                                $pgLabel = [
                                                    'non_pagato' => 'Non pagato',
                                                    'pagato'     => 'Pagato',
                                                    'fallito'    => 'Fallito',
                                                    'rimborsato' => 'Rimborsato',
                                                ];
                                                ?>
                                                <span class="badge <?= $pgClass[$pgStato] ?? 'badge-muted' ?>">
                                                    <?= $pgLabel[$pgStato] ?? htmlspecialchars($pgStato) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($p['creato_il'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="cal-table-info" id="calTableInfo" style="display:none"></p>
                    <?php endif; ?>
                </div>

            <?php elseif ($section === 'utenti'): ?>
                <!-- Gestione Utenti -->
                <div class="dashboard-section">
                    <h1>Gestione Utenti</h1>
                    <p class="section-description">Visualizza e gestisci tutti gli utenti</p>

                    <?php if (empty($utenti)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">👥</div>
                            <h3>Nessun utente presente</h3>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Nome</th>
                                        <th>Cognome</th>
                                        <th>Ruolo</th>
                                        <th>Registrato</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utenti as $u): ?>
                                        <tr>
                                            <td><?= $u['id'] ?></td>
                                            <td><a href="/admin/utente/<?= $u['id'] ?>" class="table-link"><?= htmlspecialchars($u['username']) ?></a></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td><?= htmlspecialchars($u['nome'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($u['cognome'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $u['ruolo'] ?>">
                                                    <?= ucfirst($u['ruolo']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($u['creato_il'])) ?></td>
                                            <td class="td-actions">
                                                <div class="td-actions-inner">

                                                    <?php if ($u['id'] != $user['id']): ?>
                                                        <form method="POST" class="inline-form" onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?')">
                                                            <input type="hidden" name="action" value="delete_user">
                                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                            <button type="submit" class="btn btn-small btn-danger">Elimina</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($section === 'professionisti'): ?>
                <!-- Gestione Professionisti -->
                <div class="dashboard-section">
                    <h1>Professionisti</h1>
                    <p class="section-description">Aziende e professionisti registrati</p>

                    <?php if (empty($professionisti)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🏢</div>
                            <h3>Nessun professionista registrato</h3>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ragione Sociale</th>
                                        <th>Referente</th>
                                        <th>Email</th>
                                        <th>P.IVA</th>
                                        <th>Attività</th>
                                        <th>Città</th>
                                        <th>Sconto</th>
                                        <th>Registrato</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($professionisti as $p): ?>
                                        <tr>
                                            <td><?= $p['id'] ?></td>
                                            <td><a href="/admin/utente/<?= $p['id'] ?>" class="table-link"><?= htmlspecialchars($p['ragione_sociale'] ?? '-') ?></a></td>
                                            <td><?= htmlspecialchars(trim(($p['nome'] ?? '') . ' ' . ($p['cognome'] ?? ''))) ?></td>
                                            <td><?= htmlspecialchars($p['email']) ?></td>
                                            <td><?= htmlspecialchars($p['partita_iva'] ?? '-') ?></td>
                                            <td class="td-wrap"><?= htmlspecialchars($p['tipo_attivita'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($p['citta'] ?? '-') ?></td>
                                            <td><?= number_format((float)($p['sconto_percentuale'] ?? 0), 1) ?>%</td>
                                            <td><?= date('d/m/Y', strtotime($p['creato_il'])) ?></td>
                                            <td class="td-actions">
                                                <div class="td-actions-inner">

                                                    <?php if ($p['id'] != $user['id']): ?>
                                                        <form method="POST" class="inline-form" onsubmit="return confirm('Eliminare questo professionista?')">
                                                            <input type="hidden" name="action" value="delete_user">
                                                            <input type="hidden" name="user_id" value="<?= $p['id'] ?>">
                                                            <button type="submit" class="btn btn-small btn-danger">Elimina</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
        </main>
    </div>

    <script src="/js/modules/nav.js"></script>
    <script src="/js/modules/forms.js"></script>
    <?php if ($section === 'preventivi'): ?>
        <script>
            (function() {
                'use strict';

                /* ---- Dati dal PHP ---- */
                var COUNTS = <?= json_encode($calendarData ?? [], JSON_UNESCAPED_UNICODE) ?>;

                /* ---- Stato ---- */
                var today = new Date();
                var viewYear = today.getFullYear();
                var viewMonth = today.getMonth(); // 0-based
                var activeDate = null; // 'YYYY-MM-DD' selezionato

                /* ---- Elementi DOM ---- */
                var grid = document.getElementById('calGrid');
                var label = document.getElementById('calMonthLabel');
                var btnPrev = document.getElementById('calPrev');
                var btnNext = document.getElementById('calNext');
                var btnReset = document.getElementById('calReset');
                var tbody = document.getElementById('preventiviTbody');
                var tableInfo = document.getElementById('calTableInfo');

                if (!grid) return;

                var MONTHS = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno',
                    'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'
                ];
                var DAYS = ['L', 'M', 'M', 'G', 'V', 'S', 'D'];

                /* ---- Render calendario ---- */
                function render() {
                    label.textContent = MONTHS[viewMonth] + ' ' + viewYear;
                    grid.innerHTML = '';

                    // Intestazioni giorni
                    DAYS.forEach(function(d) {
                        var h = document.createElement('div');
                        h.className = 'cal-day-name';
                        h.textContent = d;
                        grid.appendChild(h);
                    });

                    var firstDay = new Date(viewYear, viewMonth, 1).getDay();
                    // domenica=0 → portala a 6 (lunedì come primo giorno)
                    firstDay = firstDay === 0 ? 6 : firstDay - 1;

                    var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

                    // Celle vuote iniziali
                    for (var i = 0; i < firstDay; i++) {
                        var empty = document.createElement('div');
                        empty.className = 'cal-day cal-day--empty';
                        grid.appendChild(empty);
                    }

                    // Celle con i giorni
                    for (var d = 1; d <= daysInMonth; d++) {
                        var mm = String(viewMonth + 1).padStart(2, '0');
                        var dd = String(d).padStart(2, '0');
                        var iso = viewYear + '-' + mm + '-' + dd;
                        var count = COUNTS[iso] || 0;

                        var cell = document.createElement('div');
                        cell.className = 'cal-day';
                        cell.dataset.iso = iso;
                        if (count > 0) cell.classList.add('cal-day--has-events');
                        if (iso === activeDate) cell.classList.add('cal-day--active');

                        var todayIso = today.getFullYear() + '-' +
                            String(today.getMonth() + 1).padStart(2, '0') + '-' +
                            String(today.getDate()).padStart(2, '0');
                        if (iso === todayIso) cell.classList.add('cal-day--today');

                        cell.innerHTML = '<span class="cal-day__num">' + d + '</span>' +
                            (count > 0 ? '<span class="cal-day__badge">' + count + '</span>' : '');

                        cell.addEventListener('click', function() {
                            var clickedIso = this.dataset.iso;
                            if (COUNTS[clickedIso] === undefined || COUNTS[clickedIso] === 0) return;
                            if (activeDate === clickedIso) {
                                // deseleziona
                                activeDate = null;
                                filterTable(null);
                            } else {
                                activeDate = clickedIso;
                                filterTable(clickedIso);
                            }
                            render();
                            btnReset.style.display = activeDate ? 'inline-flex' : 'none';
                        });

                        grid.appendChild(cell);
                    }
                }

                /* ---- Filtra tabella ---- */
                function filterTable(isoDate) {
                    if (!tbody) return;
                    var rows = tbody.querySelectorAll('tr');
                    var shown = 0;
                    rows.forEach(function(row) {
                        if (!isoDate || row.dataset.date === isoDate) {
                            row.style.display = '';
                            shown++;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    if (tableInfo) {
                        if (isoDate) {
                            var parts = isoDate.split('-');
                            var nicDate = parts[2] + '/' + parts[1] + '/' + parts[0];
                            tableInfo.textContent = 'Mostrando ' + shown + ' preventivo/i per il ' + nicDate;
                            tableInfo.style.display = 'block';
                        } else {
                            tableInfo.style.display = 'none';
                        }
                    }
                }

                /* ---- Navigazione ---- */
                btnPrev.addEventListener('click', function() {
                    viewMonth--;
                    if (viewMonth < 0) {
                        viewMonth = 11;
                        viewYear--;
                    }
                    render();
                });
                btnNext.addEventListener('click', function() {
                    viewMonth++;
                    if (viewMonth > 11) {
                        viewMonth = 0;
                        viewYear++;
                    }
                    render();
                });
                btnReset.addEventListener('click', function() {
                    activeDate = null;
                    filterTable(null);
                    render();
                    this.style.display = 'none';
                });

                render();

                // Se ci sono eventi nel mese corrente, naviga al primo mese con eventi
                var keys = Object.keys(COUNTS);
                if (keys.length > 0) {
                    keys.sort();
                    var firstIso = keys[0];
                    var parts = firstIso.split('-');
                    viewYear = parseInt(parts[0], 10);
                    viewMonth = parseInt(parts[1], 10) - 1;
                    render();
                }
            }());
        </script>
    <?php endif; ?>
</body>

</html>