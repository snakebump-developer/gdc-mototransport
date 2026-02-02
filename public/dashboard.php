<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';

requireLogin();

$user = getCurrentUser();
$section = $_GET['section'] ?? 'profile';
$success = '';
$error = '';

// Gestione aggiornamento profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $section === 'profile') {
    try {
        updateUserProfile($user['id'], $_POST);
        $success = "Profilo aggiornato con successo!";
        $user = getCurrentUser(); // Ricarica i dati
    } catch (Exception $e) {
        $error = "Errore nell'aggiornamento: " . $e->getMessage();
    }
}

// Carica ordini se necessario
$ordini = [];
if ($section === 'orders') {
    $ordini = getUserOrders($user['id']);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Starter Kit</title>
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
                        <?= htmlspecialchars($user['username']) ?>
                        <svg width="12" height="8" viewBox="0 0 12 8" fill="none">
                            <path d="M1 1L6 6L11 1" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="dashboard.php">Il Mio Profilo</a>
                        <a href="dashboard.php?section=orders">I Miei Ordini</a>
                        <?php if (isAdmin()): ?>
                            <hr>
                            <a href="admin.php">Pannello Admin</a>
                        <?php endif; ?>
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

        <!-- Main Content -->
        <main class="dashboard-main">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($section === 'profile'): ?>
                <!-- Sezione Profilo -->
                <div class="dashboard-section">
                    <h1>Il Mio Profilo</h1>
                    <p class="section-description">Gestisci le tue informazioni personali</p>

                    <form method="POST" class="profile-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                <small>Username non modificabile</small>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                <small>Email non modificabile</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="cognome">Cognome</label>
                                <input type="text" id="cognome" name="cognome" value="<?= htmlspecialchars($user['cognome'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Telefono</label>
                                <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="indirizzo">Indirizzo</label>
                                <input type="text" id="indirizzo" name="indirizzo" value="<?= htmlspecialchars($user['indirizzo'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="citta">Città</label>
                                <input type="text" id="citta" name="citta" value="<?= htmlspecialchars($user['citta'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="cap">CAP</label>
                                <input type="text" id="cap" name="cap" value="<?= htmlspecialchars($user['cap'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="paese">Paese</label>
                                <input type="text" id="paese" name="paese" value="<?= htmlspecialchars($user['paese'] ?? '') ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                    </form>
                </div>

            <?php elseif ($section === 'orders'): ?>
                <!-- Sezione Ordini -->
                <div class="dashboard-section">
                    <h1>I Miei Ordini</h1>
                    <p class="section-description">Visualizza lo storico dei tuoi ordini</p>

                    <?php if (empty($ordini)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📦</div>
                            <h3>Nessun ordine trovato</h3>
                            <p>Non hai ancora effettuato ordini.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID Ordine</th>
                                        <th>Data</th>
                                        <th>Totale</th>
                                        <th>Stato</th>
                                        <th>Metodo Pagamento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ordini as $ordine): ?>
                                        <tr>
                                            <td>#<?= $ordine['id'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($ordine['creato_il'])) ?></td>
                                            <td>€<?= number_format($ordine['totale'], 2, ',', '.') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $ordine['stato'] ?>">
                                                    <?= ucfirst($ordine['stato']) ?>
                                                </span>
                                            </td>
                                            <td><?= ucfirst($ordine['metodo_pagamento'] ?? 'N/A') ?></td>
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