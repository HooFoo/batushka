<?php

// Function to handle the prayer request feature
function handle_prayer_request($chat_id, $text, $callback_data, $callback_query_id) {
    global $db;

    // Check if user is waiting for prayer text
    $session_state = get_user_session($chat_id);
    
    // Step 1: If user presses the "Заказать молитву" button
    if ($callback_data === "request_feature") {
        // Send message asking for prayer text
        send_message($chat_id, "Какую бы молитву вы хотели получить?");
        
        // Set session state to waiting for prayer
        update_user_session($chat_id, 'waiting_for_prayer');
        
        // Answer callback
        answer_callback_query($callback_query_id, "Запрос принят.");
    }
    
    // Step 2: Capture prayer text and ask for confirmation
    elseif ($session_state === 'waiting_for_prayer' && !empty($text)) {
        // Save prayer request to DB
        $stmt = $db->prepare("INSERT INTO prayer_requests (user_id, prayer_text) VALUES (?, ?)");
        $stmt->execute([$chat_id, $text]);
        
        // Prepare confirmation message
        $confirmation_text = "Вы хотите заказать молитву: \"$text\"? Это стоит 100 рублей. Подтверждаете?";
        send_message($chat_id, $confirmation_text, json_encode([
            'inline_keyboard' => [
                [
                    ['text' => 'Подтвердить', 'callback_data' => 'confirm_prayer'],
                    ['text' => 'Отменить', 'callback_data' => 'reject_prayer']
                ]
            ]
        ]));
        
        // Update session state to waiting for confirmation
        update_user_session($chat_id, 'waiting_for_confirmation');
    }

    // Step 3: Handle confirmation or rejection
    elseif ($callback_data === "confirm_prayer") {
        // Deduct the balance (assume 100 rubles)
        if (deduct_balance($chat_id, 100)) {
            send_message($chat_id, "Молитва принята в обработку. Ваш баланс был уменьшен на 100 рублей.");
            update_user_session($chat_id, 'none');
            update_prayer_status($chat_id, 'confirmed');
            // Here you can call a function to start prayer generation flow
        } else {
            send_message($chat_id, "Недостаточно средств на балансе.");
        }
    } elseif ($callback_data === "reject_prayer") {
        // Reset to asking for new prayer text
        send_message($chat_id, "Вы отменили запрос. Пожалуйста, укажите новую молитву.");
        update_user_session($chat_id, 'waiting_for_prayer');
    }
}

// Update user session state in DB
function update_user_session($chat_id, $state) {
    global $db;
    $stmt = $db->prepare("UPDATE users SET session_state = ? WHERE user_id = ?");
    $stmt->execute([$state, $chat_id]);
}

// Get current session state
function get_user_session($chat_id) {
    global $db;
    $stmt = $db->prepare("SELECT session_state FROM users WHERE user_id = ?");
    $stmt->execute([$chat_id]);
    return $stmt->fetchColumn();
}

// Deduct balance function
function deduct_balance($chat_id, $amount) {
    global $db;
    $stmt = $db->prepare("SELECT balance FROM users WHERE user_id = ?");
    $stmt->execute([$chat_id]);
    $balance = $stmt->fetchColumn();
    
    if ($balance >= $amount) {
        $stmt = $db->prepare("UPDATE users SET balance = balance - ? WHERE user_id = ?");
        $stmt->execute([$amount, $chat_id]);
        return true;
    }
    
    return false;
}

// Update prayer request status
function update_prayer_status($chat_id, $status) {
    global $db;
    $stmt = $db->prepare("UPDATE prayer_requests SET status = ? WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$status, $chat_id]);
}

?>