<?php

// Include configuration, common functions, and feature modules
require_once('config.php');
require_once('db_connection.php');
require_once('strings.php');
require_once('common.php');
require_once('modules/feature_request.php');  // Handle prayer request
require_once('modules/show_balance.php');     // Handle balance check
require_once('modules/refill_balance.php');   // Handle balance refill
require_once('modules/start_session.php');    // Handle starting session

// Assign $pdo to $db to maintain compatibility with modules using $db
global $pdo;
$db = $pdo;

// Now $db can be used in all modules as a global variable
global $db;

// Localization
$strings = new Strings();
global $strings;

if (!$db) {
    error_log("Database connection failed");
    exit;
}

// Get webhook data from VK
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (isset($update['object']['message'])) {
    // It's a message event
    $message = $update['object']['message'];
    $chat_id = $message['from_id'];
    $text = $message['text'];

    // If the message contains "/start"
    if ($text === "/start") {
        // Start session and show options
        start_session($chat_id);
    } else {
        // Process message based on session state (for prayer request)
        handle_prayer_request($chat_id, $text, '', '');
    }
} elseif (isset($update['object']['payload'])) {
    // It's a button (callback query equivalent) event
    $payload = json_decode($update['object']['payload'], true);
    $callback_data = $payload['callback_data'];
    $chat_id = $update['object']['message']['from_id'];

    // Process callback data for each feature
    if ($callback_data === "check_balance") {
        // Show balance
        handle_check_balance($chat_id, '');
    } elseif ($callback_data === "refill_balance") {
        // Show balance refill options
        handle_refill_balance_options($chat_id, '');
    } elseif (strpos($callback_data, "refill_balance_") === 0) {
        // Handle balance refill based on button
        handle_refill_balance($chat_id, $callback_data, '');
    } elseif (in_array($callback_data, ['request_feature', 'confirm_prayer', 'reject_prayer'])) {
        // Handle feature request (prayer request flow)
        handle_prayer_request($chat_id, '', $callback_data, '');
    }
}

?>
