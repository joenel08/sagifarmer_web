<?php
// ============================================================
// api/save_token.php  (POST)
// { "user_id": 1, "fcm_token": "abc..." }
// ============================================================
require_once __DIR__ . '/config/db.php';

$d = input();
$user_id   = (int)($d['user_id'] ?? 0);
$fcm_token = trim($d['fcm_token'] ?? '');

if ($user_id <= 0 || $fcm_token === '') {
    respond(["success" => false, "message" => "user_id and fcm_token required."]);
}

$stmt = $conn->prepare(
    "INSERT INTO user_devices (user_id, fcm_token, created_at)
     VALUES (?, ?, NOW())
     ON DUPLICATE KEY UPDATE created_at = NOW()"
);
$stmt->execute([$user_id, $fcm_token]);

respond(["success" => true, "message" => "Token saved."]);