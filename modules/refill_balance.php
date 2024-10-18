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

    send_message($chat_id, "Выберите сумму пополнения: ", $keyboard);
}

function handle_refill_callback($chat_id, $callback_data) {
    // Extract the amount from the callback data
    $amount = (int) str_replace('refill_', '', $callback_data);

    // Prepare a description for the invoice
    $description = "Вы запросили пополнение баланса на $amount рублей.";

    // Call the send_invoice function
    send_invoice($chat_id, $amount, $description);
}

function handle_refill_balance_options($chat_id, $callback_query_id) {
    send_refill_options($chat_id);
    answer_callback_query($callback_query_id, "Выберите сумму пополнения.");
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
