<?php
// ============================================================
// api/config/sms_config.php
// SMS provider settings for SAGIPfarmer.
// ============================================================

// Master ON/OFF switch.
// Set to false during development to avoid wasting SMS credits.
// define('SMS_ENABLED', true);

// // Your SMS provider endpoint (from the docs you provided)
// define('SMS_API_URL', 'https://smsapiph.onrender.com/api/v1/send/sms');

// // Your API key
// define('SMS_API_KEY', 'sk-2b10x23qvfga7za5fxmfz4g59a8fzjsx');

// // Minimum severity that triggers an SMS.
// //    'low'    = send ALL alerts (driest setting — may spam)
// //    'medium' = skip "low" alerts (RECOMMENDED default)
// //    'high'   = only critical alerts
// define('SMS_MIN_SEVERITY', 'medium');

// // Prefix shown at the start of every message (helps farmers recognize it)
// define('SMS_APP_TAG', 'SAGIPfarmer');

// // How long to wait for the SMS API before giving up (seconds)
// define('SMS_TIMEOUT', 15);

// // If true, sends are logged to the sms_logs table (see optional schema below)
// define('SMS_LOG_TO_DB', false);


// ============================================================
// api/config/sms_config.php
// SMS provider settings for SAGIPfarmer (PinAS SMS).
// ============================================================

// Master ON/OFF switch.
// Set to false during development to avoid wasting SMS credits.
define('SMS_ENABLED', true);

// PinAS SMS API endpoint
define('SMS_API_URL', 'https://pinassms.com/api/sms/send');

// Your PinAS SMS API key
define('SMS_API_KEY', 'Bc2H9fBWKzMAoK9JVnBEeAT9qgPkdN5iBIPWzymP');

// Minimum severity that triggers an SMS.
//    'low'    = send ALL alerts
//    'medium' = skip 'low' alerts (RECOMMENDED)
//    'high'   = only critical alerts
define('SMS_MIN_SEVERITY', 'medium');

// Prefix shown at the start of every message
define('SMS_APP_TAG', 'SAGIPfarmer');

// cURL timeout (seconds)
define('SMS_TIMEOUT', 15);

// If true, logs SMS sends to the `sms_logs` table (optional)
define('SMS_LOG_TO_DB', false);