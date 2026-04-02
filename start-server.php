<?php

/**
 * Script per avviare il server PHP su una porta disponibile
 * Cerca automaticamente una porta libera a partire da 8000
 */

$host = 'localhost';
$startPort = 8000;
$maxPort = 8100;
$publicDir = __DIR__ . '/public';

// Verifica che la directory public esista
if (!is_dir($publicDir)) {
    echo "❌ Errore: Directory 'public' non trovata!\n";
    echo "   Percorso: $publicDir\n";
    exit(1);
}

/**
 * Controlla se una porta è disponibile
 */
function isPortAvailable($host, $port)
{
    $connection = @fsockopen($host, $port, $errno, $errstr, 1);
    if (is_resource($connection)) {
        fclose($connection);
        return false; // Porta occupata
    }
    return true; // Porta disponibile
}

/**
 * Trova la prima porta disponibile
 */
function findAvailablePort($host, $startPort, $maxPort)
{
    for ($port = $startPort; $port <= $maxPort; $port++) {
        if (isPortAvailable($host, $port)) {
            return $port;
        }
    }
    return null;
}

echo "\n";
echo "🔍 Ricerca porta disponibile...\n";

$port = findAvailablePort($host, $startPort, $maxPort);

if ($port === null) {
    echo "❌ Errore: Nessuna porta disponibile tra $startPort e $maxPort\n";
    exit(1);
}

$url = "http://{$host}:{$port}";

echo "✅ Porta disponibile trovata: $port\n";
echo "\n";
echo "🚀 Avvio server PHP...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "   Server:  $url\n";
echo "   Porta:   $port\n";
echo "   Root:    $publicDir\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
echo "💡 Apri il browser su: $url\n";
echo "⚠️  Premi CTRL+C per fermare il server\n";
echo "\n";

// Avvia il server PHP
$routerFile = __DIR__ . '/public/router.php';
$command = sprintf(
    'php -S %s:%d -t %s %s',
    $host,
    $port,
    escapeshellarg($publicDir),
    escapeshellarg($routerFile)
);

// Esegui il comando
passthru($command, $returnCode);

exit($returnCode);
