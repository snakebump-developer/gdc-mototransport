<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';
require_once __DIR__ . '/../src/users.php';

requireAdmin();

$user = getCurrentUser();
$section = $_GET['section'] ?? 'overview';
$success = '';
$error = '';

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
} elseif ($section === 'orders') {
    $ordini = getAllOrders();
} elseif ($section === 'users') {
    $utenti = getAllUsers();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Starter Kit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php">
                    <h2>StarterKit</h2>
                </a>
            </div>
            <div class="nav-auth">
                <div class="user-dropdown">
                    <button class="user-button" id="userButton">
                        <?= htmlspecialchars($user['username']) ?> (Admin)
                        <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                            <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="dashboard.php">Dashboard Utente</a>
                        <hr>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Sidebar -->
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

        <!-- Main Content -->
        <main class="dashboard-main">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($section === 'overview'): ?>
                <!-- Panoramica -->
                <div class="dashboard-section">
                    <h1>Panoramica</h1>
                    <p class="section-description">Statistiche generali del sito</p>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-content">
                                <h3><?= $stats['totale_utenti'] ?></h3>
                                <p>Utenti Totali</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-content">
                                <h3><?= $stats['totale_ordini'] ?></h3>
                                <p>Ordini Totali</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-content">
                                <h3>€<?= number_format($stats['totale_vendite'], 2, ',', '.') ?></h3>
                                <p>Vendite Totali</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">⏳</div>
                            <div class="stat-content">
                                <h3><?= $stats['ordini_pending'] ?></h3>
                                <p>Ordini in Attesa</p>
                            </div>
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
                                            <td>€<?= number_format($ordine['totale'], 2, ',', '.') ?></td>
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

    <script src="js/main.js"></script>
</body>
</html>