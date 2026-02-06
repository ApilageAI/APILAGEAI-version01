<?php
/**
 * ApilageAI Payment Webhook (Notify)
 * 
 * Handles payment notifications from Payable gateway
 * 
 * @package ApilageAI
 */

require_once __DIR__ . /../backend/bootstrap.php'; 

// Parse input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

// Extract data with sanitization
$invoiceNo     = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['invoiceNo'] ?? '');
$tx_id         = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['payableTransactionId'] ?? '');
$order_id      = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['payableOrderId'] ?? '');
$statusCode    = (int)($data['statusCode'] ?? 0);
$statusMessage = htmlspecialchars($data['statusMessage'] ?? '', ENT_QUOTES, 'UTF-8');
$amountPaid    = floatval($data['payableAmount'] ?? 0);

// Log the webhook for debugging
error_log("Payment webhook received: invoice=$invoiceNo, status=$statusCode, amount=$amountPaid");

if (empty($invoiceNo) || $statusCode !== 1) {
    http_response_code(200);
    echo "IGNORED";
    exit;
}

// Find transaction
$stmt = $db->prepare("SELECT user_id, amount, paid FROM transactions WHERE invoice_id = ? LIMIT 1");
$stmt->bind_param("s", $invoiceNo);
$stmt->execute();
$res = $stmt->get_result();
$txn = $res->fetch_assoc();
$stmt->close();

if (!$txn) {
    error_log("Payment webhook: Unknown invoice $invoiceNo");
    http_response_code(200);
    echo "UNKNOWN_INVOICE";
    exit;
}

if ((int)$txn['paid'] === 1) {
    http_response_code(200);
    echo "ALREADY_PAID";
    exit;
}

// Verify amount matches (with small tolerance for rounding)
if (abs($txn['amount'] - $amountPaid) > 0.01) {
    error_log("Payment webhook: Amount mismatch for $invoiceNo - expected {$txn['amount']}, got $amountPaid");
    // Continue anyway but log it
}

$db->begin_transaction();
try {
    // Mark as paid
    $u1 = $db->prepare("
      UPDATE transactions
      SET paid = 1,
          updated_at = NOW(),
          payable_uid = ?,
          status_indicator = ?
      WHERE invoice_id = ?
    ");
    $u1->bind_param("sss", $tx_id, $order_id, $invoiceNo);
    $u1->execute();
    $u1->close();

    // Update user balance
    $userId = (int)$txn['user_id'];
    $u2 = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $u2->bind_param("di", $amountPaid, $userId);
    $u2->execute();
    $u2->close();

    $db->commit();
    
    error_log("Payment webhook success: invoice=$invoiceNo, user=$userId, amount=$amountPaid");

    http_response_code(200);
    echo "OK";
} catch (Exception $e) {
    $db->rollback();
    error_log("Payment webhook error: " . $e->getMessage());
    http_response_code(500);
    echo "ERROR";
}
exit;