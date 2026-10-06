<?php
// ============================================================
// api/alerts.php
//   GET  ?user_id=1       -> alerts of all plots of that user
//   GET  ?all=1           -> all alerts (admin)
//   POST ?id=5&read=1     -> mark alert as read
// ============================================================
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['all'])) {
    $sql = "SELECT a.*,
                   p.sensor_name, p.sensor_type,
                   u.full_name AS user_name,
                   u.full_address AS user_address,
                   u.contact_number AS user_contact
            FROM alerts a
            JOIN plots p ON p.id = a.plot_id
            JOIN users u ON u.id = p.user_id
            ORDER BY a.created_at DESC
            LIMIT 200";
    respond(["success" => true, "alerts" => $conn->query($sql)->fetchAll()]);
}

    $stmt = $conn->prepare(
        "SELECT a.*, p.sensor_name, p.sensor_type
         FROM alerts a JOIN plots p ON p.id = a.plot_id
         WHERE p.user_id = ? ORDER BY a.created_at DESC LIMIT 100"
    );
    $stmt->execute([$_GET['user_id'] ?? 0]);
    respond(["success" => true, "alerts" => $stmt->fetchAll()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'] ?? input()['id'] ?? 0;
    $stmt = $conn->prepare("UPDATE alerts SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    respond(["success" => true, "message" => "Alert marked as read."]);
}

respond(["success" => false, "message" => "Unsupported method."], 405);