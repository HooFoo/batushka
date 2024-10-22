<?php

// Common utility functions used across multiple modules

function send_message($chat_id, $text, $reply_markup = null) {
    global $telegram_token;
    
    $url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'markup' => 'markdown'
    ];

    if ($reply_markup) {
        $data['reply_markup'] = $reply_markup;
    }

    file_get_contents($url . '?' . http_build_query($data));
}

function send_invoice($chat_id, $price) {
    global $telegram_token;
    global $payment_provider_token;
    global $payment_title;
    global $payment_description;
    global $payment_currency;

    $url = "https://api.telegram.org/bot$telegram_token/sendInvoice";
    $prices = json_encode([["label" => $title, "amount" => $price * 100]]); // amount in smallest currency unit
    $payload = $chat_id . '-' . $amount;
    $description = str_replace("{amount}", $price, $payment_description);

    $data = [
        'chat_id' => $chat_id,
        'title' => $payment_title,
        'description' => $description,
        'payload' => $payload,
        'provider_token' => $payment_provider_token,
        'currency' => $payment_currency,
        'prices' => $prices
    ];

    $response = file_get_contents($url . '?' . http_build_query($data));
    if ($result === false || json_decode($result)->ok !== true) {
        send_message($chat_id, $strings->get('audio_send_error'));
        error_log("Error sending audio file to Telegram. \n\n" . print_r(error_get_last(), true) . "\n\n" . $result);
    } else {
        error_log("Send invoice:" . $response);
    }
    
}

function answer_callback_query($callback_query_id, $text) {
    global $telegram_token;

    $url = "https://api.telegram.org/bot$telegram_token/answerCallbackQuery";
    $data = [
        'callback_query_id' => $callback_query_id,
        'text' => $text
    ];

    file_get_contents($url . '?' . http_build_query($data));
}

// Get current session state
function get_user_session($chat_id) {
    global $db;
    $stmt = $db->prepare("SELECT state FROM sessions WHERE user_id = ?");
    $stmt->execute([$chat_id]);
    return $stmt->fetchColumn();
}

// Update user session state in DB
function update_user_session($chat_id, $state) {
    global $db;
    $stmt = $db->prepare("UPDATE sessions SET state = ? WHERE user_id = ?");
    $stmt->execute([$state, $chat_id]);
}

// Deduct balance function
function deduct_balance($chat_id, $amount) {
    global $db;
    $stmt = $db->prepare("SELECT balance FROM sessions WHERE user_id = ?");
    $stmt->execute([$chat_id]);
    $balance = $stmt->fetchColumn();
    
    if ($balance >= $amount) {
        $stmt = $db->prepare("UPDATE sessions SET balance = balance - ? WHERE user_id = ?");
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

// Function to send audio to the user
function send_audio($chat_id, $audio_file) {
    global $telegram_token;
    global $strings;

    $url = "https://api.telegram.org/bot$telegram_token/sendVoice";
    $post_fields = [
        'chat_id'   => $chat_id,
        'voice'     => new CURLFile(realpath($audio_file))
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $result = curl_exec($ch);
    curl_close($ch);

    if ($result === false || json_decode($result)->ok !== true) {
        send_message($chat_id, $strings->get('audio_send_error'));
        error_log("Error sending audio file to Telegram.");
    }

    // Optionally, delete the audio file after sending it
    unlink($audio_file);
}

?>
