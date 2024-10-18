<?php

// Function to handle starting or resetting a user session

function start_session($chat_id) {
    global $db;

    // Create or reset session in the database
    $stmt = $db->prepare("INSERT INTO users (user_id, session_state) VALUES (?, 'none') ON DUPLICATE KEY UPDATE session_state='none'");
    $stmt->execute([$chat_id]);

    // Send welcome message with action options
    send_message($chat_id, "Добро пожаловать! Выберите действие:", json_encode([
        'inline_keyboard' => [
            [
                ['text' => 'Пополнить баланс', 'callback_data' => 'refill_balance'],
                ['text' => 'Проверить баланс', 'callback_data' => 'check_balance']
            ],
            [
                ['text' => 'Заказать молитву', 'callback_data' => 'request_feature']
            ]
        ]
    ]));
}
