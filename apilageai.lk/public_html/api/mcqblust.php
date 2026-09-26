<?php
// Deprecated endpoint: return a generic response without exposing internals.
header_remove('X-Powered-By');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
http_response_code(404);
echo json_encode(['error' => 'Not found']);
exit;
