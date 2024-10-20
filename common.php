<?php

// Common utility functions used across multiple modules for VK

function send_message($chat_id, $text, $reply_markup = null) {
    global $vk_token;
    
    $url = "https://api.vk.com/method/messages.send";
    $data = [
        'user_id' => $chat_id,
        'message' => $text,
        'random_id' => mt_rand(),
        'access_token' => $vk_token,
        'v' => '5.131'  // API version
    ];

    if ($reply_markup) {
        $data['keyboard'] = json_encode($reply_markup);
    }

    file_get_contents($url . '?' . http_build_query($data));
}

function send_invoice($chat_id, $title, $description, $payload, $provider_token, $currency, $price) {
    // VK does not have a direct invoice method like Telegram.
    // You would need to integrate a third-party payment system or VK Pay.
}

function answer_callback_query($callback_query_id, $text) {
    global $vk_token;

    // VK doesn’t have callback queries like Telegram. You'll handle it with message events.
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

// Function to send audio to the user via VK
function send_audio($chat_id, $audio_file) {
    global $vk_token;

    // VK requires uploading files first and then sending them
    $upload_url = "https://api.vk.com/method/docs.getMessagesUploadServer?type=audio_message&peer_id=$chat_id&access_token=$vk_token&v=5.131";
    $upload_response = file_get_contents($upload_url);
    $upload_data = json_decode($upload_response, true);

    if (isset($upload_data['response']['upload_url'])) {
        $upload_url = $upload_data['response']['upload_url'];

        // Now upload the file
        $post_fields = ['file' => new CURLFile(realpath($audio_file))];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $upload_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $upload_result = curl_exec($ch);
        curl_close($ch);

        $upload_result = json_decode($upload_result, true);

        // Save the audio message and send it
        if (isset($upload_result['file'])) {
            $save_url = "https://api.vk.com/method/docs.save?file=" . $upload_result['file'] . "&access_token=$vk_token&v=5.131";
            $save_response = file_get_contents($save_url);
            $save_data = json_decode($save_response, true);

            if (isset($save_data['response'][0]['owner_id']) && isset($save_data['response'][0]['id'])) {
                $owner_id = $save_data['response'][0]['owner_id'];
                $doc_id = $save_data['response'][0]['id'];

                // Send the audio message
                $send_url = "https://api.vk.com/method/messages.send";
                $data = [
                    'user_id' => $chat_id,
                    'attachment' => "doc{$owner_id}_{$doc_id}",
                    'random_id' => mt_rand(),
                    'access_token' => $vk_token,
                    'v' => '5.131'
                ];

                file_get_contents($send_url . '?' . http_build_query($data));
            }
        }
    }
}

?>
