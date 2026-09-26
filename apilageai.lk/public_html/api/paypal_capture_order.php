<?php
/**
 * ApilageAI PayPal Order Capture (Sandbox)
 */

define('APILAGE_EXPECTS_JSON', true);
require_once __DIR__ . '/../../backend/bootstrap.php';

header('Content-Type: application/json');

if (!$user->_logged_in) {
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
$orderId = preg_replace('/[^A-Za-z0-9-]/', '', (string)($_POST['order_id'] ?? ''));
if ($invoiceId === '' || $orderId === '') {
    http_response_code(400);
    returnJSON(['success' => false, 'message' => 'Invoice ID and Order ID required']);
}

$userId = (int)($user->_data['id'] ?? 0);
$stmt = $db->prepare("SELECT invoice_id, amount, paid, payable_uid FROM transactions WHERE invoice_id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param('si', $invoiceId, $userId);
$stmt->execute();
$txn = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$txn) {
    http_response_code(404);
    returnJSON(['success' => false, 'message' => 'Invoice not found']);
}

if (!empty($txn['payable_uid']) && $txn['payable_uid'] !== $orderId) {
    http_response_code(409);
    returnJSON(['success' => false, 'message' => 'Order ID mismatch']);
}

if ((int)$txn['paid'] === 1) {
    returnJSON(['success' => true, 'message' => 'Already paid']);
}

$lkrAmount = (float)($txn['amount'] ?? 0);
if ($lkrAmount <= 0) {
    http_response_code(400);
    returnJSON(['success' => false, 'message' => 'Invalid invoice amount']);
}

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
    http_response_code(502);
    returnJSON(['success' => false, 'message' => 'Unable to connect to PayPal', 'error' => $tokenError]);
}

$tokenData = json_decode($tokenResponse, true);
$accessToken = $tokenData['access_token'] ?? '';
if ($accessToken === '') {
    http_response_code(502);
    returnJSON(['success' => false, 'message' => 'Failed to authorize PayPal']);
}

$ch = curl_init($baseUrl . '/v2/checkout/orders/' . $orderId . '/capture');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ],
    CURLOPT_TIMEOUT => 20
]);
$captureResponse = curl_exec($ch);
$captureHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$captureError = curl_error($ch);
curl_close($ch);

if ($captureHttp < 200 || $captureHttp >= 300 || !$captureResponse) {
    http_response_code(502);
    returnJSON(['success' => false, 'message' => 'PayPal capture failed', 'error' => $captureError]);
}

$captureData = json_decode($captureResponse, true);
$orderStatus = strtoupper((string)($captureData['status'] ?? ''));
$captureStatus = strtoupper((string)($captureData['purchase_units'][0]['payments']['captures'][0]['status'] ?? ''));

if ($orderStatus !== 'COMPLETED' && $captureStatus !== 'COMPLETED') {
    http_response_code(400);
    returnJSON(['success' => false, 'message' => 'Payment not completed']);
}

$db->begin_transaction();
try {
    $update = $db->prepare("UPDATE transactions SET paid = 1, updated_at = NOW(), payable_uid = ?, status_indicator = ? WHERE invoice_id = ? AND user_id = ? AND paid = 0");
    $statusIndicator = 'PAYPAL';
    $update->bind_param('sssi', $orderId, $statusIndicator, $invoiceId, $userId);
    $update->execute();
    $updated = $update->affected_rows > 0;
    $update->close();

    if ($updated) {
        $u2 = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $u2->bind_param('di', $lkrAmount, $userId);
        $u2->execute();
        $u2->close();
    }

    $db->commit();

    if ($updated) {
        send_payment_receipt($db, $invoiceId, $lkrAmount);
    }

    returnJSON(['success' => true, 'message' => $updated ? 'Payment captured' : 'Already processed']);
} catch (Exception $e) {
    $db->rollback();
    http_response_code(500);
    returnJSON(['success' => false, 'message' => 'Database error']);
}
