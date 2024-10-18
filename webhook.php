<?php
include 'config.php';         // Include configuration variables
include 'db_connection.php';  // Include database connection

// Set the log file path
$log_file = '/var/log/telegram_bot.log'; // Change this to your desired log file path

// Read the update from Telegram
$content = file_get_contents("php://input");
if ($content === false) {
    error_log("Failed to get content from input stream", 3, $log_file);
    exit;
}

$update = json_decode($content, true);
if ($update === null) {
    error_log("Failed to decode JSON: " . json_last_error_msg(), 3, $log_file);
    exit;
}

// Extract basic data
$chat_id = $update['message']['chat']['id'] ?? null;
$text = $update['message']['text'] ?? null;
$callback_query_id = $update['callback_query']['id'] ?? null;
$callback_data = $update['callback_query']['data'] ?? null;

// Log the incoming update
error_log("Incoming update: " . print_r($update, true), 3, $log_file);

// Function to send messages to Telegram
function send_message($chat_id, $text, $reply_markup = null) {
    global $telegram_token, $log_file;
    $url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text
    ];

    if ($reply_markup) {
        $data['reply_markup'] = json_decode($reply_markup); // Add inline keyboard if provided
    }

    // Log the message being sent
    error_log("Sending message to $chat_id: $text", 3, $log_file);

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    // Send the request and log the response
    $response = file_get_contents($url, false, stream_context_create($options));
    if ($response === false) {
        error_log("Failed to send message: $http_response_header", 3, $log_file);
    }
}

// Function to answer callback queries
function answer_callback_query($callback_query_id, $text = '') {
    global $telegram_token;

    $url = "https://api.telegram.org/bot$telegram_token/answerCallbackQuery";
    $data = [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => false // Set to true if you want to show an alert message
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    // Send the request
    file_get_contents($url, false, stream_context_create($options));
}

// Check if the update is a callback query
if (isset($update['callback_query'])) {
    $chat_id = $update['callback_query']['message']['chat']['id'];
    $callback_query_id = $update['callback_query']['id'];
    $callback_data = $update['callback_query']['data'];

    // Handle different callback data
    if ($callback_data === "check_balance") {
        include 'modules/show_balance.php';
        show_balance($chat_id);
        answer_callback_query($callback_query_id, "Баланс проверен.");
    } elseif (strpos($callback_data, 'refill_') !== false) {
        include 'modules/refill_balance.php';
        handle_refill_callback($chat_id, $callback_data);
        answer_callback_query($callback_query_id, "Запрос на пополнение баланса обработан.");
    } elseif ($callback_data === "request_feature") {
        include 'modules/feature_request.php';
        handle_feature_request($chat_id);
        answer_callback_query($callback_query_id, "Запрос на функцию обработан.");
    }
} elseif ($text === "/start") {
    include 'modules/start_session.php';
    start_session($chat_id);
} elseif (isset($update['message']['successful_payment'])) {
    include 'modules/refill_balance.php';
    handle_successful_payment($chat_id, $update['message']['successful_payment']);
} else {
    send_message($chat_id, "Пожалуйста, используйте одну из доступных опций.");
}
?>
