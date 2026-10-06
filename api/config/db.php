<?php
// ============================================================
// api/config/db.php
// ============================================================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

$DB_HOST = "localhost";
$DB_NAME = "sagipfarmer";
$DB_USER = "root";
$DB_PASS = "";               // Laragon default = ""

try {
    $conn = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $e->getMessage()]);
    exit();
}

/** Accept both JSON body and form-encoded POST */
function input() {
    $raw = file_get_contents("php://input");
    $json = json_decode($raw, true);
    if (is_array($json)) return $json;
    return $_POST;
}

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}