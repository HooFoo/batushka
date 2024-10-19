<?php

class Strings {
    private $strings;

    public function __construct() {
        $this->loadStrings();
    }

    private function loadStrings() {
        // Example strings for the bot
        $this->strings = [
            'bot_description' => 'Добро пожаловать! Я бот-молитвенник. Вы можете заказать молитву. \n\n **Для корректной работы разрешите боту присылать вам аудио сообщения в настройках приватности.**',
            'choose_action' => 'Выберите действие:',
            'refill_balance' => 'Пополнить баланс',
            'check_balance' => 'Проверить баланс',
            'request_prayer' => 'Заказать молитву',
            'current_balance' => 'Ваш текущий баланс: ',
            'balance_not_found' => 'Ваш баланс не найден. Пожалуйста, начните новую сессию.',
            'balance_checked' => 'Баланс проверен.',
            'choose_refill_amount' => 'Выберите сумму пополнения: ',
            'refill_description' => 'Вы запросили пополнение баланса на {amount} рублей.',
            'payment_success' => 'Платёж на сумму {amount} ₽ успешно выполнен!',
            'refill_100' => '100 ₽',
            'refill_200' => '200 ₽',
            'refill_400' => '400 ₽',
            'refill_1000' => '1000 ₽',
            'request_prayer_text' => 'Какую бы молитву вы хотели получить?',
            'request_received' => 'Запрос принят.',
            'confirm_prayer' => 'Вы хотите заказать молитву: "{text}"? Это стоит {price} рублей. Подтверждаете?',
            'confirm_button' => 'Подтвердить',
            'cancel_button' => 'Отменить',
            'insufficient_balance' => 'Недостаточно средств на балансе.',
            'prayer_cancelled' => 'Вы отменили запрос. Пожалуйста, укажите новую молитву.',
            'prayer_generating' => 'Пишем вашу молитву ...',
            'generated_prayer' => 'Вот ваша молитва:',
            'audio_generation_failed' => 'К сожалению, не удалось создать запись вашей молитвы.',
            'prayer_generation_failed' => 'К сожалению, не удалось получить молитву. Пожалуйста, попробуйте снова.',
            'audio_send_error' => 'Произошла ошибка при отправке аудиофайла в Telegram. Возможно у вас отключены аудиосообщения.',
        ];
        
    }

    // Function to get a string with optional interpolation
    public function get($key, $params = []) {
        $string = $this->strings[$key] ?? 'Текст недоступен';

        // Replace any placeholders with provided params
        foreach ($params as $param_key => $param_value) {
            $string = str_replace("{" . $param_key . "}", $param_value, $string);
        }

        return $string;
    }
}

// Example usage
// $strings->get('refill_description', ['amount' => 100]);

?>
