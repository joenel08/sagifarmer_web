<?php
// ============================================================
// api/readings.php
//
//  GET  ?plot_id=1&limit=20   -> recent readings (app chart)
//
//  POST (batch — recommended for 2-sensor ESP32):
//      {
//        "readings": [
//          { "plot_id": 5, "type": "moisture", "value": 45 },
//          { "plot_id": 6, "type": "level",    "value": 72 }
//        ]
//      }
//
//  POST (single):
//      { "plot_id": 5, "type": "moisture", "value": 45 }
//
//  For each reading:
//    • Look up the plot by plot_id
//    • (Optional) verify type matches plots.sensor_type
//    • Save into readings (plot_id, value)
//    • Threshold check → alert → SMS + FCM
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

// ------------------------------------------------------------
// Threshold check — returns [title, body, severity] or null
//
//  WATER LEVEL (cm — distance from sensor to water surface)
//     >= 70 cm     -> LOW   (water is far / low, needs irrigation)
//     31 – 69 cm   -> OPTIMAL
//     <= 30 cm     -> HIGH  (water is close / high, flood risk)
//
//  SOIL MOISTURE (raw ADC, capacitive sensor, higher = drier)
//     >= 3001 ADC  -> DRY   (needs watering)
//     1601 – 3000  -> MID   (normal, no alert)
//     <= 1600 ADC  -> WET   (too much water)
//
//  NOTE: Raw ADC value is always saved to the DB as-is.
//        Wet/Mid/Dry labels are ONLY used to decide alerts.
// ------------------------------------------------------------
function check_threshold($type, $value, $sensorName) {
    if ($type === 'water_level') {
        if ($value >= 70) {
            return [
                "Low Water Level",
                "Water level at $sensorName is low ({$value} cm). Irrigation needed.",
                "high",
            ];
        }
        if ($value <= 30) {
            return [
                "High Water Level",
                "Water level at $sensorName is high ({$value} cm). Flood risk.",
                "high",
            ];
        }
    } else { // soil_moisture — raw ADC
        if ($value >= 3001) {          // DRY
            return [
                "Soil Too Dry",
                "Soil moisture at $sensorName reads {$value} ADC. Consider watering.",
                "medium",
            ];
        }
        if ($value <= 1600) {          // WET
            return [
                "Soil Too Wet",
                "Soil moisture at $sensorName reads {$value} ADC. Drainage may be needed.",
                "low",
            ];
        }
        // 1601 – 3000 = MID → normal, no alert
    }
    return null;
}
// ------------------------------------------------------------
// Process ONE reading by plot_id
// ------------------------------------------------------------
function process_reading($conn, $plot_id, $raw_type, $value) {
    $result = [
        'plot_id' => $plot_id,
        'success' => false,
        'message' => '',
    ];

    // ---- Validate ----
    if ($plot_id <= 0) {
        $result['message'] = 'Invalid plot_id.';
        return $result;
    }
    if ($value === null) {
        $result['message'] = 'Missing value.';
        return $result;
    }

    // ---- Sanity clamp (prevent absurd values) ----
    //   Water level   : 0 .. 500 cm
    //   Soil moisture : 0 .. 4095 ADC
    if ($value < 0)      $value = 0;
    if ($value > 100000) $value = 100000;
    $value = round($value, 2);

    // ---- Look up plot + owner ----
    $p = $conn->prepare(
        "SELECT p.id, p.sensor_name, p.sensor_type, p.sensor_id,
                u.id AS user_id, u.full_name, u.contact_number
           FROM plots p
           JOIN users u ON u.id = p.user_id
          WHERE p.id = ?
          LIMIT 1"
    );
    $p->execute([$plot_id]);
    $plot = $p->fetch();

    if (!$plot) {
        $result['message'] = "Plot ID $plot_id not found.";
        return $result;
    }

    // The plot's registered type — always trust this
    $type = $plot['sensor_type'];

    // ---- (Optional) If the ESP32 sent a type, verify it ----
    if ($raw_type !== '') {
        $sent_type = normalize_type($raw_type);
        if ($sent_type !== null && $sent_type !== $type) {
            $result['message'] = "Type mismatch. Plot '{$plot['sensor_name']}' "
                               . "is registered as '{$type}', "
                               . "but you sent '{$raw_type}'.";
            $result['registered_type'] = $type;
            $result['received_type']   = $sent_type;
            return $result;
        }
    }

    // ---- Save the reading ----
    $ins = $conn->prepare(
        "INSERT INTO readings (plot_id, value) VALUES (?, ?)"
    );
    $ins->execute([$plot_id, $value]);

    // ---- Threshold check ----
    $alert   = check_threshold($type, $value, $plot['sensor_name']);
    $smsInfo = null;
    $fcmSent = 0;
    $alertId = null;

    if ($alert) {
        // De-dupe: 5-minute window
        $chk = $conn->prepare(
            "SELECT id FROM alerts
              WHERE plot_id = ?
                AND alert_type = ?
                AND created_at > (NOW() - INTERVAL 5 MINUTE)
              LIMIT 1"
        );
        $chk->execute([$plot_id, $alert[0]]);

        if (!$chk->fetch()) {
            $a = $conn->prepare(
                "INSERT INTO alerts (plot_id, alert_type, message, severity)
                 VALUES (?, ?, ?, ?)"
            );
            $a->execute([$plot_id, $alert[0], $alert[1], $alert[2]]);
            $alertId = $conn->lastInsertId();

            // SMS
            if (!empty($plot['contact_number'])) {
                $smsSent = send_alert_sms(
                    $plot['contact_number'],
                    $alert[0], $alert[1], $alert[2]
                );
                $smsInfo = $smsSent ? 'sent' : 'failed';
            } else {
                $smsInfo = 'no contact number';
            }

            // FCM
            try {
                $isEmergency = ($alert[2] === 'high');
                $fcmSent = send_fcm_to_user(
                    $conn,
                    (int)$plot['user_id'],
                    $alert[0], $alert[1],
                    [
                        'plot_id'  => (string)$plot_id,
                        'sensor'   => $plot['sensor_name'],
                        'severity' => $alert[2],
                        'type'     => $type,
                        'alert_id' => (string)$alertId,
                    ],
                    $isEmergency
                );
            } catch (Exception $e) {
                error_log('[FCM] ' . $e->getMessage());
                $fcmSent = 0;
            }
        } else {
            $smsInfo = 'duplicate within 5 min — skipped';
        }
    }

    // ---- Success ----
    $result['success']     = true;
    $result['message']     = 'Reading saved.';
    $result['sensor_id']   = $plot['sensor_id'];
    $result['user_id']     = (int)$plot['user_id'];
    $result['sensor_name'] = $plot['sensor_name'];
    $result['type']        = $type;
    $result['value']       = $value;
    $result['alert']       = $alert ? $alert[0] : null;
    $result['severity']    = $alert ? $alert[2] : null;
    $result['alert_id']    = $alertId;
    $result['sms']         = $smsInfo;
    $result['fcm']         = $fcmSent;
    return $result;
}

