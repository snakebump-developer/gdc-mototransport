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
        } elseif ($_POST['action'] === 'update_discount') {
            $sconto = (float)str_replace(',', '.', $_POST['sconto'] ?? '0');
            updateProfessionalDiscount((int)$_POST['user_id'], $sconto);
            $success = "Sconto aggiornato!";
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
    $orderStats = getOrderStats();
    $userStats = getUserStats();
    $stats = array_merge($orderStats, $userStats);
    $ultimi_ordini = getAllOrders(5);
    $ultimi_utenti = getAllUsers(5);
} elseif ($section === 'preventivi') {
    $preventivi = getAllPreventivi();
    $calendarData = getPreventiviCountByDate();
} elseif ($section === 'utenti') {
    $searchQuery = trim($_GET['q'] ?? '');
    $currentPage = max(1, (int)($_GET['page'] ?? 1));
    $totalItems  = countAllUsers($searchQuery);
    $totalPages  = max(1, (int)ceil($totalItems / $ITEMS_PER_PAGE));
    $currentPage = min($currentPage, $totalPages);
    $offset      = ($currentPage - 1) * $ITEMS_PER_PAGE;
    $utenti      = getAllUsers($ITEMS_PER_PAGE, $offset, $searchQuery);
} elseif ($section === 'professionisti') {
    $searchQuery    = trim($_GET['q'] ?? '');
    $currentPage    = max(1, (int)($_GET['page'] ?? 1));
    $totalItems     = countAllProfessionals($searchQuery);
    $totalPages     = max(1, (int)ceil($totalItems / $ITEMS_PER_PAGE));
    $currentPage    = min($currentPage, $totalPages);
    $offset         = ($currentPage - 1) * $ITEMS_PER_PAGE;
    $professionisti = getAllProfessionals($ITEMS_PER_PAGE, $offset, $searchQuery);
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

                    <!-- Search toolbar + col picker -->
                    <div class="table-controls">
                        <form method="GET" action="/admin/utenti" class="search-toolbar">
                            <div class="search-toolbar__input-wrap">
                                <svg class="search-toolbar__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8" />
                                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                </svg>
                                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cerca per username, email, nome…" class="search-toolbar__input" autocomplete="off">
                                <?php if ($searchQuery !== ''): ?>
                                    <a href="/admin/utenti" class="search-toolbar__clear" title="Cancella ricerca">&#x2715;</a>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Cerca</button>
                        </form>

                        <!-- Column picker -->
                        <div class="col-picker" id="colPicker-utenti">
                            <button type="button" class="btn btn-ghost btn-sm col-picker__toggle" id="colPickerBtn-utenti" aria-expanded="false" aria-haspopup="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="18" rx="1" />
                                    <rect x="14" y="3" width="7" height="18" rx="1" />
                                </svg>
                                Colonne
                            </button>
                            <div class="col-picker__dropdown" id="colPickerDropdown-utenti" hidden>
                                <p class="col-picker__label">Mostra / nascondi colonne</p>
                                <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="id"> ID</label>
                                <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="email"> Email</label>
                                <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="nome"> Nome</label>
                                <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="cognome"> Cognome</label>
                                <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="ruolo"> Ruolo</label>
                                <label class="col-picker__item"><input type="checkbox" data-table="tbl-utenti" data-col="registrato"> Registrato</label>
                            </div>
                        </div>
                    </div>

                    <?php if ($totalItems > 0): ?>
                        <p class="search-result-count">
                            <?= $totalItems ?> <?= $totalItems === 1 ? 'utente trovato' : 'utenti trovati' ?>
                            <?= $searchQuery !== '' ? ' per <strong>' . htmlspecialchars($searchQuery) . '</strong>' : '' ?>
                        </p>
                    <?php endif; ?>

                    <?php if (empty($utenti)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">👥</div>
                            <h3><?= $searchQuery !== '' ? 'Nessun utente trovato per "' . htmlspecialchars($searchQuery) . '"' : 'Nessun utente presente' ?></h3>
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

                        <!-- Paginazione -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="pagination" aria-label="Paginazione utenti">
                                <?php
                                $baseUrl = '/admin/utenti' . ($searchQuery !== '' ? '?q=' . urlencode($searchQuery) . '&' : '?');
                                ?>
                                <?php if ($currentPage > 1): ?>
                                    <a href="<?= $baseUrl ?>page=<?= $currentPage - 1 ?>" class="pagination__btn" aria-label="Pagina precedente">&#8249;</a>
                                <?php else: ?>
                                    <span class="pagination__btn pagination__btn--disabled" aria-disabled="true">&#8249;</span>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $currentPage - 2);
                                $end   = min($totalPages, $currentPage + 2);
                                if ($start > 1): ?>
                                    <a href="<?= $baseUrl ?>page=1" class="pagination__btn">1</a>
                                    <?php if ($start > 2): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
                                <?php endif; ?>

                                <?php for ($p = $start; $p <= $end; $p++): ?>
                                    <?php if ($p === $currentPage): ?>
                                        <span class="pagination__btn pagination__btn--active" aria-current="page"><?= $p ?></span>
                                    <?php else: ?>
                                        <a href="<?= $baseUrl ?>page=<?= $p ?>" class="pagination__btn"><?= $p ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end < $totalPages): ?>
                                    <?php if ($end < $totalPages - 1): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
                                    <a href="<?= $baseUrl ?>page=<?= $totalPages ?>" class="pagination__btn"><?= $totalPages ?></a>
                                <?php endif; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="<?= $baseUrl ?>page=<?= $currentPage + 1 ?>" class="pagination__btn" aria-label="Pagina successiva">&#8250;</a>
                                <?php else: ?>
                                    <span class="pagination__btn pagination__btn--disabled" aria-disabled="true">&#8250;</span>
                                <?php endif; ?>

                                <span class="pagination__info">Pagina <?= $currentPage ?> di <?= $totalPages ?></span>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php elseif ($section === 'professionisti'): ?>
                <!-- Gestione Professionisti -->
                <div class="dashboard-section">
                    <h1>Professionisti</h1>
                    <p class="section-description">Aziende e professionisti registrati</p>

                    <!-- Search toolbar + col picker -->
                    <div class="table-controls">
                        <form method="GET" action="/admin/professionisti" class="search-toolbar">
                            <div class="search-toolbar__input-wrap">
                                <svg class="search-toolbar__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8" />
                                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                </svg>
                                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Cerca per ragione sociale, email, P.IVA, città…" class="search-toolbar__input" autocomplete="off">
                                <?php if ($searchQuery !== ''): ?>
                                    <a href="/admin/professionisti" class="search-toolbar__clear" title="Cancella ricerca">&#x2715;</a>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Cerca</button>
                        </form>

                        <!-- Column picker -->
                        <div class="col-picker" id="colPicker-professionisti">
                            <button type="button" class="btn btn-ghost btn-sm col-picker__toggle" id="colPickerBtn-professionisti" aria-expanded="false" aria-haspopup="true">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="18" rx="1" />
                                    <rect x="14" y="3" width="7" height="18" rx="1" />
                                </svg>
                                Colonne
                            </button>
                            <div class="col-picker__dropdown" id="colPickerDropdown-professionisti" hidden>
                                <p class="col-picker__label">Mostra / nascondi colonne</p>
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

                    <?php if ($totalItems > 0): ?>
                        <p class="search-result-count">
                            <?= $totalItems ?> <?= $totalItems === 1 ? 'professionista trovato' : 'professionisti trovati' ?>
                            <?= $searchQuery !== '' ? ' per <strong>' . htmlspecialchars($searchQuery) . '</strong>' : '' ?>
                        </p>
                    <?php endif; ?>

                    <?php if (empty($professionisti)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🏢</div>
                            <h3><?= $searchQuery !== '' ? 'Nessun professionista trovato per "' . htmlspecialchars($searchQuery) . '"' : 'Nessun professionista registrato' ?></h3>
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

                        <!-- Paginazione -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="pagination" aria-label="Paginazione professionisti">
                                <?php
                                $baseUrl = '/admin/professionisti' . ($searchQuery !== '' ? '?q=' . urlencode($searchQuery) . '&' : '?');
                                ?>
                                <?php if ($currentPage > 1): ?>
                                    <a href="<?= $baseUrl ?>page=<?= $currentPage - 1 ?>" class="pagination__btn" aria-label="Pagina precedente">&#8249;</a>
                                <?php else: ?>
                                    <span class="pagination__btn pagination__btn--disabled" aria-disabled="true">&#8249;</span>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $currentPage - 2);
                                $end   = min($totalPages, $currentPage + 2);
                                if ($start > 1): ?>
                                    <a href="<?= $baseUrl ?>page=1" class="pagination__btn">1</a>
                                    <?php if ($start > 2): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
                                <?php endif; ?>

                                <?php for ($p = $start; $p <= $end; $p++): ?>
                                    <?php if ($p === $currentPage): ?>
                                        <span class="pagination__btn pagination__btn--active" aria-current="page"><?= $p ?></span>
                                    <?php else: ?>
                                        <a href="<?= $baseUrl ?>page=<?= $p ?>" class="pagination__btn"><?= $p ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end < $totalPages): ?>
                                    <?php if ($end < $totalPages - 1): ?><span class="pagination__ellipsis">…</span><?php endif; ?>
                                    <a href="<?= $baseUrl ?>page=<?= $totalPages ?>" class="pagination__btn"><?= $totalPages ?></a>
                                <?php endif; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="<?= $baseUrl ?>page=<?= $currentPage + 1 ?>" class="pagination__btn" aria-label="Pagina successiva">&#8250;</a>
                                <?php else: ?>
                                    <span class="pagination__btn pagination__btn--disabled" aria-disabled="true">&#8250;</span>
                                <?php endif; ?>

                                <span class="pagination__info">Pagina <?= $currentPage ?> di <?= $totalPages ?></span>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
        </main>
    </div>

    <script src="/js/modules/nav.js"></script>
    <script src="/js/modules/forms.js"></script>
    <?php if ($section === 'utenti' || $section === 'professionisti'): ?>
        <script>
            (function() {
                'use strict';

                var PICKERS = {
                    'tbl-utenti': {
                        pickerId: 'colPicker-utenti',
                        btnId: 'colPickerBtn-utenti',
                        dropdownId: 'colPickerDropdown-utenti',
                        storageKey: 'adminHiddenCols_utenti'
                    },
                    'tbl-professionisti': {
                        pickerId: 'colPicker-professionisti',
                        btnId: 'colPickerBtn-professionisti',
                        dropdownId: 'colPickerDropdown-professionisti',
                        storageKey: 'adminHiddenCols_professionisti'
                    }
                };

                function loadHidden(key) {
                    try {
                        return JSON.parse(localStorage.getItem(key) || '[]');
                    } catch (e) {
                        return [];
                    }
                }

                function saveHidden(key, arr) {
                    try {
                        localStorage.setItem(key, JSON.stringify(arr));
                    } catch (e) {}
                }

                function applyVisibility(tableId, hiddenCols) {
                    var tbl = document.getElementById(tableId);
                    if (!tbl) return;
                    tbl.querySelectorAll('[data-col]').forEach(function(el) {
                        el.style.display = hiddenCols.indexOf(el.getAttribute('data-col')) !== -1 ? 'none' : '';
                    });
                }

                function initPicker(tableId, cfg) {
                    var tbl = document.getElementById(tableId);
                    var btn = document.getElementById(cfg.btnId);
                    var dropdown = document.getElementById(cfg.dropdownId);
                    if (!tbl || !btn || !dropdown) return;

                    var hidden = loadHidden(cfg.storageKey);
                    applyVisibility(tableId, hidden);

                    // Sync checkboxes with saved state
                    dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
                        cb.checked = hidden.indexOf(cb.getAttribute('data-col')) === -1;
                        cb.addEventListener('change', function() {
                            var newHidden = [];
                            dropdown.querySelectorAll('input[type="checkbox"]').forEach(function(c) {
                                if (!c.checked) newHidden.push(c.getAttribute('data-col'));
                            });
                            saveHidden(cfg.storageKey, newHidden);
                            applyVisibility(tableId, newHidden);
                        });
                    });

                    // Toggle dropdown
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var wasHidden = dropdown.hidden;
                        dropdown.hidden = !wasHidden;
                        btn.setAttribute('aria-expanded', String(wasHidden));
                    });

                    // Close on outside click
                    document.addEventListener('click', function(e) {
                        var picker = document.getElementById(cfg.pickerId);
                        if (picker && !picker.contains(e.target)) {
                            dropdown.hidden = true;
                            btn.setAttribute('aria-expanded', 'false');
                        }
                    });
                }

                Object.keys(PICKERS).forEach(function(tableId) {
                    initPicker(tableId, PICKERS[tableId]);
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