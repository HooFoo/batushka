<?php
include 'config.php';         // Include configuration variables
include 'db_connection.php';  // Include database connection

// Read the update from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// Extract basic data
$chat_id = $update['message']['chat']['id'] ?? null;
$text = $update['message']['text'] ?? null;
$callback_data = $update['callback_query']['data'] ?? null;

// Handle different commands and callbacks
if ($text === "/start") {
    include 'modules/start_session.php';
    start_session($chat_id);
} elseif ($callback_data === "check_balance") {
    include 'modules/show_balance.php';
    show_balance($chat_id);
} elseif (strpos($callback_data, 'refill_') !== false) {
    include 'modules/refill_balance.php';
    handle_refill_callback($chat_id, $callback_data);
} elseif ($callback_data === "request_feature") {
    include 'modules/feature_request.php';
    handle_feature_request($chat_id);
} elseif (isset($update['message']['successful_payment'])) {
    include 'modules/refill_balance.php';
    handle_successful_payment($chat_id, $update['message']['successful_payment']);
} else {
    send_message($chat_id, "Пожалуйста, используйте одну из доступных опций.");
}

// Function to send messages to Telegram
function send_message($chat_id, $text) {
    global $telegram_token;
    $url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $text];
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];
    file_get_contents($url, false, $context);
}
?>
