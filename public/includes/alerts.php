<?php

/**
 * Componente per messaggi di successo/errore.
 *
 * Variabili attese:
 *   $success — Messaggio di successo (stringa vuota se nessuno)
 *   $error   — Messaggio di errore (stringa vuota se nessuno)
 */
$success = $success ?? '';
$error   = $error   ?? '';
?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>