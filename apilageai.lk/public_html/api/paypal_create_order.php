<?php
/**
 * ApilageAI PayPal Order Create (Sandbox)
 */

define('APILAGE_EXPECTS_JSON', true);
require_once __DIR__ . '/../../backend/bootstrap.php';

ini_set('display_errors', 0);
error_reporting(0);

$responseSent = false;
register_shutdown_function(function () use (&$responseSent) {
    if ($responseSent) {
        return;
    }
    $error = error_get_last();
    if (!$error) {
        return;
    }
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error. Please try again.'
    ]);
});

header('Content-Type: application/json');

function apilage_fetch_lkr_usd_rate(): ?float {
    $endpoints = [
        'https://open.er-api.com/v6/latest/LKR',
        'https://api.exchangerate.host/latest?base=LKR&symbols=USD'
    ];

    foreach ($endpoints as $endpoint) {
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300 || !$response) {
            continue;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            continue;
        }

        if (isset($data['rates']['USD'])) {
            $rate = (float)$data['rates']['USD'];
            if ($rate > 0) {
                return $rate;
            }
        }

        if (isset($data['result']) && isset($data['info']['rate'])) {
            $rate = (float)$data['info']['rate'];
            if ($rate > 0) {
                return $rate;
            }
        }
    }

    return null;
}

if (!$user->_logged_in) {
    $responseSent = true;
    http_response_code(401);
    returnJSON(['success' => false, 'message' => 'Authentication required']);
}

$rawPayload = file_get_contents('php://input');
if (!empty($rawPayload)) {
    $jsonPayload = json_decode($rawPayload, true);
    if (is_array($jsonPayload)) {
        $_POST = array_merge($_POST, $jsonPayload);
    }
}
if (function_exists('apilage_sanitize_array_input')) {
    $_POST = apilage_sanitize_array_input($_POST);
}

$invoiceId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_POST['invoice_id'] ?? ''));
if ($invoiceId === '') {
    $responseSent = true;
    http_response_code(400);
    returnJSON(['success' => false, 'message' => 'Invoice ID required']);
}

$userId = (int)($user->_data['id'] ?? 0);
$stmt = $db->prepare("SELECT invoice_id, amount, paid, payable_uid FROM transactions WHERE invoice_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param('si', $invoiceId, $userId);
$stmt->execute();
$txn = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$txn) {
    $responseSent = true;
    http_response_code(404);
    returnJSON(['success' => false, 'message' => 'Invoice not found']);
}

if ((int)$txn['paid'] === 1) {
    $responseSent = true;
    http_response_code(409);
    returnJSON(['success' => false, 'message' => 'Invoice already paid']);
}

$lkrAmount = (float)($txn['amount'] ?? 0);
if ($lkrAmount <= 500) {
    $responseSent = true;
    http_response_code(400);
    returnJSON(['success' => false, 'message' => 'PayPal is available only for amounts above Rs. 500']);
}

$rate = apilage_fetch_lkr_usd_rate();
if (!$rate || $rate <= 0) {
    $rate = defined('PAYPAL_LKR_TO_USD_RATE') ? (float)PAYPAL_LKR_TO_USD_RATE : 0.0032;
}
$usdAmount = round($lkrAmount * $rate, 2);
if ($usdAmount <= 0) {
    $responseSent = true;
    http_response_code(400);
    returnJSON(['success' => false, 'message' => 'Invalid USD amount']);
}

$usdFormatted = number_format($usdAmount, 2, '.', '');

$baseUrl = (defined('PAYPAL_SANDBOX') && PAYPAL_SANDBOX)
    ? 'https://api-m.sandbox.paypal.com'
    : 'https://api-m.paypal.com';

$ch = curl_init($baseUrl . '/v1/oauth2/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => PAYPAL_CLIENT_ID . ':' . PAYPAL_SECRET,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Accept-Language: en_US'
    ],
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
    CURLOPT_TIMEOUT => 20
]);
$tokenResponse = curl_exec($ch);
$tokenHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$tokenError = curl_error($ch);
curl_close($ch);

if ($tokenHttp !== 200 || !$tokenResponse) {
    $responseSent = true;
    http_response_code(502);
    returnJSON(['success' => false, 'message' => 'Unable to connect to PayPal', 'error' => $tokenError]);
}

$tokenData = json_decode($tokenResponse, true);
$accessToken = $tokenData['access_token'] ?? '';
if ($accessToken === '') {
    $responseSent = true;
    http_response_code(502);
    returnJSON(['success' => false, 'message' => 'Failed to authorize PayPal']);
}

$orderPayload = [
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'reference_id' => $invoiceId,
        'custom_id' => $invoiceId,
        'amount' => [
            'currency_code' => 'USD',
            'value' => $usdFormatted
        ],
        'description' => 'ApilageAI credit top-up'
    ]],
    'application_context' => [
        'brand_name' => 'ApilageAI',
        'user_action' => 'PAY_NOW',
        'shipping_preference' => 'NO_SHIPPING'
    ]
];

$ch = curl_init($baseUrl . '/v2/checkout/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ],
    CURLOPT_POSTFIELDS => json_encode($orderPayload),
    CURLOPT_TIMEOUT => 20
]);
$orderResponse = curl_exec($ch);
$orderHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$orderError = curl_error($ch);
curl_close($ch);

if ($orderHttp < 200 || $orderHttp >= 300 || !$orderResponse) {
    $responseSent = true;
    http_response_code(502);
    returnJSON(['success' => false, 'message' => 'PayPal order failed', 'error' => $orderError]);
}

$orderData = json_decode($orderResponse, true);
$orderId = $orderData['id'] ?? '';
if ($orderId === '') {
    $responseSent = true;
    http_response_code(502);
    returnJSON(['success' => false, 'message' => 'PayPal order ID missing']);
}

$updateStmt = $db->prepare("UPDATE transactions SET payable_uid = ? WHERE invoice_id = ? AND user_id = ? AND paid = 0");
$updateStmt->bind_param('ssi', $orderId, $invoiceId, $userId);
$updateStmt->execute();
$updateStmt->close();

$responseSent = true;
returnJSON([
    'success' => true,
    'order_id' => $orderId,
    'usd_amount' => $usdFormatted
]);
