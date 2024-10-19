<?php

// Function to handle starting or resetting a user session
function start_session($chat_id) {
    global $db;
    global $strings;

    // Check if the user already exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM sessions WHERE user_id = ?");
    $stmt->execute([$chat_id]);
    $user_exists = $stmt->fetchColumn();

    if ($user_exists == 0) {
        // If the user is new, insert into the database and send description message
        $stmt = $db->prepare("INSERT INTO sessions (user_id, state) VALUES (?, 'none')");
        $stmt->execute([$chat_id]);

        // Send the bot description message for new users
        send_message($chat_id, $strings->get('bot_description'));
    } else {
        // If the user exists, reset their session state
        $stmt = $db->prepare("UPDATE sessions SET state = 'none' WHERE user_id = ?");
        $stmt->execute([$chat_id]);
    }

    // Send welcome message with action options
    send_message($chat_id, $strings->get('choose_action'), generate_main_menu());
}

// Function to generate main menu buttons
function generate_main_menu() {
    global $strings;
    return json_encode([
        'inline_keyboard' => [
            [
                ['text' => $strings->get('refill_balance'), 'callback_data' => 'refill_balance'],
                ['text' => $strings->get('check_balance'), 'callback_data' => 'check_balance']
            ],
            [
                ['text' => $strings->get('request_prayer'), 'callback_data' => 'request_feature']
            ]
        ]
    ]);
}

?>
