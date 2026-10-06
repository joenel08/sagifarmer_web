<?php
// ============================================================
// api/services/fcm_service.php
// FCM HTTP v1 client + emergency flag support.
//
// NOTE: FCM HTTP v1 does NOT support `vibration_pattern` in the
// payload — that field belonged to the old legacy API. The
// vibration pattern is defined on the Flutter side inside the
// Android notification channel (`sagipfarmer_emergency`).
// ============================================================
require_once __DIR__ . '/../config/firebase_config.php';

// ------------------------------------------------------------
// OAuth2 token (JWT signed with the service account)
// ------------------------------------------------------------
function get_google_access_token() {
    static $cached = null;
    static $expiresAt = 0;

    if ($cached && time() < $expiresAt) return $cached;

    if (!file_exists(FIREBASE_SA_PATH)) {
        throw new Exception('Service account JSON not found at ' . FIREBASE_SA_PATH);
    }

    $sa = json_decode(file_get_contents(FIREBASE_SA_PATH), true);
    if (!$sa || empty($sa['private_key']) || empty($sa['client_email'])) {
        throw new Exception('Invalid service account JSON');
    }

    $now = time();
    $header  = ['alg' => 'RS256', 'typ' => 'JWT'];
    $payload = [
        'iss'   => $sa['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'iat'   => $now,
        'exp'   => $now + 3600,
    ];

    $b64 = function ($data) {
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    };

    $signingInput = $b64($header) . '.' . $b64($payload);

    $signature = '';
    if (!openssl_sign($signingInput, $signature, $sa['private_key'], 'SHA256')) {
        throw new Exception('Failed to sign JWT');
    }

    $jwt = $signingInput . '.' .
           rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) throw new Exception("OAuth cURL error: $err");
    if ($code !== 200) throw new Exception("OAuth token exchange failed ($code): $resp");

    $data = json_decode($resp, true);
    $cached    = $data['access_token'];
    $expiresAt = $now + ((int)($data['expires_in'] ?? 3600) - 120);
    return $cached;
}

// ------------------------------------------------------------
// Send to one device
// ------------------------------------------------------------
function send_fcm_notification($deviceToken, $title, $body, $data = [], $isEmergency = false) {
    if (!FCM_ENABLED) {
        error_log("[FCM] Disabled. Would send to $deviceToken: $title");
        return ['success' => false, 'message' => 'FCM disabled'];
    }

    if (empty($deviceToken)) {
        return ['success' => false, 'message' => 'empty token'];
    }

    try {
        $accessToken = get_google_access_token();
    } catch (Exception $e) {
        error_log('[FCM] ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }

    $url = 'https://fcm.googleapis.com/v1/projects/'
         . FIREBASE_PROJECT_ID . '/messages:send';

    // Add the emergency flag to the data payload so the app can decide
    // whether to render a full-screen alert.
    $data['is_emergency'] = $isEmergency ? 'true' : 'false';

    // ⚠️ Only fields supported by HTTP v1 are included here.
    //    Notice: NO vibration_pattern (legacy only).
    $androidNotification = [
        'sound'        => 'default',
        'channel_id'   => $isEmergency
            ? 'sagipfarmer_emergency'      // ⚠️ must match Flutter
            : 'sagipfarmer_alerts',
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
    ];

    $androidConfig = [
        'priority'     => 'high',
        'notification' => $androidNotification,
    ];

    if ($isEmergency) {
        $androidConfig['ttl'] = '3600s';
    }

    $payload = [
        'message' => [
            'token'        => $deviceToken,
            'notification' => ['title' => $title, 'body' => $body],
            'data'         => array_map('strval', $data),
            'android'      => $androidConfig,
            'apns'         => [
                'headers' => [
                    'apns-priority' => $isEmergency ? '10' : '5',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'badge' => 1,
                    ],
                ],
            ],
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
    ]);

    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    error_log("[FCM] HTTP $code -> $resp");

    if ($err) return ['success' => false, 'message' => "cURL: $err"];

    return [
        'success'   => $code >= 200 && $code < 300,
        'http_code' => $code,
        'response'  => json_decode($resp, true) ?? $resp,
    ];
}

// ------------------------------------------------------------
// Send to every device of a user
// ------------------------------------------------------------
function send_fcm_to_user($conn, $userId, $title, $body, $data = [], $isEmergency = false) {
    $stmt = $conn->prepare("SELECT fcm_token FROM user_devices WHERE user_id = ?");
    $stmt->execute([$userId]);
    $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $sent = 0;
    foreach ($tokens as $token) {
        $r = send_fcm_notification($token, $title, $body, $data, $isEmergency);
        if ($r['success']) {
            $sent++;
        } elseif (($r['http_code'] ?? 0) === 404) {
            $del = $conn->prepare("DELETE FROM user_devices WHERE fcm_token = ?");
            $del->execute([$token]);
        }
    }
    return $sent;
}