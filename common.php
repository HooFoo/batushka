<?php

// Common utility functions used across multiple modules

function send_message($chat_id, $text, $reply_markup = null) {
    global $telegram_token;
    
    $url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text
    ];

    if ($reply_markup) {
        $data['reply_markup'] = $reply_markup;
    }

    file_get_contents($url . '?' . http_build_query($data));
}

function send_invoice($chat_id, $title, $description, $payload, $provider_token, $currency, $price) {
    global $telegram_token;

    $url = "https://api.telegram.org/bot$telegram_token/sendInvoice";
    $prices = json_encode([["label" => $title, "amount" => $price * 100]]); // amount in smallest currency unit

    $data = [
        'chat_id' => $chat_id,
        'title' => $title,
        'description' => $description,
        'payload' => $payload,
        'provider_token' => $provider_token,
        'currency' => $currency,
        'prices' => $prices
    ];

    file_get_contents($url . '?' . http_build_query($data));
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

    $url = "https://api.telegram.org/bot$telegram_token/sendAudio";
    $post_fields = [
        'chat_id'   => $chat_id,
        'audio'     => new CURLFile(realpath($audio_file))
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    $result = curl_exec($ch);
    curl_close($ch);

    if ($result === false) {
        error_log("Error sending audio file to Telegram.");
    }

    // Optionally, delete the audio file after sending it
    unlink($audio_file);
}
?>