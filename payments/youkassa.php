<?php

// Define your whitelisted IP addresses and CIDR ranges
$whitelisted_ips = [
    '185.71.76.0/27',
    '185.71.77.0/27',
    '77.75.153.0/25',
    '77.75.156.11',
    '77.75.156.35',
    '77.75.154.128/25',
    '2a02:5180::/32'
];

// Function to check if an IP is in a CIDR range
function ip_in_cidr($ip, $cidr) {
    list($subnet, $mask) = explode('/', $cidr);
    return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
}

// Function to check if the client's IP is whitelisted
function is_ip_whitelisted($client_ip, $whitelisted_ips) {
    foreach ($whitelisted_ips as $cidr) {
        // Check if the CIDR range is valid for IPv4
        if (strpos($cidr, '/') !== false && filter_var($cidr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (ip_in_cidr($client_ip, $cidr)) {
                return true;
            }
        }
        // Check individual IPs
        if (filter_var($cidr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $client_ip === $cidr) {
            return true;
        }
        // Check for IPv6 addresses separately
        if (filter_var($cidr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && $client_ip === $cidr) {
            return true;
        }
    }
    return false;
}

// Get the client's IP address
$client_ip = $_SERVER['REMOTE_ADDR'];

// Check if the client IP is whitelisted
if (!is_ip_whitelisted($client_ip, $whitelisted_ips)) {
    http_response_code(403); // Forbidden
    die('Access denied');
}

// Read the incoming JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("Received notification: " . print_r($data, true));

// Check for JSON decoding errors
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    die('Invalid JSON');
}

// Validate the notification data
if (isset($data['event']) && isset($data['data'])) {
    // Process the notification based on event type
    switch ($data['event']) {
        case 'payment.succeeded':
            // Handle successful payment
            $payment_id = $data['data']['id'];
            $amount = $data['data']['amount']['value'];
            $currency = $data['data']['amount']['currency'];
            // Add your logic to update the database or notify users
            // Example:
            // update_payment_status($payment_id, 'succeeded');
            break;

        case 'payment.failed':
            // Handle failed payment
            $payment_id = $data['data']['id'];
            // Add your logic for failed payments
            break;

        // Handle other events as needed
        default:
            http_response_code(400); // Bad Request
            die('Unknown event type');
    }

    // Send a 200 OK response
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(400); // Bad Request
    die('Missing event data');
}

?>
