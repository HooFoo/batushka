<?php

// Include configuration, common functions, and feature modules
require_once('config.php');
require_once('db_connection.php');
require_once('strings.php');
require_once('common.php');
require_once('modules/feature_request.php');  // Handle prayer request
require_once('modules/show_balance.php');          // Handle balance check
require_once('modules/refill_balance.php');   // Handle balance refill
require_once('modules/start_session.php');    // Handle starting session
require_once('modules/handle_checkout.php');          // Handle payment

// Assign $pdo to $db to maintain compatibility with modules using $db
global $pdo;
$db = $pdo;

// Now $db can be used in all modules as a global variable
global $db;

// localization
$strings = new Strings();
global $strings;

if (!$db) {
    error_log("Database connection failed");
    exit;
}

// Get webhook data
$input = file_get_contents('php://input');
$update = json_decode($input, true);

// Check if it's a callback query or a message
if (isset($update['callback_query'])) {
    $callback_data = $update['callback_query']['data'];
    $chat_id = $update['callback_query']['message']['chat']['id'];
    $callback_query_id = $update['callback_query']['id'];

    // Process callback data for each feature
    if ($callback_data === "check_balance") {
        // Show balance
        handle_check_balance($chat_id, $callback_query_id);
    } elseif ($callback_data === "refill_balance") {
        // Show balance refill options
        handle_refill_balance_options($chat_id, $callback_query_id);
    } elseif (strpos($callback_data, "refill_balance_") === 0) {
        // Handle balance refill based on button
        handle_refill_balance($chat_id, $callback_data, $callback_query_id);
    } elseif (in_array($callback_data, ['request_feature', 'confirm_prayer', 'reject_prayer'])) {
        // Handle feature request (prayer request flow)
        handle_prayer_request($chat_id, '', $callback_data, $callback_query_id);
    } elseif (strpos($callback_data, "refill_") === 0) {
        // Handle balance refill based on selected amount
        handle_refill_callback($chat_id, $callback_data);
    }

} if (isset($update['pre_checkout_query'])) {
    $pre_checkout_query_id = $update['pre_checkout_query']['id'];
    // Always confirm the pre-checkout query to proceed with payment
    confirm_pre_checkout($pre_checkout_query_id);
} elseif (isset($update['message']['successful_payment'])) {
    // Handle successful payment
    $chat_id = $update['message']['chat']['id'];
    $payment_info = $update['message']['successful_payment'];

    // Call the handle_successful_payment function
    handle_successful_payment($chat_id, $payment_info);
} elseif (isset($update['message'])) {
    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'];

    // If the message contains "/start"
    if ($text === "/start") {
        // Start session and show options
        start_session($chat_id);
    } else {
        // Process message based on session state (for prayer request)
        handle_prayer_request($chat_id, $text, '', '');
    }
} else {
    error_log("Unexpected request: " . print_r($update, true));
}
?>