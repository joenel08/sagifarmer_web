<?php
// ============================================================
// api/config/firebase_config.php
// ============================================================

// Path to the service account JSON you just downloaded
define('FIREBASE_SA_PATH', __DIR__ . '/firebase-service-account.json');

// ⚠️ PASTE YOUR PROJECT ID HERE (from the JSON file: "project_id")
define('FIREBASE_PROJECT_ID', 'sagip-farmer');

// Master switch — set to false during dev to avoid sending pushes
define('FCM_ENABLED', true);