<?php
// ============================================================
// api/test_sms.php
// Quick one-off test — DELETE AFTER USE.
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/sms_service.php';

header('Content-Type: application/json');

// Change this to your own number for testing
$testNumber  = '09450484087';
$testMessage = '[' . SMS_APP_TAG . '] Notifications about water level and soil moisture.' .
               '🚨 Emergency Alert. Check your farm immediately!';

$result = send_sms($testNumber, $testMessage);

echo json_encode([
    'success'  => $result['success'],
    'to'       => normalize_phone($testNumber),
    'message'  => $testMessage,
    'response' => $result,
], JSON_PRETTY_PRINT);