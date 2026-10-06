<?php
// ============================================================
// api/services/sms_service.php
// Wraps the PinAS SMS HTTP call + phone number normalization.
// ============================================================
require_once __DIR__ . '/../config/sms_config.php';

/**
 * Normalize a Philippine phone number into the format PinAS
 * expects: 63XXXXXXXXXX  (no +, no leading 0)
 *
 * Handles:
 *   09171234567    -> 639171234567
 *   +639171234567  -> 639171234567
 *   639171234567   -> 639171234567
 *   9171234567     -> 639171234567
 */
function normalize_phone($number) {
    // Strip everything except digits
    $clean = preg_replace('/\D/', '', (string)$number);

    if ($clean === '') return null;

    // 09171234567  (11 digits, leading 0)
    if (strlen($clean) === 11 && $clean[0] === '0') {
        return '63' . substr($clean, 1);
    }

    // 639171234567 (12 digits, leading 63)
    if (strlen($clean) === 12 && substr($clean, 0, 2) === '63') {
        return $clean;
    }

    // 9171234567 (10 digits)
    if (strlen($clean) === 10) {
        return '63' . $clean;
    }

    // Already has country code but different length — return as-is
    return $clean;
}

/**
 * Rank severities so we can compare against SMS_MIN_SEVERITY.
 */
function severity_rank($s) {
    switch (strtolower((string)$s)) {
        case 'high':   return 3;
        case 'medium': return 2;
        case 'low':    return 1;
        default:       return 0;
    }
}

/**
 * Send an SMS via PinAS SMS.
 *
 * Returns:
 *   [
 *     'success'   => bool,
 *     'http_code' => int,
 *     'response'  => array|string,
 *     'message'   => string,
 *   ]
 */
function send_sms($recipient, $message) {
    if (!SMS_ENABLED) {
        error_log("[SMS] Disabled. Would send to $recipient: $message");
        return ['success' => false, 'message' => 'SMS disabled in config'];
    }

    $to = normalize_phone($recipient);
    if (!$to) {
        error_log("[SMS] Invalid recipient: " . var_export($recipient, true));
        return ['success' => false, 'message' => 'Invalid recipient'];
    }

    // PinAS SMS payload
    $payload = json_encode([
        'recipient' => $to,
        'message'   => $message,
    ]);

    $ch = curl_init(SMS_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => SMS_TIMEOUT,
        CURLOPT_HTTPHEADER     => [
            // ⚠️ PinAS uses X-API-KEY (capital X, dashes)
            'X-API-KEY: ' . SMS_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS     => $payload,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        error_log("[SMS] cURL error to $to: $curlErr");
        return ['success' => false, 'message' => "cURL: $curlErr"];
    }

    $decoded = json_decode($response, true);

    // Optional audit logging
    if (SMS_LOG_TO_DB) {
        sms_log_to_db($to, $message, $httpCode, $response);
    }

    error_log("[SMS] HTTP $httpCode to $to -> $response");

    // PinAS returns 200/201 on success. Also check for a
    // "success": true or "status": "success" in the body when present.
    $looksSuccessful = $httpCode >= 200 && $httpCode < 300;
    if (is_array($decoded)) {
        if (isset($decoded['success']) && $decoded['success'] === false) {
            $looksSuccessful = false;
        }
        if (isset($decoded['status']) &&
            strtolower((string)$decoded['status']) === 'error') {
            $looksSuccessful = false;
        }
    }

    return [
        'success'   => $looksSuccessful,
        'http_code' => $httpCode,
        'response'  => $decoded !== null ? $decoded : $response,
    ];
}

/**
 * Optional: insert a row into sms_logs.
 * Only used if SMS_LOG_TO_DB = true.
 */
function sms_log_to_db($to, $message, $httpCode, $response) {
    global $conn;
    if (!isset($conn)) return;
    try {
        $stmt = $conn->prepare(
            "INSERT INTO sms_logs (recipient, message, http_code, response)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$to, $message, $httpCode, (string)$response]);
    } catch (Exception $e) {
        error_log("[SMS] Log error: " . $e->getMessage());
    }
}

/**
 * Convenience — decides whether to send based on severity and
 * formats the message body nicely.
 */
function send_alert_sms($contactNumber, $alertType, $alertMessage, $severity) {
    if (severity_rank($severity) < severity_rank(SMS_MIN_SEVERITY)) {
        error_log("[SMS] Skipping (severity $severity below threshold)");
        return false;
    }

    $prefix = strtoupper($severity) === 'HIGH' ? '🚨 ' : '';
    $body =
        "[" . SMS_APP_TAG . "] " . $prefix . $alertType . "\n" .
        $alertMessage . "\n" .
        "Time: " . date('M d, h:i A');

    $result = send_sms($contactNumber, $body);
    return $result['success'] === true;
}