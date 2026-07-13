<?php

require __DIR__ . '/vendor/autoload.php';

use Rats\Zkteco\Lib\ZKTeco;
use GuzzleHttp\Client;

Dotenv\Dotenv::createImmutable(__DIR__)->load();

$deviceIp = $_ENV['ZK_DEVICE_IP'];
$devicePort = (int) ($_ENV['ZK_DEVICE_PORT'] ?? 4370);
$appUrl = rtrim($_ENV['APP_URL'], '/');
$apiKey = $_ENV['ZK_AGENT_API_KEY'];

if (!$apiKey) {
    fwrite(STDERR, "ZK_AGENT_API_KEY is not set in .env\n");
    exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Connecting to ZKTeco device at {$deviceIp}:{$devicePort}...\n";

$zk = new ZKTeco($deviceIp, $devicePort);

if (!$zk->connect()) {
    fwrite(STDERR, "Failed to connect to the ZKTeco device. Check it's powered on and reachable from this machine.\n");
    exit(1);
}

$zk->disableDevice();
$records = $zk->getAttendance();
$zk->enableDevice();
$zk->disconnect();

if (empty($records)) {
    echo "No attendance records on the device.\n";
    exit(0);
}

$punches = array_map(function ($record) {
    return [
        'zk_uid' => (string) $record['id'],
        'timestamp' => $record['timestamp'],
    ];
}, $records);

echo "Read " . count($punches) . " punches from the device. Pushing to {$appUrl}...\n";

$client = new Client(['base_uri' => $appUrl . '/', 'timeout' => 30]);

try {
    $response = $client->post('api/zk/punches', [
        'headers' => ['X-Api-Key' => $apiKey],
        'json' => ['punches' => $punches],
    ]);

    echo "Sync OK: HTTP " . $response->getStatusCode() . " " . $response->getBody() . "\n";
} catch (\GuzzleHttp\Exception\RequestException $e) {
    fwrite(STDERR, "Push failed: " . $e->getMessage() . "\n");
    if ($e->hasResponse()) {
        fwrite(STDERR, (string) $e->getResponse()->getBody() . "\n");
    }
    exit(1);
}
