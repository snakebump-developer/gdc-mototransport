<?php
require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/orders.php';
require_once __DIR__ . '/../../../src/users.php';

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
        } elseif ($_POST['action'] === 'update_discount') {
            $sconto = (float)str_replace(',', '.', $_POST['sconto'] ?? '0');
            updateProfessionalDiscount((int)$_POST['user_id'], $sconto);
            $success = "Sconto aggiornato!";
        } elseif ($_POST['action'] === 'approve_moto_bozza') {
            require_once __DIR__ . '/../../../src/db.php';
            $bId = (int)$_POST['bozza_id'];
            $bozza = $pdo->prepare("SELECT * FROM moto_bozze WHERE id=? AND stato='in_attesa'")->execute([$bId]) ? $pdo->query("SELECT * FROM moto_bozze WHERE id=$bId AND stato='in_attesa'")->fetch() : null;
            if ($bozza) {
                $pdo->prepare("INSERT IGNORE INTO catalogo_moto (marca, modello) VALUES (?,?)")->execute([$bozza['marca'], $bozza['modello']]);
                $pdo->prepare("UPDATE moto_bozze SET stato='approvata' WHERE id=?")->execute([$bId]);
                $success = "Moto approvata e aggiunta al catalogo!";
            }
        } elseif ($_POST['action'] === 'reject_moto_bozza') {
            require_once __DIR__ . '/../../../src/db.php';
            $bId = (int)$_POST['bozza_id'];
            $pdo->prepare("UPDATE moto_bozze SET stato='rifiutata', note_admin=? WHERE id=?")->execute([
                htmlspecialchars(strip_tags($_POST['nota'] ?? ''), ENT_QUOTES, 'UTF-8'),
                $bId,
            ]);
            $success = "Proposta rifiutata.";
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

$ITEMS_PER_PAGE = 10;
$searchQuery  = '';
$currentPage  = 1;
$totalItems   = 0;
$totalPages   = 1;

if ($section === 'panoramica') {
    $preventiviStats = getPreventiviStats();
    $userStats = getUserStats();
    $ultimi_preventivi = getLastPreventivi(5);
    $ultimi_utenti = getAllUsers(5);
} elseif ($section === 'preventivi') {
    $preventivi = getAllPreventivi();
    $calendarData = getPreventiviCountByDate();
} elseif ($section === 'utenti') {
    $utenti = getAllUsers(PHP_INT_MAX, 0);
} elseif ($section === 'professionisti') {
    $professionisti = getAllProfessionals(PHP_INT_MAX, 0);
} elseif ($section === 'moto-bozze') {
    require_once __DIR__ . '/../../../src/db.php';
    $motoBozze = $pdo->query("SELECT * FROM moto_bozze ORDER BY stato='in_attesa' DESC, creato_il DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include __DIR__ . '/../../includes/head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/../../includes/navbar-dashboard.php'; ?>

    <div class="dashboard-container">
        <?php include __DIR__ . '/../../includes/sidebar-admin.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <?php include __DIR__ . '/../../includes/alerts.php'; ?>

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
                                <span class="ov-card__value" id="stat-totale-utenti"><?= (int)$userStats['totale_utenti'] ?></span>
                                <span class="ov-card__label">Utenti totali</span>
                            </div>
                            <div class="ov-card__sub">
                                <span class="ov-badge ov-badge--green" id="stat-nuovi-oggi">+<?= (int)$userStats['nuovi_oggi'] ?> oggi</span>
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
                                <span class="ov-card__value" id="stat-totale-professionisti"><?= (int)($userStats['totale_professionisti'] ?? 0) ?></span>
                                <span class="ov-card__label">Professionisti</span>
                            </div>
                            <div class="ov-card__sub">
                                <span class="ov-badge ov-badge--gray" id="stat-totale-clienti"><?= (int)$userStats['totale_clienti'] ?> clienti</span>
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
                                <span class="ov-card__value" id="stat-totale-preventivi"><?= (int)$preventiviStats['totale_preventivi'] ?></span>
                                <span class="ov-card__label">Preventivi totali</span>
                            </div>
                            <div class="ov-card__sub">
                                <span class="ov-badge ov-badge--orange" id="stat-preventivi-inviati"><?= (int)$preventiviStats['preventivi_inviati'] ?> inviati</span>
                                <span class="ov-badge ov-badge--blue" id="stat-preventivi-in-lavorazione"><?= (int)$preventiviStats['preventivi_in_lavorazione'] ?> in lavorazione</span>
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
                                <span class="ov-card__value" id="stat-totale-fatturato">&euro;<?= number_format((float)$preventiviStats['totale_fatturato'], 2, ',', '.') ?></span>
                                <span class="ov-card__label">Fatturato totale</span>
                            </div>
                            <div class="ov-card__sub">
                                <span class="ov-badge ov-badge--green" id="stat-preventivi-confermati"><?= (int)$preventiviStats['preventivi_confermati'] ?> confermati</span>
                            </div>
                        </div>
                    </div>

                    <!-- Attività recente -->
                    <div class="overview-recent">
                        <!-- Ultimi preventivi -->
                        <div class="recent-block">
                            <div class="recent-block__header">
                                <h3>Ultimi preventivi</h3>
                                <a href="/admin/preventivi" class="recent-block__link">Vedi tutti →</a>
                            </div>
                            <div id="ultimi-preventivi-wrap">
                                <?php if (empty($ultimi_preventivi)): ?>
                                    <p class="recent-block__empty">Nessun preventivo ancora.</p>
                                <?php else: ?>
                                    <div class="recent-list">
                                        <?php foreach ($ultimi_preventivi as $p): ?>
                                            <div class="recent-item">
                                                <div class="recent-item__info">
                                                    <span class="recent-item__title">#<?= $p['id'] ?> — <?= htmlspecialchars($p['cliente'] ?? 'N/A') ?></span>
                                                    <span class="recent-item__date"><?= date('d/m/Y H:i', strtotime($p['creato_il'])) ?></span>
                                                </div>
                                                <div class="recent-item__right">
                                                    <span class="recent-item__amount">&euro;<?= number_format((float)($p['prezzo_finale'] ?? 0), 2, ',', '.') ?></span>
                                                    <span class="ov-status ov-status--<?= $p['stato'] ?>"><?= ucfirst(str_replace('_', ' ', $p['stato'])) ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Ultimi utenti -->
                        <div class="recent-block">
                            <div class="recent-block__header">
                                <h3>Ultimi utenti registrati</h3>
                                <a href="/admin/utenti" class="recent-block__link">Vedi tutti →</a>
                            </div>
                            <div id="ultimi-utenti-wrap">
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
                </div>

            <?php elseif ($section === 'preventivi'): ?>
                <!-- Gestione Preventivi -->
                <div class="dashboard-section">
                    <h1>Tutti i Preventivi</h1>
                    <p class="section-description">Gestisci tutti i preventivi di trasporto</p>

                    <!-- ===== BARRA CONTROLLI (ricerca + filtri) ===== -->
                    <div class="table-controls">
                        <!-- Sinistra: ricerca -->
                        <div class="search-toolbar__input-wrap" style="flex:1;max-width:480px;">
                            <svg class="search-toolbar__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" id="preventiviSearch" class="search-toolbar__input" placeholder="Cerca cliente, moto, indirizzo…" autocomplete="off">
                            <button type="button" class="search-toolbar__clear" id="preventiviSearchClear" style="display:none;background:none;border:none;cursor:pointer;" aria-label="Cancella ricerca">&#x2715;</button>
                        </div>

                        <!-- Destra: filtri -->
                        <div style="display:flex;align-items:center;gap:.5rem;margin-left:auto;">
                            <button class="cal-reset btn btn-ghost btn-sm" id="calReset" style="display:none">Mostra tutti</button>

                            <!-- Col-picker -->
                            <div class="col-picker" id="colPicker-preventivi">
                                <button type="button" class="btn btn-ghost btn-sm col-picker__toggle" id="colPickerBtn-preventivi" aria-expanded="false" aria-haspopup="true">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="18" rx="1" />
                                        <rect x="14" y="3" width="7" height="18" rx="1" />
                                    </svg>
                                    Colonne
                                </button>
                                <div class="col-picker__dropdown" id="colPickerDropdown-preventivi" hidden>
                                    <p class="col-picker__label">Mostra / nascondi colonne</p>
                                    <div class="col-picker__items">
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="id"> ID</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="data-ritiro"> Data ritiro</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="cliente"> Cliente</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="telefono"> Telefono</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="moto"> Moto</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="cilindrata"> Cilindrata</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="borse"> Borse</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="ritiro"> Ritiro</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="consegna"> Consegna</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="km"> Km</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="tipo"> Tipo</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="prezzo"> Prezzo</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="stato"> Stato</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="pagamento"> Pagamento</label>
                                        <label class="col-picker__item"><input type="checkbox" data-table="tbl-preventivi" data-col="ricevuto"> Ricevuto</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottone calendario -->
                            <button class="btn btn-ghost btn-sm cal-open-btn" id="calOpenBtn" type="button">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                Filtra per data
                            </button>
                        </div>
                    </div>

                    <!-- Modale calendario -->
                    <div class="cal-modal-overlay" id="calModalOverlay" hidden>
                        <div class="cal-modal" id="calModal" role="dialog" aria-modal="true" aria-labelledby="calModalTitleEl">
                            <div class="cal-modal__header">
                                <span class="cal-modal__title" id="calModalTitleEl">Seleziona una data di ritiro</span>
                                <button class="cal-modal__close" id="calModalClose" type="button" aria-label="Chiudi">&#x2715;</button>
                            </div>
                            <div class="cal-wrapper cal-wrapper--modal">
                                <div class="cal-header">
                                    <button class="cal-nav" id="calPrev" aria-label="Mese precedente">&#8249;</button>
                                    <span class="cal-month-label" id="calMonthLabel"></span>
                                    <button class="cal-nav" id="calNext" aria-label="Mese successivo">&#8250;</button>
                                </div>
                                <div class="cal-grid" id="calGrid"></div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($preventivi)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <h3>Nessun preventivo presente</h3>
                        </div>
                    <?php else: ?>
                        <div class="orders-table" id="preventiviTable">
                            <table id="tbl-preventivi">
                                <thead>
                                    <tr>
                                        <th data-col="id">ID</th>
                                        <th data-col="data-ritiro">Data ritiro</th>
                                        <th data-col="cliente">Cliente</th>
                                        <th data-col="telefono">Telefono</th>
                                        <th data-col="moto">Moto</th>
                                        <th data-col="cilindrata">Cilindrata</th>
                                        <th data-col="borse">Borse</th>
                                        <th data-col="ritiro">Ritiro</th>
                                        <th data-col="consegna">Consegna</th>
                                        <th data-col="km">Km</th>
                                        <th data-col="tipo">Tipo</th>
                                        <th data-col="prezzo">Prezzo</th>
                                        <th data-col="stato">Stato</th>
                                        <th data-col="pagamento">Pagamento</th>
                                        <th data-col="richiesta">Richiesta il</th>
                                        <th data-col="doc-trasporto">Doc. Trasporto</th>
                                    </tr>
                                </thead>
                                <tbody id="preventiviTbody">
                                    <?php foreach ($preventivi as $p): ?>
                                        <tr data-date="<?= htmlspecialchars($p['data_ritiro'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <td data-col="id"><a href="/admin/preventivo/<?= $p['id'] ?>" class="table-link table-link--id">#<?= $p['id'] ?></a></td>
                                            <td data-col="data-ritiro" class="td-date">
                                                <?php if (!empty($p['data_ritiro'])): ?>
                                                    <strong><?= date('d/m/Y', strtotime($p['data_ritiro'])) ?></strong>
                                                    <?php $giorni = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato']; ?>
                                                    <span class="td-date__day"><?= $giorni[date('w', strtotime($p['data_ritiro']))] ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-col="cliente">
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
                                            <td data-col="telefono"><?= htmlspecialchars($p['telefono_cliente'] ?? '—') ?></td>
                                            <td data-col="moto"><?= htmlspecialchars(trim(($p['marca_moto'] ?? '') . ' ' . ($p['modello_moto'] ?? ''))) ?></td>
                                            <td data-col="cilindrata"><?= htmlspecialchars($p['cilindrata'] ?? '—') ?></td>
                                            <td data-col="borse">
                                                <?php
                                                $borseVal = (float)($p['borse_laterali'] ?? 0);
                                                if ($borseVal > 0) {
                                                    echo '+€' . number_format($borseVal, 0);
                                                } else {
                                                    echo '<span class="text-muted">No</span>';
                                                }
                                                ?>
                                            </td>
                                            <td data-col="ritiro" class="td-wrap"><?= htmlspecialchars($p['indirizzo_ritiro']) ?></td>
                                            <td data-col="consegna" class="td-wrap"><?= htmlspecialchars($p['indirizzo_consegna']) ?></td>
                                            <td data-col="km"><?= $p['distanza_km'] ? number_format((float)$p['distanza_km'], 0, ',', '.') . ' km' : '—' ?></td>
                                            <td data-col="tipo">
                                                <?php
                                                $tipoClass = ['Standard' => 'badge-standard', 'Express' => 'badge-express', 'Urgente' => 'badge-urgente'];
                                                $tipo = $p['tipo_consegna'] ?? 'Standard';
                                                ?>
                                                <span class="badge <?= $tipoClass[$tipo] ?? '' ?>"><?= htmlspecialchars($tipo) ?></span>
                                            </td>
                                            <td data-col="prezzo">&euro;<?= number_format((float)($p['prezzo_finale'] ?? 0), 2, ',', '.') ?></td>
                                            <td data-col="stato">
                                                <?php
                                                // Normalizza vecchi valori DB (bozza/inviato → nuovo)
                                                $statoDisplay = in_array($p['stato'], ['bozza', 'inviato']) ? 'nuovo' : $p['stato'];
                                                $statiLabels  = [
                                                    'nuovo'          => 'Nuovo',
                                                    'confermato'     => 'Confermato',
                                                    'in_lavorazione' => 'In lavorazione',
                                                    'completato'     => 'Completato',
                                                    'annullato'      => 'Annullato',
                                                ];
                                                ?>
                                                <form method="POST" class="inline-form">
                                                    <input type="hidden" name="action" value="update_preventivo_stato">
                                                    <input type="hidden" name="preventivo_id" value="<?= $p['id'] ?>">
                                                    <select name="stato" onchange="this.form.submit()" class="status-select">
                                                        <?php foreach ($statiLabels as $val => $label): ?>
                                                            <option value="<?= $val ?>" <?= $statoDisplay === $val ? 'selected' : '' ?>><?= $label ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td data-col="pagamento">
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
                                            <td data-col="richiesta"><?= date('d/m/Y', strtotime($p['creato_il'])) ?></td>
                                            <td data-col="doc-trasporto" style="white-space:nowrap;">
                                                <?php if (($p['pagamento_stato'] ?? '') === 'pagato'): ?>
                                                    <a href="/api/lettera-vettura?id=<?= $p['id'] ?>"
                                                        target="_blank"
                                                        title="Scarica Lettera di Vettura PDF"
                                                        class="btn-ldv"
                                                        aria-label="Scarica lettera di vettura preventivo #<?= $p['id'] ?>">
                                                        &#x1F4E5; Lettera Vettura
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted" title="Disponibile solo dopo il pagamento">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="cal-table-info" id="calTableInfo" style="display:none"></p>
                        <nav class="pagination" id="preventiviPagination" aria-label="Paginazione preventivi"></nav>
                    <?php endif; ?>
                </div>

            <?php elseif ($section === 'utenti'): ?>
                <!-- Gestione Utenti -->
                <div class="dashboard-section">
                    <h1>Gestione Utenti</h1>
                    <p class="section-description">Visualizza e gestisci tutti gli utenti</p>

                    <div class="table-controls">
                        <div class="search-toolbar__input-wrap" style="flex:1;max-width:480px;">
                            <svg class="search-toolbar__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" id="utentiSearch" class="search-toolbar__input" placeholder="Cerca per username, email, nome…" autocomplete="off">
                            <button type="button" class="search-toolbar__clear" id="utentiSearchClear" style="display:none;background:none;border:none;cursor:pointer;" aria-label="Cancella ricerca">&#x2715;</button>
                        </div>
                        <div class="col-picker" id="colPicker-utenti" style="margin-left:auto;">
                            <button type="button" class="btn btn-ghost btn-sm col-picker__toggle" id="colPickerBtn-utenti" aria-expanded="false" aria-haspopup="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="18" rx="1" />
                                    <rect x="14" y="3" width="7" height="18" rx="1" />
                                </svg>
                                Colonne
                            </button>
                            <div class="col-picker__dropdown" id="colPickerDropdown-utenti" hidden>
                                <p class="col-picker__label">Mostra / nascondi colonne</p>
                                <div class="col-picker__items">
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="id"> ID</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="email"> Email</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="nome"> Nome</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="cognome"> Cognome</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="ruolo"> Ruolo</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="registrato"> Registrato</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($utenti)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">👥</div>
                            <h3>Nessun utente presente</h3>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table id="tbl-utenti">
                                <thead>
                                    <tr>
                                        <th data-col="id">ID</th>
                                        <th data-col="username">Username</th>
                                        <th data-col="email">Email</th>
                                        <th data-col="nome">Nome</th>
                                        <th data-col="cognome">Cognome</th>
                                        <th data-col="ruolo">Ruolo</th>
                                        <th data-col="registrato">Registrato</th>
                                        <th data-col="azioni">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utenti as $u): ?>
                                        <tr>
                                            <td data-col="id"><?= $u['id'] ?></td>
                                            <td data-col="username"><a href="/admin/utente/<?= $u['id'] ?>" class="table-link"><?= htmlspecialchars($u['username']) ?></a></td>
                                            <td data-col="email"><?= htmlspecialchars($u['email']) ?></td>
                                            <td data-col="nome"><?= htmlspecialchars($u['nome'] ?? '-') ?></td>
                                            <td data-col="cognome"><?= htmlspecialchars($u['cognome'] ?? '-') ?></td>
                                            <td data-col="ruolo">
                                                <span class="badge badge-<?= $u['ruolo'] ?>">
                                                    <?= ucfirst($u['ruolo']) ?>
                                                </span>
                                            </td>
                                            <td data-col="registrato"><?= date('d/m/Y', strtotime($u['creato_il'])) ?></td>
                                            <td data-col="azioni" class="td-actions">
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
                        <nav class="pagination" id="utentiPagination" aria-label="Paginazione utenti"></nav>
                    <?php endif; ?>
                </div>
            <?php elseif ($section === 'professionisti'): ?>
                <!-- Gestione Professionisti -->
                <div class="dashboard-section">
                    <h1>Professionisti</h1>
                    <p class="section-description">Aziende e professionisti registrati</p>

                    <div class="table-controls">
                        <div class="search-toolbar__input-wrap" style="flex:1;max-width:480px;">
                            <svg class="search-toolbar__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" id="professionistiSearch" class="search-toolbar__input" placeholder="Cerca per ragione sociale, email, P.IVA, città…" autocomplete="off">
                            <button type="button" class="search-toolbar__clear" id="professionistiSearchClear" style="display:none;background:none;border:none;cursor:pointer;" aria-label="Cancella ricerca">&#x2715;</button>
                        </div>
                        <div class="col-picker" id="colPicker-professionisti" style="margin-left:auto;">
                            <button type="button" class="btn btn-ghost btn-sm col-picker__toggle" id="colPickerBtn-professionisti" aria-expanded="false" aria-haspopup="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="18" rx="1" />
                                    <rect x="14" y="3" width="7" height="18" rx="1" />
                                </svg>
                                Colonne
                            </button>
                            <div class="col-picker__dropdown" id="colPickerDropdown-professionisti" hidden>
                                <p class="col-picker__label">Mostra / nascondi colonne</p>
                                <div class="col-picker__items">
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-professionisti" data-col="id"> ID</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-professionisti" data-col="referente"> Referente</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-professionisti" data-col="email"> Email</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-professionisti" data-col="piva"> P.IVA</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-professionisti" data-col="attivita"> Attività</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-professionisti" data-col="citta"> Città</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-professionisti" data-col="sconto"> Sconto</label>
                                    <label class="col-picker__item"><input type="checkbox" data-table="tbl-professionisti" data-col="registrato"> Registrato</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($professionisti)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🏢</div>
                            <h3>Nessun professionista registrato</h3>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table id="tbl-professionisti">
                                <thead>
                                    <tr>
                                        <th data-col="id">ID</th>
                                        <th data-col="ragione-sociale">Ragione Sociale</th>
                                        <th data-col="referente">Referente</th>
                                        <th data-col="email">Email</th>
                                        <th data-col="piva">P.IVA</th>
                                        <th data-col="attivita">Attività</th>
                                        <th data-col="citta">Città</th>
                                        <th data-col="sconto">Sconto</th>
                                        <th data-col="registrato">Registrato</th>
                                        <th data-col="azioni">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($professionisti as $p): ?>
                                        <tr>
                                            <td data-col="id"><?= $p['id'] ?></td>
                                            <td data-col="ragione-sociale"><a href="/admin/utente/<?= $p['id'] ?>" class="table-link"><?= htmlspecialchars($p['ragione_sociale'] ?? '-') ?></a></td>
                                            <td data-col="referente"><?= htmlspecialchars(trim(($p['nome'] ?? '') . ' ' . ($p['cognome'] ?? ''))) ?></td>
                                            <td data-col="email"><?= htmlspecialchars($p['email']) ?></td>
                                            <td data-col="piva"><?= htmlspecialchars($p['partita_iva'] ?? '-') ?></td>
                                            <td data-col="attivita" class="td-wrap"><?= htmlspecialchars($p['tipo_attivita'] ?? '-') ?></td>
                                            <td data-col="citta"><?= htmlspecialchars($p['citta'] ?? '-') ?></td>
                                            <td data-col="sconto">
                                                <form method="POST" class="inline-form discount-form">
                                                    <input type="hidden" name="action" value="update_discount">
                                                    <input type="hidden" name="user_id" value="<?= $p['id'] ?>">
                                                    <div class="discount-inline">
                                                        <input type="number" name="sconto"
                                                            value="<?= number_format((float)($p['sconto_percentuale'] ?? 0), 1, '.', '') ?>"
                                                            min="0" max="100" step="0.5"
                                                            class="discount-input"
                                                            aria-label="Sconto %">
                                                        <span class="discount-pct">%</span>
                                                        <button type="submit" class="btn btn-xs btn-save" title="Salva sconto">&#10003;</button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td data-col="registrato"><?= date('d/m/Y', strtotime($p['creato_il'])) ?></td>
                                            <td data-col="azioni" class="td-actions">
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
                        <nav class="pagination" id="professionistiPagination" aria-label="Paginazione professionisti"></nav>
                    <?php endif; ?>
                </div>

            <?php elseif ($section === 'moto-bozze'): ?>
                <!-- Gestione Moto in Bozza -->
                <div class="dashboard-section">
                    <h1>Moto in bozza</h1>
                    <p class="section-description">Proposte di moto inserite dagli utenti — approva per aggiungere al catalogo ufficiale</p>

                    <?php
                    $bozzeInAttesa  = array_values(array_filter($motoBozze ?? [], fn($b) => $b['stato'] === 'in_attesa'));
                    $bozzeProcessed = array_values(array_filter($motoBozze ?? [], fn($b) => $b['stato'] !== 'in_attesa'));
                    ?>

                    <!-- Sezione in attesa -->
                    <h2 style="font-size:1.05rem;font-weight:600;margin:1.5rem 0 .75rem;color:var(--text-primary)">
                        In attesa di revisione
                        <?php if (count($bozzeInAttesa) > 0): ?>
                            <span style="background:#fef08a;color:#713f12;border-radius:999px;padding:.1em .55em;font-size:.8em;font-weight:700;margin-left:.4em"><?= count($bozzeInAttesa) ?></span>
                        <?php endif; ?>
                    </h2>

                    <?php if (empty($bozzeInAttesa)): ?>
                        <div class="empty-state" style="padding:2rem 0">
                            <div class="empty-icon">✅</div>
                            <h3>Nessuna proposta in attesa!</h3>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Marca</th>
                                        <th>Modello</th>
                                        <th>Ricevuta il</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bozzeInAttesa as $b): ?>
                                        <tr>
                                            <td><?= $b['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($b['marca']) ?></strong></td>
                                            <td><?= htmlspecialchars($b['modello']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($b['creato_il'])) ?></td>
                                            <td class="td-actions">
                                                <div class="td-actions-inner">
                                                    <!-- Approva -->
                                                    <form method="POST" class="inline-form" onsubmit="return confirm('Aggiungere «<?= htmlspecialchars(addslashes($b['marca'] . ' ' . $b['modello'])) ?>» al catalogo ufficiale?')">
                                                        <input type="hidden" name="action" value="approve_moto_bozza">
                                                        <input type="hidden" name="bozza_id" value="<?= $b['id'] ?>">
                                                        <button type="submit" class="btn btn-small" style="background:#16a34a;color:#fff;border:none">✓ Approva</button>
                                                    </form>
                                                    <!-- Rifiuta -->
                                                    <form method="POST" class="inline-form" onsubmit="return confirm('Rifiutare questa proposta?')">
                                                        <input type="hidden" name="action" value="reject_moto_bozza">
                                                        <input type="hidden" name="bozza_id" value="<?= $b['id'] ?>">
                                                        <button type="submit" class="btn btn-small btn-danger">✕ Rifiuta</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Storico bozze processate -->
                    <?php if (!empty($bozzeProcessed)): ?>
                        <h2 style="font-size:1.05rem;font-weight:600;margin:2rem 0 .75rem;color:var(--text-primary)">Storico</h2>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Marca</th>
                                        <th>Modello</th>
                                        <th>Stato</th>
                                        <th>Ricevuta il</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bozzeProcessed as $b): ?>
                                        <tr>
                                            <td><?= $b['id'] ?></td>
                                            <td><?= htmlspecialchars($b['marca']) ?></td>
                                            <td><?= htmlspecialchars($b['modello']) ?></td>
                                            <td>
                                                <?php if ($b['stato'] === 'approvata'): ?>
                                                    <span class="badge" style="background:#dcfce7;color:#166534">✓ Approvata</span>
                                                <?php else: ?>
                                                    <span class="badge" style="background:#fee2e2;color:#991b1b">✕ Rifiutata</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($b['creato_il'])) ?></td>
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
    <?php if ($section === 'panoramica'): ?>
        <script>
            (function() {
                'use strict';

                var POLL_INTERVAL = 30000; // 30 secondi

                function fmt(n) {
                    return new Intl.NumberFormat('it-IT', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(n);
                }

                function fmtDate(iso) {
                    var d = new Date(iso.replace(' ', 'T'));
                    return d.toLocaleDateString('it-IT') + ' ' + d.toLocaleTimeString('it-IT', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                function setText(id, val) {
                    var el = document.getElementById(id);
                    if (el) el.textContent = val;
                }

                function esc(str) {
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                }

                function renderPreventivi(preventivi) {
                    var wrap = document.getElementById('ultimi-preventivi-wrap');
                    if (!wrap) return;
                    if (!preventivi || preventivi.length === 0) {
                        wrap.innerHTML = '<p class="recent-block__empty">Nessun preventivo ancora.</p>';
                        return;
                    }
                    var html = '<div class="recent-list">';
                    preventivi.forEach(function(p) {
                        var statoLabel = p.stato.replace(/_/g, ' ');
                        statoLabel = statoLabel.charAt(0).toUpperCase() + statoLabel.slice(1);
                        html += '<div class="recent-item">' +
                            '<div class="recent-item__info">' +
                            '<span class="recent-item__title">#' + p.id + ' \u2014 ' + esc(p.cliente) + '</span>' +
                            '<span class="recent-item__date">' + fmtDate(p.creato_il) + '</span>' +
                            '</div>' +
                            '<div class="recent-item__right">' +
                            '<span class="recent-item__amount">&euro;' + fmt(p.prezzo_finale) + '</span>' +
                            '<span class="ov-status ov-status--' + esc(p.stato) + '">' + statoLabel + '</span>' +
                            '</div>' +
                            '</div>';
                    });
                    html += '</div>';
                    wrap.innerHTML = html;
                }

                function renderUtenti(utenti) {
                    var wrap = document.getElementById('ultimi-utenti-wrap');
                    if (!wrap) return;
                    if (!utenti || utenti.length === 0) {
                        wrap.innerHTML = '<p class="recent-block__empty">Nessun utente ancora.</p>';
                        return;
                    }
                    var html = '<div class="recent-list">';
                    utenti.forEach(function(u) {
                        html += '<div class="recent-item">' +
                            '<div class="recent-item__avatar">' + esc(u.username.charAt(0).toUpperCase()) + '</div>' +
                            '<div class="recent-item__info">' +
                            '<span class="recent-item__title">' + esc(u.username) + '</span>' +
                            '<span class="recent-item__date">' + esc(u.email) + '</span>' +
                            '</div>' +
                            '<div class="recent-item__right">' +
                            '<span class="ov-role ov-role--' + esc(u.ruolo) + '">' + esc(u.ruolo.charAt(0).toUpperCase() + u.ruolo.slice(1)) + '</span>' +
                            '</div>' +
                            '</div>';
                    });
                    html += '</div>';
                    wrap.innerHTML = html;
                }

                function refreshStats() {
                    fetch('/api/admin-stats.php', {
                            credentials: 'same-origin'
                        })
                        .then(function(res) {
                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            return res.json();
                        })
                        .then(function(data) {
                            var s = data.preventivi_stats;
                            setText('stat-totale-utenti', s.totale_utenti);
                            setText('stat-nuovi-oggi', '+' + s.nuovi_oggi + ' oggi');
                            setText('stat-totale-professionisti', s.totale_professionisti || 0);
                            setText('stat-totale-clienti', s.totale_clienti + ' clienti');
                            setText('stat-totale-preventivi', s.totale_preventivi);
                            setText('stat-preventivi-inviati', s.preventivi_inviati + ' inviati');
                            setText('stat-preventivi-in-lavorazione', s.preventivi_in_lavorazione + ' in lavorazione');
                            setText('stat-totale-fatturato', '\u20AC' + fmt(s.totale_fatturato));
                            setText('stat-preventivi-confermati', s.preventivi_confermati + ' confermati');
                            renderPreventivi(data.ultimi_preventivi);
                            renderUtenti(data.ultimi_utenti);
                        })
                        .catch(function() {
                            /* silenzioso — i dati restano invariati */
                        });
                }

                setInterval(refreshStats, POLL_INTERVAL);
            }());
        </script>
    <?php endif; ?>
    <?php if ($section === 'utenti' || $section === 'professionisti'): ?>
        <script>
            (function() {
                'use strict';

                var PAGE_SIZE = 10;

                /* Funzione riutilizzabile per ogni tabella */
                function initTable(cfg) {
                    var tbody = document.querySelector('#' + cfg.tableId + ' tbody');
                    var paginationNav = document.getElementById(cfg.paginationId);
                    var searchInput = document.getElementById(cfg.searchId);
                    var searchClear = document.getElementById(cfg.searchClearId);
                    var searchQuery = '';
                    var currentPage = 1;

                    if (!tbody) return;

                    /* ---- Col-picker ---- */
                    var storageKey = 'adminHiddenCols_' + cfg.tableId;
                    var tbl = document.getElementById(cfg.tableId);
                    var pickerBtn = document.getElementById(cfg.pickerBtnId);
                    var dropdown = document.getElementById(cfg.pickerDropdownId);
                    var pickerWrap = document.getElementById(cfg.pickerId);

                    function loadHidden() {
                        try {
                            return JSON.parse(localStorage.getItem(storageKey) || '[]');
                        } catch (e) {
                            return [];
                        }
                    }

                    function saveHidden(arr) {
                        try {
                            localStorage.setItem(storageKey, JSON.stringify(arr));
                        } catch (e) {}
                    }

                    function applyVisibility(hiddenCols) {
                        if (!tbl) return;
                        tbl.querySelectorAll('[data-col]').forEach(function(el) {
                            el.style.display = hiddenCols.indexOf(el.getAttribute('data-col')) !== -1 ? 'none' : '';
                        });
                    }
                    if (tbl && pickerBtn && dropdown) {
                        var hidden = loadHidden();
                        applyVisibility(hidden);
                        dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                            cb.checked = hidden.indexOf(cb.getAttribute('data-col')) === -1;
                            cb.addEventListener('change', function() {
                                var newHidden = [];
                                dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(c) {
                                    if (!c.checked) newHidden.push(c.getAttribute('data-col'));
                                });
                                saveHidden(newHidden);
                                applyVisibility(newHidden);
                            });
                        });
                        pickerBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            var wasHidden = dropdown.hidden;
                            dropdown.hidden = !wasHidden;
                            pickerBtn.setAttribute('aria-expanded', String(wasHidden));
                        });
                        document.addEventListener('click', function(e) {
                            if (pickerWrap && !pickerWrap.contains(e.target)) {
                                dropdown.hidden = true;
                                pickerBtn.setAttribute('aria-expanded', 'false');
                            }
                        });
                    }

                    /* ---- Filtra e paginazione ---- */
                    function applyFilters() {
                        var q = searchQuery.trim().toLowerCase();
                        var rows = Array.from(tbody.querySelectorAll('tr'));
                        rows.forEach(function(row) {
                            row._filtered = !q || row.textContent.toLowerCase().indexOf(q) !== -1;
                        });
                        currentPage = 1;
                        renderPage();
                    }

                    function renderPage() {
                        var rows = Array.from(tbody.querySelectorAll('tr'));
                        var visible = rows.filter(function(r) {
                            return r._filtered !== false;
                        });
                        var total = visible.length;
                        var totalPages = Math.ceil(total / PAGE_SIZE) || 1;
                        if (currentPage > totalPages) currentPage = totalPages;
                        var start = (currentPage - 1) * PAGE_SIZE;
                        var end = start + PAGE_SIZE;

                        rows.forEach(function(row) {
                            row.style.display = 'none';
                        });
                        visible.slice(start, end).forEach(function(row) {
                            row.style.display = '';
                        });
                        renderPagination(totalPages);
                    }

                    function renderPagination(totalPages) {
                        if (!paginationNav) return;
                        if (totalPages <= 1) {
                            paginationNav.innerHTML = '';
                            return;
                        }
                        var html = '';
                        if (currentPage > 1) {
                            html += '<button class="pagination__btn" data-page="' + (currentPage - 1) + '" aria-label="Pagina precedente">&#8249;</button>';
                        } else {
                            html += '<span class="pagination__btn pagination__btn--disabled" aria-disabled="true">&#8249;</span>';
                        }
                        var s = Math.max(1, currentPage - 2);
                        var e = Math.min(totalPages, currentPage + 2);
                        if (s > 1) {
                            html += '<button class="pagination__btn" data-page="1">1</button>';
                            if (s > 2) html += '<span class="pagination__ellipsis">…</span>';
                        }
                        for (var p = s; p <= e; p++) {
                            if (p === currentPage) {
                                html += '<span class="pagination__btn pagination__btn--active" aria-current="page">' + p + '</span>';
                            } else {
                                html += '<button class="pagination__btn" data-page="' + p + '">' + p + '</button>';
                            }
                        }
                        if (e < totalPages) {
                            if (e < totalPages - 1) html += '<span class="pagination__ellipsis">…</span>';
                            html += '<button class="pagination__btn" data-page="' + totalPages + '">' + totalPages + '</button>';
                        }
                        if (currentPage < totalPages) {
                            html += '<button class="pagination__btn" data-page="' + (currentPage + 1) + '" aria-label="Pagina successiva">&#8250;</button>';
                        } else {
                            html += '<span class="pagination__btn pagination__btn--disabled" aria-disabled="true">&#8250;</span>';
                        }
                        html += '<span class="pagination__info">Pagina ' + currentPage + ' di ' + totalPages + '</span>';
                        paginationNav.innerHTML = html;
                        paginationNav.querySelectorAll('button[data-page]').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                currentPage = parseInt(this.dataset.page, 10);
                                renderPage();
                            });
                        });
                    }

                    /* ---- Ricerca ---- */
                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            searchQuery = this.value;
                            if (searchClear) searchClear.style.display = searchQuery ? 'block' : 'none';
                            applyFilters();
                        });
                    }
                    if (searchClear) {
                        searchClear.addEventListener('click', function() {
                            searchQuery = '';
                            if (searchInput) {
                                searchInput.value = '';
                                searchInput.focus();
                            }
                            this.style.display = 'none';
                            applyFilters();
                        });
                    }

                    applyFilters();
                }

                initTable({
                    tableId: 'tbl-utenti',
                    paginationId: 'utentiPagination',
                    searchId: 'utentiSearch',
                    searchClearId: 'utentiSearchClear',
                    pickerId: 'colPicker-utenti',
                    pickerBtnId: 'colPickerBtn-utenti',
                    pickerDropdownId: 'colPickerDropdown-utenti'
                });
                initTable({
                    tableId: 'tbl-professionisti',
                    paginationId: 'professionistiPagination',
                    searchId: 'professionistiSearch',
                    searchClearId: 'professionistiSearchClear',
                    pickerId: 'colPicker-professionisti',
                    pickerBtnId: 'colPickerBtn-professionisti',
                    pickerDropdownId: 'colPickerDropdown-professionisti'
                });
            })();
        </script>
    <?php endif; ?>
    <?php if ($section === 'preventivi'): ?>
        <script>
            (function() {
                'use strict';

                /* ---- Dati dal PHP ---- */
                var COUNTS = <?= json_encode($calendarData ?? [], JSON_UNESCAPED_UNICODE) ?>;

                /* ---- Costanti ---- */
                var PAGE_SIZE = 10;

                /* ---- Stato ---- */
                var today = new Date();
                var viewYear = today.getFullYear();
                var viewMonth = today.getMonth(); // 0-based
                var activeDate = null; // 'YYYY-MM-DD' selezionato
                var searchQuery = '';
                var currentPage = 1;

                /* ---- Elementi DOM ---- */
                var grid = document.getElementById('calGrid');
                var label = document.getElementById('calMonthLabel');
                var btnPrev = document.getElementById('calPrev');
                var btnNext = document.getElementById('calNext');
                var btnReset = document.getElementById('calReset');
                var tbody = document.getElementById('preventiviTbody');
                var tableInfo = document.getElementById('calTableInfo');
                var paginationNav = document.getElementById('preventiviPagination');
                var searchInput = document.getElementById('preventiviSearch');
                var searchClear = document.getElementById('preventiviSearchClear');

                if (!grid) return;

                var MONTHS = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno',
                    'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'
                ];
                var DAYS = ['L', 'M', 'M', 'G', 'V', 'S', 'D'];

                /* ---- Render calendario ---- */
                function render() {
                    label.textContent = MONTHS[viewMonth] + ' ' + viewYear;
                    grid.innerHTML = '';

                    DAYS.forEach(function(d) {
                        var h = document.createElement('div');
                        h.className = 'cal-day-name';
                        h.textContent = d;
                        grid.appendChild(h);
                    });

                    var firstDay = new Date(viewYear, viewMonth, 1).getDay();
                    firstDay = firstDay === 0 ? 6 : firstDay - 1;
                    var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

                    for (var i = 0; i < firstDay; i++) {
                        var empty = document.createElement('div');
                        empty.className = 'cal-day cal-day--empty';
                        grid.appendChild(empty);
                    }

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
                                activeDate = null;
                                filterTable(null);
                            } else {
                                activeDate = clickedIso;
                                filterTable(clickedIso);
                            }
                            render();
                            btnReset.style.display = activeDate ? 'inline-flex' : 'none';
                            closeModal();
                        });

                        grid.appendChild(cell);
                    }
                }

                /* ---- Filtra + paginazione ---- */
                function applyFilters() {
                    if (!tbody) return;
                    var rows = Array.from(tbody.querySelectorAll('tr'));
                    var q = searchQuery.trim().toLowerCase();
                    rows.forEach(function(row) {
                        var matchDate = !activeDate || row.dataset.date === activeDate;
                        var matchSearch = !q || row.textContent.toLowerCase().indexOf(q) !== -1;
                        row._filtered = matchDate && matchSearch;
                    });
                    currentPage = 1;
                    renderPage();
                }

                function filterTable(isoDate) {
                    applyFilters();
                }

                function renderPage() {
                    if (!tbody) return;
                    var rows = Array.from(tbody.querySelectorAll('tr'));
                    var visible = rows.filter(function(r) {
                        return r._filtered !== false;
                    });
                    var total = visible.length;
                    var totalPages = Math.ceil(total / PAGE_SIZE) || 1;
                    if (currentPage > totalPages) currentPage = totalPages;
                    var start = (currentPage - 1) * PAGE_SIZE;
                    var end = start + PAGE_SIZE;

                    rows.forEach(function(row) {
                        row.style.display = 'none';
                    });
                    visible.slice(start, end).forEach(function(row) {
                        row.style.display = '';
                    });

                    renderPagination(totalPages);
                    updateTableInfo(total);
                }

                function renderPagination(totalPages) {
                    if (!paginationNav) return;
                    if (totalPages <= 1) {
                        paginationNav.innerHTML = '';
                        return;
                    }

                    var html = '';
                    if (currentPage > 1) {
                        html += '<button class="pagination__btn" data-page="' + (currentPage - 1) + '" aria-label="Pagina precedente">&#8249;</button>';
                    } else {
                        html += '<span class="pagination__btn pagination__btn--disabled" aria-disabled="true">&#8249;</span>';
                    }

                    var s = Math.max(1, currentPage - 2);
                    var e = Math.min(totalPages, currentPage + 2);
                    if (s > 1) {
                        html += '<button class="pagination__btn" data-page="1">1</button>';
                        if (s > 2) html += '<span class="pagination__ellipsis">…</span>';
                    }
                    for (var p = s; p <= e; p++) {
                        if (p === currentPage) {
                            html += '<span class="pagination__btn pagination__btn--active" aria-current="page">' + p + '</span>';
                        } else {
                            html += '<button class="pagination__btn" data-page="' + p + '">' + p + '</button>';
                        }
                    }
                    if (e < totalPages) {
                        if (e < totalPages - 1) html += '<span class="pagination__ellipsis">…</span>';
                        html += '<button class="pagination__btn" data-page="' + totalPages + '">' + totalPages + '</button>';
                    }
                    if (currentPage < totalPages) {
                        html += '<button class="pagination__btn" data-page="' + (currentPage + 1) + '" aria-label="Pagina successiva">&#8250;</button>';
                    } else {
                        html += '<span class="pagination__btn pagination__btn--disabled" aria-disabled="true">&#8250;</span>';
                    }
                    html += '<span class="pagination__info">Pagina ' + currentPage + ' di ' + totalPages + '</span>';

                    paginationNav.innerHTML = html;
                    paginationNav.querySelectorAll('button[data-page]').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            currentPage = parseInt(this.dataset.page, 10);
                            renderPage();
                        });
                    });
                }

                function updateTableInfo(total) {
                    if (!tableInfo) return;
                    if (activeDate) {
                        var parts = activeDate.split('-');
                        var nicDate = parts[2] + '/' + parts[1] + '/' + parts[0];
                        tableInfo.textContent = total + ' preventivo/i per il ' + nicDate;
                        tableInfo.style.display = 'block';
                    } else {
                        tableInfo.style.display = 'none';
                    }
                }

                /* ---- Navigazione calendario ---- */
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
                    applyFilters();
                    render();
                    this.style.display = 'none';
                });

                /* ---- Ricerca testuale ---- */
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        searchQuery = this.value;
                        if (searchClear) searchClear.style.display = searchQuery ? 'block' : 'none';
                        applyFilters();
                    });
                }
                if (searchClear) {
                    searchClear.addEventListener('click', function() {
                        searchQuery = '';
                        if (searchInput) {
                            searchInput.value = '';
                            searchInput.focus();
                        }
                        this.style.display = 'none';
                        applyFilters();
                    });
                }

                /* ---- Modale ---- */
                var overlay = document.getElementById('calModalOverlay');
                var modal = document.getElementById('calModal');
                var btnOpen = document.getElementById('calOpenBtn');
                var btnClose = document.getElementById('calModalClose');

                function openModal() {
                    overlay.hidden = false;
                    btnClose.focus();
                }

                function closeModal() {
                    overlay.hidden = true;
                    btnOpen.focus();
                }

                btnOpen.addEventListener('click', openModal);
                btnClose.addEventListener('click', closeModal);
                overlay.addEventListener('click', function(e) {
                    if (!modal.contains(e.target)) closeModal();
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !overlay.hidden) closeModal();
                });

                /* ---- Col-picker preventivi ---- */
                (function() {
                    var storageKey = 'adminHiddenCols_preventivi';
                    var tbl = document.getElementById('tbl-preventivi');
                    var btn = document.getElementById('colPickerBtn-preventivi');
                    var dropdown = document.getElementById('colPickerDropdown-preventivi');
                    if (!tbl || !btn || !dropdown) return;

                    function loadHidden() {
                        try {
                            return JSON.parse(localStorage.getItem(storageKey) || '[]');
                        } catch (e) {
                            return [];
                        }
                    }

                    function saveHidden(arr) {
                        try {
                            localStorage.setItem(storageKey, JSON.stringify(arr));
                        } catch (e) {}
                    }

                    function applyVisibility(hiddenCols) {
                        tbl.querySelectorAll('[data-col]').forEach(function(el) {
                            el.style.display = hiddenCols.indexOf(el.getAttribute('data-col')) !== -1 ? 'none' : '';
                        });
                    }

                    var hidden = loadHidden();
                    applyVisibility(hidden);

                    dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                        cb.checked = hidden.indexOf(cb.getAttribute('data-col')) === -1;
                        cb.addEventListener('change', function() {
                            var newHidden = [];
                            dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(c) {
                                if (!c.checked) newHidden.push(c.getAttribute('data-col'));
                            });
                            saveHidden(newHidden);
                            applyVisibility(newHidden);
                        });
                    });

                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var wasHidden = dropdown.hidden;
                        dropdown.hidden = !wasHidden;
                        btn.setAttribute('aria-expanded', String(wasHidden));
                    });
                    document.addEventListener('click', function(e) {
                        var picker = document.getElementById('colPicker-preventivi');
                        if (picker && !picker.contains(e.target)) {
                            dropdown.hidden = true;
                            btn.setAttribute('aria-expanded', 'false');
                        }
                    });
                })();

                /* ---- Init ---- */
                render();
                applyFilters(); // prima pagina con tutte le righe visibili

                // Naviga al primo mese con eventi
                var keys = Object.keys(COUNTS);
                if (keys.length > 0) {
                    keys.sort();
                    var firstIso = keys[0];
                    var p0 = firstIso.split('-');
                    viewYear = parseInt(p0[0], 10);
                    viewMonth = parseInt(p0[1], 10) - 1;
                    render();
                }
            }());
        </script>
    <?php endif; ?>
    <?php include __DIR__ . '/../../includes/whatsapp-button.php'; ?>
</body>

</html>