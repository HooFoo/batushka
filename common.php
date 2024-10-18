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
?>