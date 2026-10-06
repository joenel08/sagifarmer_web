<?php
// ============================================================
// api/admin_sensors.php  (GET) -> every sensor with owner info (for map)
// ============================================================
require_once __DIR__ . '/config/db.php';

$sql = "SELECT p.*, u.full_name, u.contact_number,
               (SELECT value FROM readings r WHERE r.plot_id = p.id
                ORDER BY r.reading_time DESC LIMIT 1) AS latest_value,
               (SELECT reading_time FROM readings r WHERE r.plot_id = p.id
                ORDER BY r.reading_time DESC LIMIT 1) AS last_update
        FROM plots p
        JOIN users u ON u.id = p.user_id
        ORDER BY p.created_at DESC";

respond(["success" => true, "sensors" => $conn->query($sql)->fetchAll()]);