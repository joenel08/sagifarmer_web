<?php
// ============================================================
// api/test_fcm.php  — DELETE AFTER TESTING
// Opens in browser to send a test push to the first saved device.
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/fcm_service.php';

header('Content-Type: application/json');

// Grab any saved device token
$row = $conn->query("SELECT user_id, fcm_token FROM user_devices LIMIT 1")
            ->fetch();

if (!$row) {
    echo json_encode([
        'success' => false,
        'message' => 'No device tokens in DB. Log into the Flutter app first.',
    ], JSON_PRETTY_PRINT);
    exit();
}

$result = send_fcm_notification(
    $row['fcm_token'],
    '🚨 SAGIPfarmer Test',
    'If you see this, FCM push is working perfectly!',
    ['test' => 'true']
);

echo json_encode([
    'token_owner_user_id' => $row['user_id'],
    'token_prefix'        => substr($row['fcm_token'], 0, 20) . '…',
    'result'              => $result,
], JSON_PRETTY_PRINT);