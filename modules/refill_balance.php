<?php
function send_refill_options($chat_id) {
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '100 ₽', 'callback_data' => 'refill_100'],
                ['text' => '200 ₽', 'callback_data' => 'refill_200']
            ],
            [
                ['text' => '400 ₽', 'callback_data' => 'refill_400'],
                ['text' => '1000 ₽', 'callback_data' => 'refill_1000']
            ]
        ]
    ];

    $data = [
        'chat_id' => $chat_id,
        'text' => "Выберите сумму пополнения:",
        'reply_markup' => json_encode($keyboard)
    ];

    send_message($chat_id, $data['text']);
}

function handle_refill_callback($chat_id, $callback_data) {
    // Extract the amount from the callback data
    $amount = (int) str_replace('refill_', '', $callback_data);

    // Prepare a description for the invoice
    $description = "Вы запросили пополнение баланса на $amount рублей.";

    // Call the send_invoice function
    send_invoice($chat_id, $amount, $description);
}

function send_invoice($chat_id, $amount, $description) {
    global $telegram_token;

    $url = "https://api.telegram.org/bot$telegram_token/sendInvoice";
    $data = [
        'chat_id' => $chat_id,
        'title' => 'Пополнение баланса',
        'description' => $description,
        'payload' => uniqid(), // Unique identifier for your invoice
        'provider_token' => 'YOUR_PROVIDER_TOKEN', // Replace with your actual payment provider token
        'start_parameter' => 'refill_balance', // Start parameter for the invoice
        'currency' => 'RUB', // Ensure this is the correct currency code
        'amount' => $amount * 100, // Amount in kopecks (RUB), multiply by 100
        'reply_markup' => json_encode([
            'inline_keyboard' => [[
                ['text' => 'Оплатить', 'pay' => true]
            ]]
        ])
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];

    // Send the request
    $response = file_get_contents($url, false, stream_context_create($options));
    if ($response === false) {
        error_log("Failed to send invoice: $http_response_header", 3, $log_file);
    }
}

function handle_successful_payment($chat_id, $payment_info) {
    $amount_paid = $payment_info['total_amount'] / 100; // Convert from kopecks to ₽

    update_balance($chat_id, $amount_paid);

    global $pdo;
    $stmt = $pdo->prepare("SELECT balance FROM sessions WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $chat_id]);
    $balance = $stmt->fetchColumn();

    send_message($chat_id, "Платёж на сумму " . $amount_paid . " ₽ успешно выполнен! Ваш текущий баланс: " . $balance . " ₽");
}

function update_balance($user_id, $amount) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE sessions SET balance = balance + :amount WHERE user_id = :user_id");
    $stmt->execute(['amount' => $amount, 'user_id' => $user_id]);
}

function handle_refill_callback($chat_id, $callback_data) {
    // Extract the amount from the callback data
    $amount = (int) str_replace('refill_', '', $callback_data);

    // Prepare a description for the invoice
    $description = "Вы запросили пополнение баланса на $amount рублей.";

    // Call the send_invoice function
    send_invoice($chat_id, $amount, $description);
}
?>
