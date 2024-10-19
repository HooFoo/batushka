<?php

class Strings {
    private $strings;

    public function __construct() {
        $this->loadStrings();
    }

    private function loadStrings() {
        // Example strings for the bot
        $this->strings = [
            'bot_description' => 'Welcome! I am your prayer companion, here to help you request prayers for yourself or your loved ones. \n\n **Please allow the bot to send audio messages in your privacy settings for full functionality.**',
            'choose_action' => 'How can I assist you today?',
            'refill_balance' => 'Make an Offering',
            'check_balance' => 'Check Your Offerings',
            'request_prayer' => 'Request a Prayer',
            'current_balance' => 'Your current offering balance is: ${amount}',
            'balance_not_found' => 'We couldn’t locate your balance. Please start a new session.',
            'balance_checked' => 'Your offering balance has been checked.',
            'choose_refill_amount' => 'Choose your offering amount:',
            'refill_description' => 'You’ve chosen an offering of {amount}. Please confirm to proceed.',
            'payment_success' => 'Thank you! Your offering of {amount} has been received.',
            'refill_100' => 'Offer $1',
            'refill_200' => 'Offer $2',
            'refill_400' => 'Offer $4',
            'refill_1000' => 'Offer $10',
            'request_prayer_text' => 'What prayer would you like to request? Please share your intentions.',
            'request_received' => 'Your prayer request has been received. We are ready to proceed.',
            'confirm_prayer' => 'You requested the following prayer: "{text}". The offering is {price}. Do you confirm?',
            'confirm_button' => 'Confirm',
            'cancel_button' => 'Cancel',
            'insufficient_balance' => 'You don’t have enough offerings to proceed.',
            'prayer_cancelled' => 'Your request has been canceled. Please submit a new prayer request.',
            'prayer_generating' => 'We are preparing your prayer...',
            'generated_prayer' => 'Here is your prayer:',
            'audio_generation_failed' => 'We couldn’t generate an audio recording of your prayer. Please try again.',
            'prayer_generation_failed' => 'We couldn’t generate the prayer text. Please try again.',
            'audio_send_error' => 'There was an issue sending the audio message. Please check your privacy settings.',
            'prompt_pray' => 'You are a Catholic priest. Please craft a beautiful and heartfelt prayer, no longer than 1024 characters, based on the following intention:',
            'prompt_saints' => 'You are a Catholic priest. Please give me recommendation how to pray for the following intention:',
            'saints_recommendation' => 'How best to pray:',
        ];                   
    }

    // Function to get a string with optional interpolation
    public function get($key, $params = []) {
        $string = $this->strings[$key] ?? 'Text unavailable';

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
