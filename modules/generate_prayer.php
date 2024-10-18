<?php

// Function to generate a prayer based on user input
function generate_prayer($chat_id, $user_prayer_request) {
    global $db;
    global $price;

    // Prepare the prompt for ChatGPT
    $prompt = "Сгенерируй православную молитву про: " . $user_prayer_request;

    // Call ChatGPT API
    $response = call_chatgpt_api($prompt);

    if ($response && isset($response['choices'][0]['text'])) {
        // Successfully generated prayer
        $generated_prayer = trim($response['choices'][0]['text']);

        // Send the generated prayer to the user
        send_message($chat_id, "Вот ваша молитва:\n\n" . $generated_prayer);

    } else {
        // If there was a problem, inform the user and return the balance
        send_message($chat_id, "К сожалению, не удалось получить молитву. Пожалуйста, попробуйте снова.");
        
        // Refund the user
        refund_balance($chat_id, $price); // Assuming the cost was 100 rubles

        // Update session state to wait for a new prayer request
        update_user_session($chat_id, 'waiting_for_prayer');
    }
}

// Function to call the ChatGPT API for generating a prayer
function call_chatgpt_api($prompt) {
    global $api_key;  // API key is now in the config.php file
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' => 'gpt-4o',  // Or another suitable model
        'messages' => [
            ['role' => 'system', 'content' => 'Ты православный священник. Напиши молитву по следующей теме:'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 150
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

// Function to refund balance in case of failure
function refund_balance($chat_id, $amount) {
    global $db;
    $stmt = $db->prepare("UPDATE sessions SET balance = balance + ? WHERE user_id = ?");
    $stmt->execute([$amount, $chat_id]);
}

?>
