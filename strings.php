<?php

class Strings {
    private $strings;

    public function __construct() {
        $this->loadStrings();
    }

    private function loadStrings() {
        // Example strings for the bot
        $this->strings = [
            'bot_description' => 'Добро пожаловать! Я ваш молитвенный помощник. Здесь вы можете заказать молитву за себя или своих близких. \n\n **Для корректной работы, пожалуйста, разрешите боту отправлять вам аудиосообщения в настройках.**',
            'choose_action' => 'Что бы вы хотели сделать?',
            'refill_balance' => 'Пополнить счёт',
            'check_balance' => 'Проверить счёт',
            'request_prayer' => 'Заказать молитву',
            'current_balance' => 'Ваш текущий баланс составляет: ',
            'balance_not_found' => 'К сожалению, ваш баланс не найден. Пожалуйста, начните новую сессию.',
            'balance_checked' => 'Ваш баланс проверен.',
            'choose_refill_amount' => 'Выберите сумму для пополнения:',
            'refill_description' => 'Вы выбрали пополнение на сумму {amount} рублей. Подтвердите оплату для продолжения.',
            'payment_success' => 'Ваш платёж на сумму {amount} ₽ был успешно проведён! Благодарим за ваше пожертвование.',
            'refill_100' => 'Пожертвовать 100 ₽',
            'refill_200' => 'Пожертвовать 200 ₽',
            'refill_400' => 'Пожертвовать 400 ₽',
            'refill_1000' => 'Пожертвовать 1000 ₽',
            'request_prayer_text' => 'Какую молитву вы хотите заказать? Укажите ваши намерения.',
            'request_received' => 'Запрос получен. Мы готовы приступить к молитве.',
            'confirm_prayer' => 'Вы заказали молитву: "{text}". Это стоит {price} рублей. Подтверждаете?',
            'confirm_button' => 'Подтвердить',
            'cancel_button' => 'Отменить',
            'insufficient_balance' => 'К сожалению, на вашем счёте недостаточно средств.',
            'prayer_cancelled' => 'Ваш запрос был отменён. Пожалуйста, отправьте новый текст молитвы.',
            'prayer_generating' => 'Мы составляем вашу молитву...',
            'generated_prayer' => 'Вот ваша молитва:',
            'audio_generation_failed' => 'К сожалению, не удалось создать аудиозапись молитвы. Попробуйте снова.',
            'prayer_generation_failed' => 'Не удалось получить текст молитвы. Пожалуйста, попробуйте снова.',
            'audio_send_error' => 'Произошла ошибка при отправке аудиосообщения. Проверьте настройки приватности.',
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
