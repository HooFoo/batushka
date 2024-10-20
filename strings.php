<?php

class Strings {
    private $strings;

    public function __construct() {
        $this->loadStrings();
    }

    private function loadStrings() {
        // Example strings for the bot
        $this->strings = [
            'bot_description' => '¡Bienvenido! Soy tu compañero de oración. Aquí puedes solicitar oraciones para ti o tus seres queridos. \n\n **Para un correcto funcionamiento, por favor permite que el bot te envíe mensajes de audio en la configuración de privacidad.**',
            'choose_action' => '¿Qué te gustaría hacer?',
            'refill_balance' => 'Hacer una ofrenda',
            'check_balance' => 'Consultar saldo',
            'request_prayer' => 'Solicitar una oración',
            'current_balance' => 'Tu saldo actual es: {balance} $',
            'balance_not_found' => 'No se pudo encontrar tu saldo. Por favor, inicia una nueva sesión.',
            'balance_checked' => 'Tu saldo ha sido verificado.',
            'choose_refill_amount' => 'Elige el monto de la ofrenda:',
            'refill_description' => 'Has elegido hacer una ofrenda de {amount} $. Por favor, confirma para proceder.',
            'payment_success' => '¡Tu ofrenda de {amount} $ ha sido recibida! Gracias por tu generosidad.',
            'refill_100' => 'Ofrendar 1 $',
            'refill_200' => 'Ofrendar 2 $',
            'refill_400' => 'Ofrendar 4 $',
            'refill_1000' => 'Ofrendar 10 $',
            'request_prayer_text' => '¿Qué oración te gustaría solicitar? Indica tus intenciones.',
            'request_received' => 'Solicitud recibida. Estamos listos para comenzar con la oración.',
            'confirm_prayer' => 'Has solicitado la oración: "{text}". Esto tiene un costo de {price} $. ¿Confirmas?',
            'confirm_button' => 'Confirmar',
            'cancel_button' => 'Cancelar',
            'insufficient_balance' => 'No tienes suficiente saldo para continuar.',
            'prayer_cancelled' => 'Tu solicitud ha sido cancelada. Por favor, envía un nuevo texto de oración.',
            'prayer_generating' => 'Estamos preparando tu oración...',
            'generated_prayer' => 'Aquí está tu oración:',
            'audio_generation_failed' => 'No se pudo generar la grabación de tu oración. Inténtalo de nuevo.',
            'prayer_generation_failed' => 'No se pudo generar el texto de la oración. Inténtalo de nuevo.',
            'audio_send_error' => 'Hubo un error al enviar el mensaje de audio. Verifica la configuración de privacidad.',
            'prompt_pray' => 'Eres un sacerdote católico. Escribe una oración profunda y sincera, no más de 1024 caracteres, basada en el siguiente tema:',
            'prompt_saints' => 'Eres un sacerdote católico. ¿A qué santos se debe rezar por la siguiente intención? Sé breve y da recomendaciones específicas sobre cómo rezar a los santos.',
            'saints_recommendation' => 'La mejor manera de rezar:',
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
