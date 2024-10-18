<?php

// Function to generate a prayer based on user input
function generate_prayer($chat_id, $user_prayer_request) {
    global $db;
    global $price;

    // Prepare the prompt for ChatGPT
    $prompt = $user_prayer_request;
    // Call ChatGPT API
    $response = call_chatgpt_api($prompt);
    
    // Call ChatGPT API
    if ($response && isset($response['choices'][0]['message']['content'])) {
        // Successfully generated prayer
        $generated_prayer = trim($response['choices'][0]['message']['content']);
        // Send the generated prayer to the user
        send_message($chat_id, "Вот ваша молитва:\n\n" . $generated_prayer);

        // Generate audio for the prayer text
        $audio_file = call_audio_api($generated_prayer);

        if ($audio_file) {
            // Send the generated audio to the user
            send_audio($chat_id, $audio_file);
        } else {
            // If there was a problem generating the audio
            send_message($chat_id, "К сожалению, не удалось создать запись вашей молитвы.");
        }

    } else {
        // Log failure
        error_log("Failed to generate prayer for user: $chat_id");

        // If there was a problem, inform the user and return the balance
        send_message($chat_id, "К сожалению, не удалось получить молитву. Пожалуйста, попробуйте снова.");
        
        // Refund the user
        refund_balance($chat_id, $price); // Assuming the cost was 100 rubles

        // Log refund
        error_log("Refunded balance for user: $chat_id, amount: $price");

        // Update session state to wait for a new prayer request
        update_user_session($chat_id, 'waiting_for_prayer');
    }
}

// Function to call the ChatGPT API for generating a prayer
function call_chatgpt_api($prompt) {
    global $api_key;  // API key is now in the config.php file
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' => 'gpt-4',  // Or another suitable model
        'messages' => [
            ['role' => 'system', 'content' => 'Ты православный священник. Не скупись на слова. Сделай длинную и красивую молитву. Напиши молитву по следующей теме:'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 300
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $api_key\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        error_log("Error connecting to ChatGPT API");
        return null;
    }

    return json_decode($result, true);
}

// Function to call the OpenAI API for generating audio
function call_audio_api($text) {
    global $api_key;  // API key is now in the config.php file
    $url = 'https://api.openai.com/v1/audio/speech';

    $data = [
        'text' => $text,
        'voice' => 'onyx',  // Replace with the desired voice
        'model' => 'tts1',  // Replace with desired model
        'response_format' => 'opus'
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $api_key\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        error_log("Error connecting to OpenAI Audio API");
        return null;
    }

    $response = json_decode($result, true);

    if (isset($response['data']['audio'])) {
        // Audio is base64 encoded, you may need to decode and save it
        $audio_data = base64_decode($response['data']['audio']);
        $file_path = '/tmp/prayer_' . uniqid() . '.mp3'; // Save as mp3 in temp folder
        file_put_contents($file_path, $audio_data);

        return $file_path;
    }

    return null;
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

// Function to refund balance in case of failure
function refund_balance($chat_id, $amount) {
    global $db;
    $stmt = $db->prepare("UPDATE sessions SET balance = balance + ? WHERE user_id = ?");
    $stmt->execute([$amount, $chat_id]);

    // Log refund
    error_log("Refunded balance: $amount for user: $chat_id");
}

?>
