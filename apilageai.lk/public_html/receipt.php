<?php
require_once __DIR__ . '/../backend/bootstrap.php';

if (!$user->_logged_in) {
    header("Location: " . APP_URL . "/auth/login");
    exit();
}

header("X-Robots-Tag: noindex, nofollow, noarchive", true);
$smarty->assign('noindex', true);

$invoiceId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_GET['invoice'] ?? ''));
if ($invoiceId === '') {
    http_response_code(404);
    echo 'Receipt not found';
    exit();
}

$userId = (int)($user->_data['id'] ?? 0);
$stmt = $db->prepare(
    "SELECT invoice_id, amount, paid, created_at, updated_at, status_indicator
     FROM transactions
     WHERE invoice_id = ? AND user_id = ?
     LIMIT 1"
);
$stmt->bind_param('si', $invoiceId, $userId);
$stmt->execute();
$receiptRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$receiptRow) {
    http_response_code(404);
    echo 'Receipt not found';
    exit();
}

$amountValue = (float)($receiptRow['amount'] ?? 0);
$amountFormatted = number_format($amountValue, 2);
$paidAtRaw = $receiptRow['updated_at'] ?? $receiptRow['created_at'] ?? null;
$timestamp = $paidAtRaw ? strtotime($paidAtRaw) : time();
$paymentDate = date('M d, Y', $timestamp);
$paymentTime = date('H:i', $timestamp);
$paidStatus = (int)($receiptRow['paid'] ?? 0) === 1;
$statusLabel = $paidStatus ? 'PAID' : 'PENDING';

$displayName = trim((string)($user->_data['first_name'] ?? '') . ' ' . (string)($user->_data['last_name'] ?? ''));
if ($displayName === '') {
    $displayName = 'ApilageAI User';
}

page_header(
    'Payment Receipt | ApilageAI',
    'View your ApilageAI payment receipt and download a PDF copy.'
);

$smarty->assign('receipt_invoice_id', $invoiceId);
$smarty->assign('receipt_amount', $amountFormatted);
$smarty->assign('receipt_date', $paymentDate);
$smarty->assign('receipt_time', $paymentTime);
$smarty->assign('receipt_status', $statusLabel);
$smarty->assign('receipt_user_name', $displayName);
$smarty->assign('receipt_user_email', (string)($user->_data['email'] ?? ''));
$smarty->assign('receipt_credit_link', APP_URL . '/how_apilageai_credit_works');
$smarty->assign('receipt_paid', $paidStatus);

page_footer('receipt');
?>
