<?php
require __DIR__ . '/../vendor/autoload.php';

use OnePay\OnePayGuzzle;
use OnePay\ApiHandler;
use Dotenv\Dotenv;

// load .env if exists
$envPath = dirname(__DIR__);
if (file_exists($envPath.'/.env')) {
    Dotenv::createImmutable($envPath)->load();
}

$token = getenv('ONEPAY_TOKEN') ?: '';
$sandbox = getenv('ONEPAY_SANDBOX') !== '0';

if (empty($token)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error'=>'ONEPAY_TOKEN not set in environment or .env']);
    exit;
}

$client = new OnePayGuzzle($token, $sandbox);
$api = new ApiHandler($client);

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

$uri = explode('?', $uri, 2)[0];
$base = dirname($scriptName);
$path = substr($uri, strlen($base));
$path = trim($path, '/');

$parts = $path === '' ? [] : explode('/', $path);

// GET /{gateway}/accountinfo
if ($method === 'GET' && count($parts) === 2 && $parts[1] === 'accountinfo') {
    $gateway = $parts[0];
    $api->handleAccountInfo($gateway);
}

// POST /createorder
if ($method === 'POST' && $path === 'createorder') {
    $api->handleCreateOrder();
}

// POST /checkorder
if ($method === 'POST' && $path === 'checkorder') {
    $api->handleCheckOrder();
}

// GET /{gateway}/invoice/list/{payerEmail}
if ($method === 'GET' && count($parts) === 4 && $parts[1] === 'invoice' && $parts[2] === 'list') {
    $gateway = $parts[0];
    $payerEmail = $parts[3];
    $api->handleInvoiceList($gateway, $payerEmail);
}

// fallback
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['error'=>'endpoint_not_found']);
exit;
