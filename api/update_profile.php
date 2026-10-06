<?php
// ============================================================
// api/update_profile.php  (POST)
//
// Payload:
//   {
//     "user_id": 2,
//     "full_name": "Juan Dela Cruz",
//     "contact_number": "09171234567",
//     "full_address": "Purok 1, Brgy. Example, Bacolod City"
//   }
//
// Updates the farmer's personal details.
// Username and password are NOT changed here.
// ============================================================
require_once __DIR__ . '/config/db.php';

$d = input();

$user_id        = (int)($d['user_id'] ?? 0);
$full_name      = trim((string)($d['full_name'] ?? ''));
$contact_number = trim((string)($d['contact_number'] ?? ''));
$full_address   = trim((string)($d['full_address'] ?? ''));

// ---- Validate ----
if ($user_id <= 0) {
    respond(["success" => false, "message" => "user_id is required."]);
}

if ($full_name === '') {
    respond(["success" => false, "message" => "Full name is required."]);
}

if ($contact_number === '' || strlen($contact_number) < 10) {
    respond(["success" => false, "message" => "Valid contact number is required."]);
}

if ($full_address === '') {
    respond(["success" => false, "message" => "Full address is required."]);
}

// ---- Confirm the user exists ----
$chk = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
$chk->execute([$user_id]);
if (!$chk->fetch()) {
    respond(["success" => false, "message" => "User not found."]);
}

// ---- Update ----
$stmt = $conn->prepare(
    "UPDATE users
        SET full_name = ?,
            contact_number = ?,
            full_address = ?
      WHERE id = ?"
);
$stmt->execute([$full_name, $contact_number, $full_address, $user_id]);

// ---- Return the updated user (without password) ----
$sel = $conn->prepare(
    "SELECT id, full_name, contact_number, full_address, username, role
       FROM users
      WHERE id = ?
      LIMIT 1"
);
$sel->execute([$user_id]);
$user = $sel->fetch();

respond([
    "success" => true,
    "message" => "Profile updated successfully.",
    "user"    => $user,
]);