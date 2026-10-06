<?php
// ============================================================
// api/admin_users.php  (GET)  -> all users + their plots
// ============================================================
require_once __DIR__ . '/config/db.php';

$users = $conn->query(
    "SELECT id, full_name, contact_number, full_address, username, created_at
     FROM users WHERE role = 'user' ORDER BY created_at DESC"
)->fetchAll();

$plotStmt = $conn->prepare("SELECT * FROM plots WHERE user_id = ?");

foreach ($users as &$u) {
    $plotStmt->execute([$u['id']]);
    $u['plots'] = $plotStmt->fetchAll();
}
unset($u);

respond(["success" => true, "users" => $users]);