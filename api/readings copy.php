<?php
// ============================================================
// api/readings.php
//
//  GET  ?plot_id=1&limit=20     -> recent readings (app chart)
//  POST { id, type, value }     -> ESP32/GSM pushes a reading
//
//  On threshold breach:
//    • INSERT into alerts
//    • Send SMS to plot owner
//    • Send FCM push to all of the owner's devices
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/sms_service.php';
require_once __DIR__ . '/services/fcm_service.php';

// ------------------------------------------------------------
// Normalize a type string to the DB enum value
// ------------------------------------------------------------
function normalize_type($t) {
    $t = strtolower(trim((string)$t));
    if (in_array($t, ['level', 'water', 'water_level', 'waterlevel'])) {
        return 'water_level';
    }
    if (in_array($t, ['moisture', 'soil', 'soil_moisture', 'soilmoisture'])) {
        return 'soil_moisture';
    }
    return null;
}

// ============================================================
// GET  ->  return readings for the app
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $plot_id = $_GET['plot_id'] ?? 0;
    $limit   = (int)($_GET['limit'] ?? 20);
    if ($limit < 1)   $limit = 1;
    if ($limit > 500) $limit = 500;

    $stmt = $conn->prepare(
        "SELECT value, reading_time
           FROM readings
          WHERE plot_id = ?
          ORDER BY reading_time DESC
          LIMIT $limit"
    );
    $stmt->execute([$plot_id]);
    $rows = array_reverse($stmt->fetchAll());

    $latest = count($rows) ? end($rows)['value'] : null;

    respond([
        "success"  => true,
        "readings" => $rows,
        "latest"   => $latest,
    ]);
}

// ============================================================
// POST  ->  ESP32 pushes a new reading
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $d = input();

    // Accept id / plot_id / sensor_id
    $plot_id  = $d['id'] ?? $d['plot_id'] ?? $d['sensor_id'] ?? 0;
    $plot_id  = (int)$plot_id;
    $raw_type = $d['type'] ?? '';
    $value    = isset($d['value']) ? (float)$d['value'] : null;

    if ($plot_id <= 0 || $value === null || $raw_type === '') {
        respond([
            "success" => false,
            "message" => "Fields required: id, type, value",
        ]);
    }

    $type = normalize_type($raw_type);
    if ($type === null) {
        respond([
            "success" => false,
            "message" => "Invalid type. Use 'level' or 'moisture'.",
        ]);
    }

    // Clamp to 0..100
    if ($value < 0)   $value = 0;
    if ($value > 100) $value = 100;
    $value = round($value, 2);

    // ------------------------------------------------------------
    // Fetch plot + owner (join to get contact number for SMS)
    // ------------------------------------------------------------
    $p = $conn->prepare(
        "SELECT p.id, p.sensor_name, p.sensor_type,
                u.id AS user_id, u.full_name, u.contact_number
           FROM plots p
           JOIN users u ON u.id = p.user_id
          WHERE p.id = ?
          LIMIT 1"
    );
    $p->execute([$plot_id]);
    $plot = $p->fetch();

    if (!$plot) {
        respond([
            "success" => false,
            "message" => "Plot ID $plot_id not found.",
        ]);
    }

    // Type must match what the plot was registered as
    if ($plot['sensor_type'] !== $type) {
        respond([
            "success" => false,
            "message" => "Type mismatch. Plot '{$plot['sensor_name']}' is "
                       . "registered as '{$plot['sensor_type']}', "
                       . "but you sent '$raw_type'.",
        ]);
    }

    // ------------------------------------------------------------
    // Save the reading
    // ------------------------------------------------------------
    $ins = $conn->prepare(
        "INSERT INTO readings (plot_id, value) VALUES (?, ?)"
    );
    $ins->execute([$plot_id, $value]);

    // ------------------------------------------------------------
    // Threshold check -> alert + SMS + FCM
    // ------------------------------------------------------------
    $alert = null;

    if ($type === 'water_level') {
        if ($value < 50) {
            $alert = [
                "Low Water Level",
                "Water level at {$plot['sensor_name']} dropped to {$value}%. Irrigation needed.",
                "high",
            ];
        } elseif ($value > 85) {
            $alert = [
                "High Water Level",
                "Water level at {$plot['sensor_name']} reached {$value}%. Risk of flooding.",
                "high",
            ];
        }
    } else { // soil_moisture
        if ($value < 60) {
            $alert = [
                "Soil Too Dry",
                "Soil moisture at {$plot['sensor_name']} is {$value}%. Consider watering.",
                "medium",
            ];
        } elseif ($value > 80) {
            $alert = [
                "Soil Too Wet",
                "Soil moisture at {$plot['sensor_name']} is {$value}%. Drainage may be needed.",
                "low",
            ];
        }
    }

    $smsInfo  = null;
    $fcmSent  = 0;
    $alertId  = null;

    if ($alert) {
        // -------- 5-minute de-dupe window --------
        $chk = $conn->prepare(
            "SELECT id FROM alerts
              WHERE plot_id = ?
                AND alert_type = ?
                AND created_at > (NOW() - INTERVAL 5 MINUTE)
              LIMIT 1"
        );
        $chk->execute([$plot_id, $alert[0]]);

        if (!$chk->fetch()) {
            // ============================================================
            // FRESH ALERT -> insert row, then notify (SMS + FCM)
            // ============================================================
            $a = $conn->prepare(
                "INSERT INTO alerts (plot_id, alert_type, message, severity)
                 VALUES (?, ?, ?, ?)"
            );
            $a->execute([$plot_id, $alert[0], $alert[1], $alert[2]]);
            $alertId = $conn->lastInsertId();

            // -------- SMS --------
            if (!empty($plot['contact_number'])) {
                $smsSent = send_alert_sms(
                    $plot['contact_number'],
                    $alert[0],
                    $alert[1],
                    $alert[2]
                );
                $smsInfo = $smsSent ? 'sent' : 'failed/skipped';
            } else {
                error_log("[SMS] No contact number for plot {$plot_id}");
                $smsInfo = 'no contact number';
            }

            // -------- FCM PUSH --------
            try {
                $isEmergency = ($alert[2] === 'high');
                $fcmSent = send_fcm_to_user(
                    $conn,
                    (int)$plot['user_id'],
                    $alert[0],                             // title
                    $alert[1],                             // body
                    [                                      // data payload
                        'plot_id'  => (string)$plot_id,
                        'sensor'   => $plot['sensor_name'],
                        'severity' => $alert[2],
                        'type'     => $type,
                        'alert_id' => (string)$alertId,
                    ],
                     $isEmergency 
                );
            } catch (Exception $e) {
                error_log('[FCM] send error: ' . $e->getMessage());
                $fcmSent = 0;
            }
        } else {
            // De-duped: no SMS, no FCM
            $smsInfo = 'duplicate within 5 min — skipped';
            $fcmSent = 0;
        }
    }

    // ------------------------------------------------------------
    // Response
    // ------------------------------------------------------------
    respond([
        "success"   => true,
        "message"   => "Reading saved.",
        "plot_id"   => $plot_id,
        "type"      => $type,
        "value"     => $value,
        "alert"     => $alert ? $alert[0] : null,
        "severity"  => $alert ? $alert[2] : null,
        "alert_id"  => $alertId,
        "sms"       => $smsInfo,
        "fcm"       => $fcmSent,      // number of devices that received the push
    ]);
}

respond(["success" => false, "message" => "Unsupported method."], 405);