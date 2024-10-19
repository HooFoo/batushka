<?php
require_once('modules/generate_prayer.php');

// Function to handle the prayer request feature
function handle_prayer_request($chat_id, $text, $callback_data, $callback_query_id) {
    global $db;
    global $price;
    global $strings;

    // Check if user is waiting for prayer text
    $session_state = get_user_session($chat_id);
    
    // Step 1: If user presses the "request_feature" button
    if ($callback_data === "request_feature") {
        // Send message asking for prayer text
        send_message($chat_id, $strings->get('request_prayer_text'));
        
        // Set session state to waiting for prayer
        update_user_session($chat_id, 'waiting_for_prayer');
        
        // Answer callback
        answer_callback_query($callback_query_id, $strings->get('request_received'));
    }
    
    // Step 2: Capture prayer text and ask for confirmation
    elseif ($session_state === 'waiting_for_prayer' && !empty($text)) {
        // Save prayer request to DB
        $stmt = $db->prepare("INSERT INTO prayer_requests (user_id, prayer_text) VALUES (?, ?)");
        $stmt->execute([$chat_id, $text]);
        
        // Prepare confirmation message
        $confirmation_text = $strings->get('confirm_prayer', ['text' => $text, 'price' => $price]);
        send_message($chat_id, $confirmation_text, json_encode([
            'inline_keyboard' => [
                [
                    ['text' => $strings->get('confirm_button'), 'callback_data' => 'confirm_prayer'],
                    ['text' => $strings->get('cancel_button'), 'callback_data' => 'reject_prayer']
                ]
            ]
        ]));
        
        // Update session state to waiting for confirmation
        update_user_session($chat_id, 'waiting_for_confirmation');
    }

    // Step 3: Handle confirmation or rejection
    elseif ($callback_data === "confirm_prayer") {
        // Deduct the balance (assume 100 rubles)
        if (deduct_balance($chat_id, $price)) {
            // Generate prayer after balance deduction
            $stmt = $db->prepare("SELECT prayer_text FROM prayer_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$chat_id]);
            $prayer_request = $stmt->fetchColumn();

            // Call function to generate the prayer
            generate_prayer($chat_id, $prayer_request);
            
            // Update session state to none
            update_user_session($chat_id, 'none');
        } else {
            send_message($chat_id, $strings->get('insufficient_balance'));
        }
    } elseif ($callback_data === "reject_prayer") {
        // Reset to asking for new prayer text
        send_message($chat_id, $strings->get('prayer_cancelled'));
        update_user_session($chat_id, 'waiting_for_prayer');
    }
}

?>
