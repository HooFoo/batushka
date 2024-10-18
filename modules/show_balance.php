<?php
function show_balance($chat_id) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT balance FROM sessions WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $chat_id]);
    $balance = $stmt->fetchColumn();

    if ($balance !== false) {
        send_message($chat_id, "Ваш текущий баланс: " . $balance . " ₽");
    } else {
        send_message($chat_id, "Ваш баланс не найден. Пожалуйста, начните новую сессию.");
    }
}
?>
