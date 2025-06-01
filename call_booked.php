<?php
// call_booked.php - Updates journey status based on cookies after TidyCal booking

require_once 'config.php';

// CONFIGURATION
$mailerlite_api_key = MAILERLITE_API_KEY;
$telegram_token   = TELEGRAM_BOT_TOKEN;
$telegram_chat_id = "YOUR_CHAT_ID";
$log_file         = 'call_booking_log.txt';
$db_path          = 'journey_data.sqlite';

// --- HELPER FUNCTIONS ---
function log_booking_event($msg) {
    global $log_file;
    $ts = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$ts] $msg\n", FILE_APPEND | LOCK_EX);
}

function sendTelegramNotification($text) {
    global $telegram_token, $telegram_chat_id;
    $url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $params = [
        'chat_id' => $telegram_chat_id,
        'text'    => $text
    ];
    $opts = [
        "http" => [
            "method"  => "POST",
            "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
            "timeout" => 10,
            "content" => http_build_query($params)
        ]
    ];
    $context = stream_context_create($opts);
    @file_get_contents($url, false, $context);
}

function updateMailerLiteStatus($apiKey, $subscriberId) {
    $url = "https://api.mailerlite.com/api/v2/subscribers/$subscriberId";
    $data = [
        'fields' => [
            'journey_status' => 'call_booked'
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-MailerLite-ApiKey: ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    log_booking_event("MailerLite update: HTTP $httpCode");

    return $httpCode == 200;
}

// --- MAIN PROCESSING ---
$updateSuccess = false;
$userMatched = false;
$userInfo = [];

// Get user identifiers from cookies
$userTrackerId = $_COOKIE['user_tracker_id'] ?? null;
$userEmail = $_COOKIE['user_email'] ?? null;
$journeyStatus = $_COOKIE['journey_status'] ?? null;

log_booking_event("Call booking page accessed - Cookies: tracker_id=$userTrackerId, email=$userEmail, status=$journeyStatus");

if ($userTrackerId || $userEmail) {
    try {
        $pdo = new PDO('sqlite:' . $db_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Find user by tracker ID or email
        if ($userTrackerId) {
            $stmt = $pdo->prepare("SELECT * FROM user_journey_progress WHERE user_tracker_id = :id");
            $stmt->execute([':id' => $userTrackerId]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM user_journey_progress WHERE email = :email");
            $stmt->execute([':email' => $userEmail]);
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userMatched = true;
            $userInfo = $user;
            $oldStatus = $user['current_journey_status'];

            // Update to call_booked
            $stmt = $pdo->prepare("
                UPDATE user_journey_progress
                SET current_journey_status = 'call_booked',
                    last_activity_timestamp = :ts
                WHERE user_tracker_id = :id
            ");
            $stmt->execute([
                ':ts' => date('c'),
                ':id' => $user['user_tracker_id']
            ]);

            log_booking_event("Database updated: {$user['email']} from $oldStatus to call_booked");

            // Update MailerLite
            if (updateMailerLiteStatus($mailerlite_api_key, $user['user_tracker_id'])) {
                log_booking_event("MailerLite updated successfully");
            }

            // Update cookie
            setcookie('journey_status', 'call_booked', time() + (86400 * 365), "/");

            $updateSuccess = true;
        } else {
            log_booking_event("User not found: tracker_id=$userTrackerId, email=$userEmail");
        }
    } catch (PDOException $e) {
        log_booking_event("Database error: " . $e->getMessage());
    }
}

// Always send Telegram notification
$message = "📞 Call Booked via TidyCal!\n\n";

if ($userMatched) {
    $message .= "✅ USER MATCHED\n";
    $message .= "Email: {$userInfo['email']}\n";
    $message .= "ID: {$userInfo['user_tracker_id']}\n";
    $message .= "Previous: {$userInfo['current_journey_status']}\n";
    $message .= "New: call_booked\n";
} else {
    $message .= "⚠️ USER NOT MATCHED\n";
    $message .= "Cookie Email: " . ($userEmail ?? 'None') . "\n";
    $message .= "Cookie ID: " . ($userTrackerId ?? 'None') . "\n";
    $message .= "Journey Status: " . ($journeyStatus ?? 'None') . "\n";
    $message .= "Reason: ";

    if (!$userTrackerId && !$userEmail) {
        $message .= "No identifying cookies found";
    } elseif (!$userMatched) {
        $message .= "User not found in database";
    } else {
        $message .= "Unknown error";
    }
}

$message .= "\nTime: " . date('Y-m-d H:i:s');
sendTelegramNotification($message);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌿</text></svg>">
    <title>Call Booked Successfully!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 40px 20px;
            background-color: #111827;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            width: 100%;
            background: #1f2937;
            padding: 40px;
            border-radius: 0.75rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            text-align: center;
            border: 1px solid #374151;
        }
        h1 {
            color: #ffffff;
            margin-bottom: 20px;
            font-size: 32px;
            font-weight: 700;
        }
        .checkmark {
            font-size: 72px;
            color: #f59e0b;
            margin-bottom: 20px;
        }
        p {
            color: #d1d5db;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 18px;
        }
        .note {
            background-color: #111827;
            padding: 20px;
            border-radius: 0.5rem;
            margin-top: 30px;
            border: 1px solid #374151;
        }
        .button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #f59e0b;
            color: #111827;
            text-decoration: none;
            border-radius: 0.5rem;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s;
            font-weight: 700;
        }
        .button:hover {
            background-color: #d97706;
        }
        .sparkle {
            position: fixed;
            pointer-events: none;
            animation: sparkle 3s linear forwards;
            font-size: 24px;
        }
        @keyframes sparkle {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
        .note p strong {
            color: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkmark">✅</div>
        <h1>Your Call is Scheduled!</h1>

        <p>You'll receive a confirmation email shortly with all the details, including the call link and time.</p>

        <div class="note">
            <p><strong>What happens next?</strong></p>
            <p>• Check your email for the calendar invite<br>
            • Add the appointment to your calendar<br>
            • Prepare any questions you'd like to discuss<br>
            • Join the call at the scheduled time</p>
        </div>

        <p>I'm looking forward to speaking with you and supporting your healing journey!</p>

        <a href="https://www.google.com/" class="button">Return to Homepage</a>
    </div>

    <script>
        // Create celebratory sparkles
        function createSparkle() {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.style.left = Math.random() * window.innerWidth + 'px';
            sparkle.style.top = window.innerHeight + 'px';
            sparkle.style.animationDuration = (3 + Math.random() * 2) + 's';
            sparkle.textContent = ['✨', '🌟', '⭐', '💫'][Math.floor(Math.random() * 4)];
            document.body.appendChild(sparkle);

            setTimeout(() => sparkle.remove(), 5000);
        }

        // Create multiple sparkles
        for (let i = 0; i < 20; i++) {
            setTimeout(createSparkle, i * 200);
        }
    </script>
</body>
</html>