// ============================================================
// GET  ->  readings for the app chart
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $plot_id = isset($_GET['plot_id']) ? (int)$_GET['plot_id'] : 0;
    $limit   = isset($_GET['limit'])   ? (int)$_GET['limit']   : 20;

    if ($plot_id <= 0) {
        respond([
            "success" => false,
            "message" => "Valid plot_id is required.",
        ]);
    }

    if ($limit < 1)   $limit = 1;
    if ($limit > 500) $limit = 500;

    $stmt = $conn->prepare(
        "SELECT id, plot_id, value, reading_time
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
        "plot_id"  => $plot_id,
        "count"    => count($rows),
        "latest"   => $latest,
        "readings" => $rows,
    ]);
}

// ============================================================
// POST  ->  ESP32 pushes reading(s)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $d = input();

    // ------------------------------------------------------------
    // Detect batch vs. single payload
    // ------------------------------------------------------------
    if (isset($d['readings']) && is_array($d['readings'])) {
        $batch = $d['readings'];
    } elseif (isset($d['plot_id']) && isset($d['value'])) {
        $batch = [$d];
    } else {
        respond([
            "success" => false,
            "message" => "Expected 'readings' array or a single { plot_id, value }.",
        ]);
    }

    if (count($batch) === 0) {
        respond(["success" => false, "message" => "No readings in payload."]);
    }

    if (count($batch) > 20) {
        respond([
            "success" => false,
            "message" => "Too many readings (max 20 per request).",
        ]);
    }

    // ------------------------------------------------------------
    // Process each reading
    // ------------------------------------------------------------
    $results = [];
    $okCount = 0;

    foreach ($batch as $item) {
        $plot_id  = isset($item['plot_id']) ? (int)$item['plot_id'] : 0;
        $raw_type = trim((string)($item['type'] ?? ''));
        $value    = isset($item['value']) ? (float)$item['value'] : null;

        $r = process_reading($conn, $plot_id, $raw_type, $value);
        if (!empty($r['success'])) $okCount++;
        $results[] = $r;
    }

    // ------------------------------------------------------------
    // Summary response
    // ------------------------------------------------------------
    respond([
        "success" => $okCount > 0,
        "message" => "Processed {$okCount} of " . count($batch) . " reading(s).",
        "count"   => count($batch),
        "ok"      => $okCount,
        "results" => $results,
    ]);
}

respond(["success" => false, "message" => "Unsupported method."], 405);