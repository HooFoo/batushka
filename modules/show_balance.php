<?php

// Function to handle checking and displaying the user's balance
function handle_check_balance($chat_id, $callback_query_id) {
    global $db;
    global $strings;

    // Respond to the callback query
    answer_callback_query($callback_query_id, $strings->get('balance_checked'));
    
    // Retrieve balance from the sessions table
    $stmt = $db->prepare("SELECT balance FROM sessions WHERE user_id = ?");
    $stmt->execute([$chat_id]);
    $balance = $stmt->fetchColumn();

    if ($balance !== false) {
        // Send balance message
        send_message($chat_id, $strings->get('current_balance', ['balance' => $balance]));
    } else {
        // If user not found, notify them
        send_message($chat_id, $strings->get('balance_not_found'));
    }
}

?>
