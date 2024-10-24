<?php

function send_refill_options($chat_id) {
    global $strings;

    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => $strings->get('refill_100'), 'callback_data' => 'refill_100'],
                ['text' => $strings->get('refill_200'), 'callback_data' => 'refill_200']
            ],
            [
                ['text' => $strings->get('refill_400'), 'callback_data' => 'refill_400'],
                ['text' => $strings->get('refill_1000'), 'callback_data' => 'refill_1000']
            ]
        ]
    ];

    send_message($chat_id, $strings->get('choose_refill_amount'), json_encode($keyboard));
}

function handle_refill_callback($chat_id, $callback_data, $callback_query_id) {
    global $strings;

    answer_callback_query($callback_query_id, $strings->get('refill_requested'));
    // Extract the amount from the callback data
    $amount = (int) str_replace('refill_', '', $callback_data);
    error_log("Refill amount: " . $amount);

    // Prepare a description for the invoice with interpolation
    $description = $strings->get('refill_description', ['amount' => $amount]);

    // Call the send_invoice function
    send_invoice($chat_id, $amount);
}

function handle_refill_balance_options($chat_id, $callback_query_id) {
    global $strings;
    
    answer_callback_query($callback_query_id, $strings->get('choose_refill_amount'));
    send_refill_options($chat_id);
}

function handle_successful_payment($chat_id, $payment_info) {
    global $pdo, $strings;

    $amount_paid = $payment_info['total_amount'] / 100; // Convert from kopecks to $
    update_balance($chat_id, $amount_paid);

    $stmt = $pdo->prepare("SELECT balance FROM sessions WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $chat_id]);
    $balance = $stmt->fetchColumn();

    // Log the payment in the payments table
    log_payment($chat_id, $amount_paid, true); // Assuming the payment was successful

    send_message($chat_id, $strings->get('payment_success', ['amount' => $amount_paid]) . ' ' . $strings->get('current_balance', ['balance' => $balance]));
}

function log_payment($user_id, $amount, $success) {
    global $pdo;

    $payment_method = 'VK'; // Update this if you're using different payment methods
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, payment_method, success) VALUES (:user_id, :amount, :payment_method, :success)");
    $stmt->execute([
        'user_id' => $user_id,
        'amount' => $amount,
        'payment_method' => $payment_method,
        'success' => $success
    ]);
}

function update_balance($user_id, $amount) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE sessions SET balance = balance + :amount WHERE user_id = :user_id");
    $stmt->execute(['amount' => $amount, 'user_id' => $user_id]);
}

?>