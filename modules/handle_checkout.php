<?php

function confirm_pre_checkout($pre_checkout_query_id) {
    global $telegram_token;

    $url = "https://api.telegram.org/bot$telegram_token/answerPreCheckoutQuery";
    $data = [
        'pre_checkout_query_id' => $pre_checkout_query_id,
        'ok' => true // Change to false if you want to reject the payment
    ];

    file_get_contents($url . '?' . http_build_query($data));
}

?>