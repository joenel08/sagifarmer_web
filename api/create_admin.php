<?php
// ============================================================
// api/create_admin.php  — run ONCE in browser, then DELETE the file.
// Creates/resets the admin account.
// ============================================================
require_once __DIR__ . '/config/db.php';

$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$conn->prepare("DELETE FROM users WHERE username = 'admin'")->execute();

$stmt = $conn->prepare(
    "INSERT INTO users (full_name, contact_number, full_address, username, password, role)
     VALUES ('System Administrator', '09171234567', 'SAGIPfarmer HQ', ?, ?, 'admin')"
);
$stmt->execute([$username, $hash]);

echo json_encode(["success" => true, "message" => "Admin created. username=admin password=admin123 — DELETE THIS FILE NOW."]);