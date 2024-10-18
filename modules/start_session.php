<?php
function start_session($chat_id) {
    global $pdo;

    // Check if the user already exists in the DB
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $chat_id]);

    if ($stmt->rowCount() === 0) {
        // Insert new session if the user doesn't exist
        $stmt = $pdo->prepare("INSERT INTO sessions (user_id, balance, state) VALUES (:user_id, 0, 'initial')");
        $stmt->execute(['user_id' => $chat_id]);
        send_message($chat_id, "Сессия началась. Ваш баланс: 0 ₽");
    } else {
        // Reset the session if the user exists
        $stmt = $pdo->prepare("UPDATE sessions SET state = 'initial' WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $chat_id]);
        send_message($chat_id, "Сессия перезапущена. Ваш баланс: 0 ₽");
    }
}
?>
