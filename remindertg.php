<?php
// remindertg.php - Without Markdown parsing to avoid formatting issues

$telegram_token   = "8125599242:AAGLFxszOwUkIOLTGV0bBPYod5b9wXAbSbs";
$telegram_chat_id = "614587329";
$webhook_secret   = "thisismypassword";
$log_file         = 'remindertg_log.txt';

function logWebhookEvent($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

function sendTelegramNotification($message) {
    global $telegram_token, $telegram_chat_id;

    $url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $params = [
        'chat_id' => $telegram_chat_id,
        'text'    => $message
        // Removed parse_mode to avoid Markdown parsing issues
    ];

    logWebhookEvent("Telegram - Attempting to send message");
    logWebhookEvent("Telegram - Message content: " . $message);

    $opts = [
        "http" => [
            "method"  => "POST",
            "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
            "timeout" => 10,
            "content" => http_build_query($params),
            "ignore_errors" => true
        ]
    ];

    $context = stream_context_create($opts);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        $error = error_get_last();
        logWebhookEvent("Telegram - FAILED to connect: " . json_encode($error));
        return false;
    } else {
        $response_data = json_decode($result, true);
        logWebhookEvent("Telegram - Raw API Response: " . $result);

        if (isset($response_data['ok']) && $response_data['ok'] === true) {
            logWebhookEvent("Telegram - SUCCESS: Message sent");
            return true;
        } else {
            logWebhookEvent("Telegram - API ERROR: " . ($response_data['description'] ?? 'Unknown error'));
            return false;
        }
    }
}

// Start processing
logWebhookEvent("============ WEBHOOK REQUEST START ============");
logWebhookEvent("Request Method: " . $_SERVER['REQUEST_METHOD']);
logWebhookEvent("Request IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));

// Get headers
$headers = getallheaders();

// Get the raw payload
$raw_payload = file_get_contents('php://input');
logWebhookEvent("Payload size: " . strlen($raw_payload) . " bytes");

// Verify webhook signature
if (!empty($webhook_secret)) {
    $signature = $headers['Signature'] ?? '';

    if (empty($signature)) {
        logWebhookEvent("ERROR: Missing signature");
        http_response_code(401);
        echo json_encode(['error' => 'Missing signature']);
        exit;
    }

    $expected_signature = hash_hmac('sha256', $raw_payload, $webhook_secret);

    if (!hash_equals($expected_signature, $signature)) {
        logWebhookEvent("ERROR: Signature mismatch");
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }

    logWebhookEvent("Signature verified successfully");
}

// Parse the payload
$payload = json_decode($raw_payload, true);

if ($payload === null) {
    logWebhookEvent("ERROR: Invalid JSON");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$events_to_process = [];

// Determine if batched or single event
if (isset($payload['events']) && is_array($payload['events'])) {
    $events_to_process = $payload['events'];
    logWebhookEvent("BATCHED webhook with " . count($events_to_process) . " events");
} else {
    $events_to_process[] = $payload;
    logWebhookEvent("SINGLE event webhook");
}

// Process each event
$processed_count = 0;

foreach ($events_to_process as $index => $event) {
    logWebhookEvent("--- Processing Event #$index ---");

    // Check if this is an automation step event with subscriber info
    if (isset($event['automation_step_id']) && isset($event['subscriber'])) {
        $subscriber = $event['subscriber'];
        $subscriber_email = $subscriber['email'] ?? 'Unknown';
        $subscriber_name = $subscriber['fields']['name'] ?? '';
        $sent_count = $subscriber['sent'] ?? 0;
        $journey_status = $subscriber['fields']['journey_status'] ?? 'unknown';

        logWebhookEvent("Automation step webhook - Email: $subscriber_email, Status: $journey_status, Sent: $sent_count");

        // Infer email type based on journey status
        $email_type = 'Reminder';
        if ($journey_status === 'signed_up') {
            $email_type = 'Day 1 Reminder';
        } elseif ($journey_status === 'day1_submitted') {
            $email_type = 'Day 2 Reminder';
        } elseif ($journey_status === 'day2_submitted') {
            $email_type = 'Day 3 Reminder';
        }

        // Build plain text message (no Markdown)
        $message = "📧 {$email_type} Sent\n";
        if ($subscriber_name) {
            $message .= "To: {$subscriber_name} ({$subscriber_email})\n";
        } else {
            $message .= "To: {$subscriber_email}\n";
        }
        $message .= "Journey Status: {$journey_status}\n";
        $message .= "Total Emails Sent: {$sent_count}\n";
        $message .= "Time: " . date('Y-m-d H:i:s');

        // Send notification
        if (sendTelegramNotification($message)) {
            $processed_count++;
            logWebhookEvent("SUCCESS: Telegram notification sent");
        } else {
            logWebhookEvent("FAILED: Could not send Telegram notification");
        }
    } else {
        logWebhookEvent("Not an automation step event - skipping");
    }
}

logWebhookEvent("=== SUMMARY ===");
logWebhookEvent("Total events processed: " . count($events_to_process));
logWebhookEvent("Telegram messages sent: $processed_count");

// Return success response
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'processed' => $processed_count,
    'total_events' => count($events_to_process)
]);

logWebhookEvent("============ WEBHOOK REQUEST END ============\n");
?>
