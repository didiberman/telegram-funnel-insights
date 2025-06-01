<?php
// unsub.php - Fixed version for MailerLite unsubscribe webhooks

// Configuration
define('DB_PATH', 'journey_data.sqlite');
define('WEBHOOK_SECRET', 'VRKIfGgNDq');
define('LOG_FILE', 'unsub_log.txt');

// Function to log events
function logEvent($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

// Start processing
logEvent("============ WEBHOOK REQUEST START ============");
logEvent("Request Method: " . $_SERVER['REQUEST_METHOD']);
logEvent("Request IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'));

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logEvent("ERROR: Method not allowed - " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    die('Method not allowed');
}

// Log all headers for debugging
$headers = getallheaders();
logEvent("Headers: " . json_encode($headers, JSON_PRETTY_PRINT));

// Get the raw POST data
$payload = file_get_contents('php://input');
logEvent("Payload size: " . strlen($payload) . " bytes");

if (empty($payload)) {
    logEvent("ERROR: Empty payload received");
    http_response_code(400);
    die('Empty payload');
}

// Log the raw payload
logEvent("Raw payload: " . $payload);

// Verify webhook signature
if (!empty(WEBHOOK_SECRET) && WEBHOOK_SECRET !== 'your_webhook_secret_here') {
    // MailerLite uses "Signature" header (not X-Mailerlite-Signature)
    $signature = $headers['Signature'] ?? '';

    logEvent("Found signature: " . ($signature ? "YES - $signature" : "NO"));

    if (!empty($signature)) {
        $calculated_signature = hash_hmac('sha256', $payload, WEBHOOK_SECRET);
        logEvent("Expected signature: " . $calculated_signature);
        logEvent("Received signature: " . $signature);

        if (!hash_equals($signature, $calculated_signature)) {
            logEvent("ERROR: Invalid webhook signature");
            http_response_code(403);
            die('Invalid signature');
        }

        logEvent("Signature verified successfully");
    } else {
        logEvent("WARNING: No signature found in request");
    }
}

// Parse the JSON payload
$data = json_decode($payload, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    logEvent("ERROR: Invalid JSON - " . json_last_error_msg());
    http_response_code(400);
    die('Invalid JSON');
}

logEvent("JSON parsed successfully");

// Get event type - MailerLite uses 'event' field, not 'type'
$eventType = $data['event'] ?? 'unknown';
logEvent("Event type: '$eventType'");

// Check if this is an unsubscribe event
if ($eventType === 'subscriber.unsubscribed') {
    // Extract subscriber data directly from the payload
    $email = $data['email'] ?? '';
    $subscriberId = $data['id'] ?? '';
    $status = $data['status'] ?? '';

    logEvent("Processing unsubscribe - Email: $email, ID: $subscriberId, Status: $status");

    if (empty($email) && empty($subscriberId)) {
        logEvent("ERROR: Missing subscriber information");
        http_response_code(400);
        die('Missing subscriber data');
    }

    try {
        // Connect to database
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        logEvent("Database connection successful");

        // Update user status
        $timestamp = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            UPDATE user_journey_progress
            SET is_subscribed_to_mailerlite = 0,
                current_journey_status = 'unsubscribed',
                last_activity_timestamp = :timestamp
            WHERE email = :email OR user_tracker_id = :subscriber_id
        ");

        $stmt->execute([
            ':timestamp' => $timestamp,
            ':email' => $email,
            ':subscriber_id' => $subscriberId
        ]);

        $rowsAffected = $stmt->rowCount();
        logEvent("Database update executed - Rows affected: $rowsAffected");

        if ($rowsAffected > 0) {
            logEvent("SUCCESS: Updated user: $email (ID: $subscriberId)");
        } else {
            logEvent("WARNING: No user found to update: $email (ID: $subscriberId)");

            // Check if user exists in database
            $checkStmt = $pdo->prepare("
                SELECT user_tracker_id, email, current_journey_status
                FROM user_journey_progress
                WHERE email = :email OR user_tracker_id = :subscriber_id
            ");
            $checkStmt->execute([':email' => $email, ':subscriber_id' => $subscriberId]);
            $existing = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
            logEvent("Existing records: " . json_encode($existing));
        }

    } catch (PDOException $e) {
        logEvent("DATABASE ERROR: " . $e->getMessage());
        http_response_code(500);
        die('Database error');
    }
} else {
    logEvent("Not an unsubscribe event - Type: $eventType");
}

// Send success response
http_response_code(200);
header('Content-Type: application/json');
$response = ['status' => 'success', 'message' => 'Webhook processed'];
echo json_encode($response);

logEvent("Response sent: " . json_encode($response));
logEvent("============ WEBHOOK REQUEST END ============\n");
?>
