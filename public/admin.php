<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';
require_once __DIR__ . '/../src/users.php';

requireAdmin();

$user = getCurrentUser();
$section = $_GET['section'] ?? 'overview';
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

if ($section === 'overview') {
    $orderStats = getOrderStats();
    $userStats = getUserStats();
    $stats = array_merge($orderStats, $userStats);
    $ultimi_ordini = getAllOrders(5);
    $ultimi_utenti = getAllUsers(5);
} elseif ($section === 'orders') {
    $ordini = getAllOrders();
} elseif ($section === 'users') {
    $utenti = getAllUsers();
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

            <?php if ($section === 'overview'): ?>
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
                                <a href="admin.php?section=orders" class="recent-block__link">Vedi tutti →</a>
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
                                <a href="admin.php?section=users" class="recent-block__link">Vedi tutti →</a>
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

            <?php elseif ($section === 'orders'): ?>
                <!-- Gestione Ordini -->
                <div class="dashboard-section">
                    <h1>Tutti gli Ordini</h1>
                    <p class="section-description">Gestisci tutti gli ordini del sistema</p>

                    <?php if (empty($ordini)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📦</div>
                            <h3>Nessun ordine presente</h3>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utente</th>
                                        <th>Email</th>
                                        <th>Data</th>
                                        <th>Totale</th>
                                        <th>Stato</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ordini as $ordine): ?>
                                        <tr>
                                            <td>#<?= $ordine['id'] ?></td>
                                            <td><?= htmlspecialchars($ordine['username']) ?></td>
                                            <td><?= htmlspecialchars($ordine['email']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($ordine['creato_il'])) ?></td>
                                            <td>&euro;<?= number_format($ordine['totale'], 2, ',', '.') ?></td>
                                            <td>
                                                <form method="POST" class="inline-form">
                                                    <input type="hidden" name="action" value="update_order_status">
                                                    <input type="hidden" name="order_id" value="<?= $ordine['id'] ?>">
                                                    <select name="status" onchange="this.form.submit()" class="status-select">
                                                        <option value="pending" <?= $ordine['stato'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                        <option value="processing" <?= $ordine['stato'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                                        <option value="completed" <?= $ordine['stato'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                        <option value="cancelled" <?= $ordine['stato'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <button onclick="viewOrderDetails(<?= $ordine['id'] ?>)" class="btn btn-small">Dettagli</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($section === 'users'): ?>
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
                                            <td><?= htmlspecialchars($u['username']) ?></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td><?= htmlspecialchars($u['nome'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($u['cognome'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $u['ruolo'] ?>">
                                                    <?= ucfirst($u['ruolo']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($u['creato_il'])) ?></td>
                                            <td>
                                                <?php if ($u['id'] != $user['id']): ?>
                                                    <form method="POST" class="inline-form" onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?')">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                        <button type="submit" class="btn btn-small btn-danger">Elimina</button>
                                                    </form>
                                                <?php endif; ?>
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

    <script src="js/modules/nav.js"></script>
    <script src="js/modules/forms.js"></script>
</body>

</html>