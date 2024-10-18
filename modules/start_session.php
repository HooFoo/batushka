<?php
function start_session($chat_id) {
    global $pdo;

    // Check if the user already exists in the DB
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $chat_id]);

    if ($stmt->rowCount() === 0) {
        // Insert new session if the user doesn't exist
        $stmt = $pdo->prepare("INSERT INTO sessions (user_id, balance, state) VALUES (:user_id, 0, 'initial')");
        $stmt->execute(['user_id' => $chat_id]);
        $initial_message = "Сессия началась. Ваш баланс: 0 ₽";
    } else {
        // Reset the session if the user exists
        $stmt = $pdo->prepare("UPDATE sessions SET state = 'initial' WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $chat_id]);
        $initial_message = "Сессия перезапущена. Ваш баланс: 0 ₽";
    }

    // Send the initial message with buttons
    send_message_with_buttons($chat_id, $initial_message);
}

// Function to send a message with inline keyboard buttons
function send_message_with_buttons($chat_id, $text) {
    global $telegram_token;
    
    // Define the inline keyboard buttons
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => 'Пополнить баланс', 'callback_data' => 'refill_balance'],
                ['text' => 'Проверить баланс', 'callback_data' => 'check_balance']
            ],
            [
                ['text' => 'Запросить функцию', 'callback_data' => 'request_feature']
            ]
        ]
    ];

    // Prepare the data for the API request
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => json_encode($keyboard)
    ];

    // Send the message
    send_message($chat_id, $data['text'], $data['reply_markup']);
}

// Updated send_message function to include reply_markup
function send_message($chat_id, $text, $reply_markup = null) {
    global $telegram_token, $log_file;
    $url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $text];

    if ($reply_markup) {
        $data['reply_markup'] = $reply_markup; // Add inline keyboard if provided
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
?>
