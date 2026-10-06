<?php
// ============================================================
// api/clear_token.php  (POST)
// { "user_id": 1, "fcm_token": "..." }  -- fcm_token optional
// ============================================================
require_once __DIR__ . '/config/db.php';

$d = input();
$user_id   = (int)($d['user_id'] ?? 0);
$fcm_token = trim($d['fcm_token'] ?? '');

if ($user_id <= 0) {
    respond(["success" => false, "message" => "user_id required."]);
}

if ($fcm_token !== '') {
    $stmt = $conn->prepare(
        "DELETE FROM user_devices WHERE user_id = ? AND fcm_token = ?"
    );
    $stmt->execute([$user_id, $fcm_token]);
} else {
    $stmt = $conn->prepare("DELETE FROM user_devices WHERE user_id = ?");
    $stmt->execute([$user_id]);
}

respond(["success" => true, "message" => "Token(s) cleared."]);