<?php
// update_journey.php (Modified for simplified status system, RELATIVE PATHS, and IMPROVED TELEGRAM NOTIFICATIONS)

// CONFIGURATION
$log_file         = 'journey_log.txt'; // RELATIVE PATH
$db_path          = 'journey_data.sqlite'; // RELATIVE PATH, Path to SQLite database
$telegram_token   = "8125599242:AAGLFxszOwUkIOLTGV0bBPYod5b9wXAbSbs";
$telegram_chat_id = "614587329";

// --- HELPER FUNCTIONS ---
function log_journey_update_event($msg) {
    global $log_file;
    $ts = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$ts] $msg\n", FILE_APPEND | LOCK_EX);
}

function sendTelegramMessageUpdateJourney($text) {
    global $telegram_token, $telegram_chat_id;
    $url    = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $params = [
        'chat_id' => $telegram_chat_id,
        'text'    => $text,
        'parse_mode' => 'Markdown',
        'disable_web_page_preview' => true // Added for consistency
    ];
    $opts = [
        "http" => [
            "method"  => "POST",
            "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
            "timeout" => 20, // Increased timeout
            "content" => http_build_query($params),
            "ignore_errors" => true // Crucial for getting error response body
        ]
    ];
    $context = stream_context_create($opts);
    log_journey_update_event("Attempting to send Telegram message (UpdateJourney). Length: " . strlen($text));
    $result  = file_get_contents($url, false, $context); // Removed @ suppressor

    if ($result === FALSE) {
        log_journey_update_event("Telegram send FAILED (UpdateJourney): file_get_contents returned false. Check PHP error log for details if allow_url_fopen is disabled or other stream errors.");
        return false;
    } else {
        $response_data = json_decode($result, true);
        if (isset($response_data['ok']) && $response_data['ok'] === false) {
            log_journey_update_event("Telegram API Error (UpdateJourney): Code " . ($response_data['error_code'] ?? 'N/A') . " - " . ($response_data['description'] ?? 'No description') . ". Full Response: " . $result);
            return false;
        }
        // Consider logging only a part of the response on success if it's too verbose
        $log_response = strlen($result) > 500 ? substr($result, 0, 500) . '...' : $result;
        log_journey_update_event("Telegram send SUCCESS (UpdateJourney). Response snippet: " . $log_response);
        return true;
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_tracker_id = $_POST['user_tracker_id'] ?? null;
    $v_variable = $_POST['v_variable'] ?? 'N/A';
    $email_for_log = $_POST['email'] ?? $user_tracker_id; // Use email if available for logs, else ID
    $page_viewed = $_POST['page_viewed'] ?? null; // e.g., day1.html, day2.html
    $current_iso_timestamp = date('c');

    if ($user_tracker_id) {
        log_journey_update_event("Page View (update_journey.php): User: $user_tracker_id, Email: $email_for_log, Page: '$page_viewed', V: $v_variable. Updating last_activity_timestamp.");

        // Send Telegram notification for Day page views
        //if ($page_viewed !== null && trim($page_viewed) !== '') {
      //      log_journey_update_event("Page viewed data for notification check: '$page_viewed' (User: $user_tracker_id)");
            // Check if it's a dayX.html page (e.g., day1.html, day2.html, dayN.html)
          //  if (preg_match('/^day\d+\.html$/i', trim($page_viewed))) {
        //        $tg_message = "📄 User [{$email_for_log}] viewed page: *" . htmlspecialchars(trim($page_viewed)) . "*\n(v: {$v_variable}, ID: {$user_tracker_id})";
        //        log_journey_update_event("Attempting to send VIEW notification for '$page_viewed' to user $user_tracker_id.");
        //        sendTelegramMessageUpdateJourney($tg_message);
      //      } else {
      //          log_journey_update_event("Page '$page_viewed' is not a dayX.html page. No VIEW notification sent for user $user_tracker_id.");
      //      }
      //  } else {
      //      log_journey_update_event("Page viewed data is null or empty. No VIEW notification sent for user $user_tracker_id.");
    //    }

        $db_update_status = 'PENDING';
        try {
            $pdo = new PDO('sqlite:' . $db_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("
                UPDATE user_journey_progress
                SET last_activity_timestamp = :ts
                WHERE user_tracker_id = :id
            ");
            $stmt->execute([
                ':ts' => $current_iso_timestamp,
                ':id' => $user_tracker_id
            ]);
            if ($stmt->rowCount() > 0) {
                $db_update_status = 'OK (Timestamp Updated)';
            } else {
                $db_update_status = 'NO_ROWS_AFFECTED (User ID might not exist or timestamp identical)';
                log_journey_update_event("DB Update (update_journey.php - timestamp only): No rows affected for user_tracker_id: $user_tracker_id.");
            }
        } catch (PDOException $e) {
            $db_update_status = 'FAIL: ' . $e->getMessage();
            log_journey_update_event("SQLite Error (update_journey.php - timestamp only): " . $e->getMessage());
        }

        setcookie('last_activity_timestamp', $current_iso_timestamp, time() + (86400 * 365), "/");

        echo json_encode([
            'success' => true,
            'message' => 'Last activity timestamp updated. Page view notification processed.',
            'db_status' => $db_update_status
        ]);

    } else {
        log_journey_update_event("Missing user_tracker_id for update_journey. POST data: " . print_r($_POST, true));
        echo json_encode([
            'success' => false,
            'message' => 'Missing user_tracker_id parameter.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed'
    ]);
}
?>
