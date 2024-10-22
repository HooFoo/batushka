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
