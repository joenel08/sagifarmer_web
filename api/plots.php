<?php
require_once __DIR__ . '/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM plots WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        respond(["success" => true, "plot" => $stmt->fetch()]);
    }
    $stmt = $conn->prepare("SELECT * FROM plots WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_GET['user_id'] ?? 0]);
    respond(["success" => true, "plots" => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $d = input();
    $required = ['user_id','sensor_name','sensor_type','sensor_id','latitude','longitude'];
    foreach ($required as $f) {
        if (!isset($d[$f]) || $d[$f] === '') {
            respond(["success" => false, "message" => "Missing field: $f"]);
        }
    }
    if (!in_array($d['sensor_type'], ['water_level','soil_moisture'])) {
        respond(["success" => false, "message" => "Invalid sensor type."]);
    }
    $stmt = $conn->prepare(
        "INSERT INTO plots (user_id, sensor_name, sensor_type, sensor_id, latitude, longitude)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $d['user_id'], $d['sensor_name'], $d['sensor_type'],
        $d['sensor_id'], $d['latitude'], $d['longitude']
    ]);
    respond(["success" => true, "message" => "Plot added successfully.", "id" => $conn->lastInsertId()]);
}

if ($method === 'DELETE') {
    $id = $_GET['id'] ?? input()['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM plots WHERE id = ?");
    $stmt->execute([$id]);
    respond(["success" => true, "message" => "Plot deleted."]);
}

respond(["success" => false, "message" => "Unsupported method."], 405);