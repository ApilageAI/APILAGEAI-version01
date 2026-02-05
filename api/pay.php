<?php
/**
 * ApilageAI Payment Callback API
 * 
 * PAYABLE Callback + Webhook Handler
 * - Verifies payment status
 * - Updates DB (transactions + user balance)
 * - Redirects user to app
 * 
 * @package ApilageAI
 */

require_once __DIR__ . '/../../backend/bootstrap.php';

// Configuration from config.php
$sandbox = PAYABLE_SANDBOX;
$statusUrl = $sandbox
    ? 'https://payable-ipg-payment.web.app/ipg/sandbox/status'
    : 'https://payable-ipg-payment.web.app/ipg/production/status';

// Input validation
$uidParam = $_GET['uid'] ?? '';
$resultIndicator = $_GET['resultIndicator'] ?? '';

if (empty($uidParam) || empty($resultIndicator)) {
    error_log("Payment callback: Missing params - uid=$uidParam, resultIndicator=$resultIndicator");
    header("Location: " . APP_URL . "/app?status=failed&reason=missing_params");
    exit;
}

// Sanitize inputs
$uidParam = preg_replace('/[^a-zA-Z0-9_-]/', '', $uidParam);
$resultIndicator = preg_replace('/[^a-zA-Z0-9_-]/', '', $resultIndicator);

// Verify with PAYABLE
$url = $statusUrl . "?uid=" . urlencode($uidParam) . "&resultIndicator=" . urlencode($resultIndicator);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => true
]);
$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

if ($http !== 200) {
    error_log("PAYABLE STATUS FAIL: HTTP=$http, Error=$curlError, Response=$response");
    header("Location: " . APP_URL . "/app?status=failed&reason=verification_failed");
    exit;
}

$data = json_decode($response, true);
$status = strtoupper($data['data']['statusMessage'] ?? '');
$invoiceId = $data['data']['invoiceNo'] ?? null;
$amount = floatval($data['data']['payableAmount'] ?? 0);

if ($status !== 'SUCCESS' || empty($invoiceId)) {
    error_log("PAYMENT NOT SUCCESS: status=$status, invoiceId=$invoiceId, response=$response");
    header("Location: " . APP_URL . "/app?status=failed&reason=payment_not_success");
    exit;
}

// Sanitize invoice ID
$invoiceId = preg_replace('/[^a-zA-Z0-9_-]/', '', $invoiceId);

// Update database with transaction
try {
    $db->begin_transaction();

    // Find transaction
    $stmt = $db->prepare("SELECT user_id, paid, amount FROM transactions WHERE invoice_id = ? LIMIT 1");
    $stmt->bind_param("s", $invoiceId);
    $stmt->execute();
    $tx = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tx) {
        throw new Exception("Transaction not found: $invoiceId");
    }

    // Verify amount matches
    if (abs($tx['amount'] - $amount) > 0.01) {
        error_log("PAYMENT AMOUNT MISMATCH: expected={$tx['amount']}, received=$amount, invoice=$invoiceId");
        // Continue anyway but log it
    }

    if ((int)$tx['paid'] === 0) {
        // Mark as paid
        $stmt = $db->prepare("UPDATE transactions SET paid = 1, updated_at = NOW(), payable_uid = ?, status_indicator = ? WHERE invoice_id = ?");
        $stmt->bind_param("sss", $uidParam, $resultIndicator, $invoiceId);
        $stmt->execute();
        $stmt->close();

        // Update user balance
        $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $tx['user_id']);
        $stmt->execute();
        $stmt->close();

        error_log("PAYMENT SUCCESS: invoice=$invoiceId, amount=$amount, user={$tx['user_id']}");
    } else {
        error_log("PAYMENT ALREADY PROCESSED: invoice=$invoiceId");
    }

    $db->commit();
    header("Location: " . APP_URL . "/app?status=success");
    exit;

} catch (Exception $e) {
    $db->rollback();
    error_log("PAYMENT DB ERROR: " . $e->getMessage() . ", invoice=$invoiceId");
    header("Location: " . APP_URL . "/app?status=failed&reason=db_error");
    exit;
}
