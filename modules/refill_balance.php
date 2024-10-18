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
    $amount = 0;

    switch ($callback_data) {
        case 'refill_100':
            $amount = 10000; // 100 ₽ in kopecks
            break;
        case 'refill_200':
            $amount = 20000;
            break;
        case 'refill_400':
            $amount = 40000;
            break;
        case 'refill_1000':
            $amount = 100000;
            break;
    }

    send_invoice($chat_id, $amount);
}

function send_invoice($chat_id, $amount) {
    global $telegram_token;

    $data = [
        'chat_id' => $chat_id,
        'title' => 'Пополнение баланса',
        'description' => 'Пополнение баланса на указанную сумму.',
        'payload' => 'balance_refill_' . $amount, // Unique payload
        'provider_token' => 'YOUR_PROVIDER_TOKEN', // Your payment provider token
        'currency' => 'RUB', // Currency
        'prices' => json_encode([['label' => 'Пополнение', 'amount' => $amount]]), // Amount in kopecks
        'start_parameter' => 'balance-refill'
    ];

    $url = "https://api.telegram.org/bot$telegram_token/sendInvoice";
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];
    file_get_contents($url, false, $context);
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
?>
