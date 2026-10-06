<?php
// ============================================================
// api/register.php   (POST)
// ============================================================
require_once __DIR__ . '/config/db.php';

$d = input();
$full_name      = trim($d['full_name'] ?? '');
$contact_number = trim($d['contact_number'] ?? '');
$full_address   = trim($d['full_address'] ?? '');
$username       = trim($d['username'] ?? '');
$password       = $d['password'] ?? '';

if ($full_name === '' || $contact_number === '' || $full_address === '' ||
    $username === '' || $password === '') {
    respond(["success" => false, "message" => "All fields are required."]);
}

$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->execute([$username]);
if ($check->fetch()) {
    respond(["success" => false, "message" => "Username already taken."]);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO users (full_name, contact_number, full_address, username, password, role)
     VALUES (?, ?, ?, ?, ?, 'user')"
);
$stmt->execute([$full_name, $contact_number, $full_address, $username, $hash]);

respond(["success" => true, "message" => "Registration successful.", "user_id" => $conn->lastInsertId()]);