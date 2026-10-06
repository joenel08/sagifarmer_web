<?php
// ============================================================
// api/login.php   (POST)   { username, password }
// ============================================================
require_once __DIR__ . '/config/db.php';

$d = input();
$username = trim($d['username'] ?? '');
$password = $d['password'] ?? '';

if ($username === '' || $password === '') {
    respond(["success" => false, "message" => "Username and password required."]);
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    respond(["success" => false, "message" => "Invalid username or password."]);
}

unset($user['password']);
respond(["success" => true, "message" => "Login successful.", "user" => $user]);