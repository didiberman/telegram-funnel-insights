<?php
// submit_journal.php (Modified to properly update journey status for all users)

require_once 'config.php';

// CONFIGURATION
$mailerlite_api_key = MAILERLITE_API_KEY;
$telegram_token   = "YOUR_TELEGRAM_TOKEN"
$telegram_chat_id = "YOUR_TELEGRAM_CHAT_ID";
$log_file         = 'journey_log.txt'; // RELATIVE PATH
$db_path          = 'journey_data.sqlite'; // RELATIVE PATH
$deduplication_window_seconds = 300; // 5 minutes

// --- HELPER FUNCTIONS ---
function log_journal_submission_event($msg) {
    global $log_file;
    $ts = date('Y-m-d H:i:s');
    $pid = getmypid(); // Get current process ID
    file_put_contents($log_file, "[$ts PID:$pid] $msg\n", FILE_APPEND | LOCK_EX);
}

function sendTelegramMessageJournal($text) {
    global $telegram_token, $telegram_chat_id;
    $url    = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $params = [
        'chat_id' => $telegram_chat_id,
        'text'    => $text,
        'disable_web_page_preview' => true
    ];
    $opts = [
        "http" => [
            "method"  => "POST",
            "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
            "timeout" => 20, // Telegram API timeout
            "content" => http_build_query($params),
            "ignore_errors" => true
        ]
    ];
    $context = stream_context_create($opts);
    log_journal_submission_event("TIMING: Telegram send START.");
    $start_time = microtime(true);
    $result  = @file_get_contents($url, false, $context);
    $duration = microtime(true) - $start_time;
    log_journal_submission_event(sprintf("TIMING: Telegram send END. Duration: %.4f seconds.", $duration));

    if ($result === FALSE) {
        log_journal_submission_event("Telegram send FAILED (Journal): file_get_contents returned false. Duration: {$duration}s. Check PHP error log for details if allow_url_fopen is disabled or other stream errors.");
        return false;
    } else {
        $response_data = json_decode($result, true);
        if (isset($response_data['ok']) && $response_data['ok'] === false) {
            log_journal_submission_event("Telegram API Error (Journal): Code " . ($response_data['error_code'] ?? 'N/A') . " - " . ($response_data['description'] ?? 'No description') . ". Duration: {$duration}s. Full Response: " . $result);
            return false;
        }
        $log_response = strlen($result) > 500 ? substr($result, 0, 500) . '...' : $result;
        log_journal_submission_event("Telegram send SUCCESS (Journal). Duration: {$duration}s. Response snippet: " . $log_response);
        return true;
    }
}

function updateMailerLiteSubscriberStatusJournal($apiKey, $subscriberIdentifier, $fieldsToUpdate) {
    // Only perform update if identifier is not empty
    if (empty($subscriberIdentifier)) {
        log_journal_submission_event("Skipping MailerLite update - empty identifier");
        return ['response' => 'Skipped - no identifier', 'http_code' => 200];
    }

    $identifierIsEmail = filter_var($subscriberIdentifier, FILTER_VALIDATE_EMAIL);
    $url = "https://api.mailerlite.com/api/v2/subscribers/" . urlencode($identifierIsEmail ? $subscriberIdentifier : $subscriberIdentifier);
    $data = [ 'fields' => $fieldsToUpdate ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-MailerLite-ApiKey: ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // MailerLite API timeout

    log_journal_submission_event("TIMING: MailerLite API call START for $subscriberIdentifier.");
    $start_time = microtime(true);
    $response_body = curl_exec($ch);
    $duration = microtime(true) - $start_time;
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    log_journal_submission_event(sprintf("TIMING: MailerLite API call END. Duration: %.4f seconds. HTTP: %d", $duration, $http_code));

    $status_to_log = isset($fieldsToUpdate['journey_status']) ? $fieldsToUpdate['journey_status'] : 'N/A';
    log_journal_submission_event("MailerLite Update (Journal) for $subscriberIdentifier with status $status_to_log: HTTP $http_code - Response: $response_body");

    return [ 'response' => $response_body, 'http_code' => $http_code ];
}

header('Content-Type: application/json');
$request_start_time = microtime(true);
log_journal_submission_event("--- submit_journal.php REQUEST START ---");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['website'])) { /* Honeypot */ exit; }

    // Accept empty values instead of requiring them
    $user_tracker_id = $_POST['user_tracker_id'] ?? '';
    $v_variable = $_POST['v_variable'] ?? '';
    $email_for_telegram = $_POST['email'] ?? '';
    $current_day_submitted = $_POST['current_day'] ?? null;
    $current_iso_timestamp = date('c');

    // Generate anonymous ID if no tracker ID exists
    if (empty($user_tracker_id)) {
        $user_tracker_id = 'anonymous_' . uniqid() . '_' . time();
        log_journal_submission_event("Generated anonymous ID: $user_tracker_id");
    }

    // Use default values for display if empty
    $v_variable_display = !empty($v_variable) ? $v_variable : 'N/A';
    $email_display = !empty($email_for_telegram) ? $email_for_telegram : 'Anonymous';

    $journal_answers = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'journal_q') === 0) {
            $journal_answers[$key] = trim($value);
        }
    }

    // Check if we have the minimum required data (day and answers)
    if ($current_day_submitted && !empty($journal_answers)) {
        $new_journey_status = '';
        $next_page = '';
        $day_display_for_telegram = '';

        switch ($current_day_submitted) {
            case 'day1': $new_journey_status = 'day1_submitted'; $next_page = 'day2.html'; $day_display_for_telegram = 'Day 1'; break;
            case 'day2': $new_journey_status = 'day2_submitted'; $next_page = 'day3.html'; $day_display_for_telegram = 'Day 2'; break;
            case 'day3': $new_journey_status = 'day3_submitted'; $next_page = 'thank_you_final.html'; $day_display_for_telegram = 'Day 3'; break;
            default:
                log_journal_submission_event("Invalid current_day value: $current_day_submitted for user $user_tracker_id");
                echo json_encode(['success' => false, 'message' => 'Invalid current day specified.']);
                exit;
        }

        log_journal_submission_event("Journal submission received: User: $user_tracker_id, Day: $current_day_submitted, Status: $new_journey_status, V: $v_variable_display, Email: $email_display");

        // --- Deduplication Check (skip for anonymous users) ---
        if (strpos($user_tracker_id, 'anonymous_') !== 0) {
            log_journal_submission_event("TIMING: Deduplication check START.");
            $dedup_start_time = microtime(true);
            try {
                $pdo_check = new PDO('sqlite:' . $db_path);
                $pdo_check->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt_check = $pdo_check->prepare("
                    SELECT submission_timestamp FROM journal_answers
                    WHERE user_tracker_id = :user_id AND day_number = :day_num
                    ORDER BY submission_timestamp DESC LIMIT 1
                ");
                $stmt_check->execute([':user_id' => $user_tracker_id, ':day_num' => $current_day_submitted]);
                $last_submission = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($last_submission) {
                    $last_submission_dt = new DateTime($last_submission['submission_timestamp']);
                    $current_dt = new DateTime($current_iso_timestamp);
                    $interval_seconds = $current_dt->getTimestamp() - $last_submission_dt->getTimestamp();
                    if ($interval_seconds < $deduplication_window_seconds) {
                        log_journal_submission_event("Duplicate submission DETECTED & BLOCKED. User: $user_tracker_id, Day: $current_day_submitted. Interval: $interval_seconds s.");
                        echo json_encode(['success' => false, 'message' => 'Duplicate submission.', 'error_code' => 'DUPLICATE_SUBMISSION']);
                        exit;
                    }
                }
            } catch (PDOException $e) {
                log_journal_submission_event("Deduplication check DB Error: " . $e->getMessage() . " - Proceeding.");
            }
            $dedup_duration = microtime(true) - $dedup_start_time;
            log_journal_submission_event(sprintf("TIMING: Deduplication check END. Duration: %.4f seconds.", $dedup_duration));
        } else {
            log_journal_submission_event("Skipping deduplication check for anonymous user.");
        }

        // Update MailerLite only if we have a real subscriber ID (not anonymous)
        $ml_result = array('http_code' => 0, 'response' => '');
        if (strpos($user_tracker_id, 'anonymous_') !== 0) {
            $fields_to_update = ['journey_status' => $new_journey_status];
            log_journal_submission_event("Attempting MailerLite update for non-anonymous user: $user_tracker_id with status: $new_journey_status");
            $ml_result = updateMailerLiteSubscriberStatusJournal($mailerlite_api_key, $user_tracker_id, $fields_to_update);
        } else {
            log_journal_submission_event("Skipping MailerLite update for anonymous user.");
        }

        $db_status = ['progress_update' => 'PENDING', 'answers_insert' => 'PENDING'];

        // ALWAYS proceed with database operations, regardless of MailerLite result
        log_journal_submission_event("TIMING: DB operations START.");
        $db_ops_start_time = microtime(true);
        try {
            $pdo = new PDO('sqlite:' . $db_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->beginTransaction();

            // Check if user exists in database first
            $stmt_check_user = $pdo->prepare("SELECT user_tracker_id FROM user_journey_progress WHERE user_tracker_id = :id");
            $stmt_check_user->execute([':id' => $user_tracker_id]);
            $user_exists = $stmt_check_user->fetch();

            if ($user_exists) {
                // Update existing user
                $stmt_progress = $pdo->prepare("UPDATE user_journey_progress SET current_journey_status = :status, last_activity_timestamp = :ts WHERE user_tracker_id = :id");
                $stmt_progress->execute([':status' => $new_journey_status, ':ts' => $current_iso_timestamp, ':id' => $user_tracker_id]);
                $db_status['progress_update'] = ($stmt_progress->rowCount() > 0) ? 'OK' : 'NO_ROWS_AFFECTED';
                log_journal_submission_event("Updated existing user progress: $user_tracker_id to status: $new_journey_status");
            } else {
                // Insert new user (for anonymous or missing users)
                $stmt_insert = $pdo->prepare("
                    INSERT INTO user_journey_progress
                    (user_tracker_id, email, v_variable, current_journey_status, is_subscribed_to_mailerlite, first_seen_timestamp, last_activity_timestamp)
                    VALUES
                    (:id, :email, :v_var, :status, :is_subscribed, :first_seen, :last_seen)
                ");
                $stmt_insert->execute([
                    ':id' => $user_tracker_id,
                    ':email' => $email_for_telegram,
                    ':v_var' => $v_variable,
                    ':status' => $new_journey_status,
                    ':is_subscribed' => strpos($user_tracker_id, 'anonymous_') === 0 ? 0 : 1,
                    ':first_seen' => $current_iso_timestamp,
                    ':last_seen' => $current_iso_timestamp
                ]);
                $db_status['progress_update'] = 'USER_CREATED';
                log_journal_submission_event("Created new user progress record: $user_tracker_id with status: $new_journey_status");
            }

            // Insert journal answers
            $stmt_answer = $pdo->prepare("INSERT INTO journal_answers (user_tracker_id, v_variable, day_number, question_identifier, answer_text, submission_timestamp) VALUES (:user_id, :v_var, :day_num, :q_id, :answer, :ts)");
            $answers_inserted_count = 0;
            foreach ($journal_answers as $question_identifier => $answer_text) {
                if (!empty($answer_text)) {
                    $stmt_answer->execute([
                        ':user_id' => $user_tracker_id,
                        ':v_var' => $v_variable_display,
                        ':day_num' => $current_day_submitted,
                        ':q_id' => $question_identifier,
                        ':answer' => $answer_text,
                        ':ts' => $current_iso_timestamp
                    ]);
                    $answers_inserted_count++;
                }
            }
            $db_status['answers_insert'] = "OK ($answers_inserted_count answers)";
            $pdo->commit();
            log_journal_submission_event("Database transaction committed successfully");
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            $db_status['progress_update'] = 'FAIL_DB_TRANSACTION: ' . $e->getMessage();
            $db_status['answers_insert'] = 'FAIL_DB_TRANSACTION: ' . $e->getMessage();
            log_journal_submission_event("SQLite Error: " . $e->getMessage());
        }
        $db_ops_duration = microtime(true) - $db_ops_start_time;
        log_journal_submission_event(sprintf("TIMING: DB operations END. Duration: %.4f seconds.", $db_ops_duration));

        // Send Telegram notification
        $user_display = strpos($user_tracker_id, 'anonymous_') === 0 ? "Anonymous User" : $email_display;
        $tg_message = "✅ {$user_display} submitted {$day_display_for_telegram} journal.\n(v: {$v_variable_display})\n";
        $answer_summary = [];
        foreach($journal_answers as $q_key => $answer) {
            $q_num = str_replace('journal_q', '', $q_key);
            $answer_summary[] = "Q{$q_num}: " . (mb_strlen($answer) > 100 ? mb_substr($answer, 0, 97) . "..." : $answer);
        }
        $tg_message .= implode("\n", $answer_summary);

        $telegram_sent_successfully = sendTelegramMessageJournal($tg_message);

        // Set cookies for journey tracking
        setcookie('journey_status', $new_journey_status, time() + (86400 * 365), "/");
        setcookie('last_activity_timestamp', $current_iso_timestamp, time() + (86400 * 365), "/");

        // If anonymous, also set the generated ID as a cookie for future submissions
        if (strpos($user_tracker_id, 'anonymous_') === 0) {
            setcookie('user_tracker_id', $user_tracker_id, time() + (86400 * 365), "/");
        }

        // Determine overall success
        $overall_success = true;
        $error_message = '';

        // Check MailerLite status only for non-anonymous users
        if (strpos($user_tracker_id, 'anonymous_') !== 0) {
            if ($ml_result['http_code'] != 200) {
                $error_message .= "MailerLite update failed (HTTP {$ml_result['http_code']}). ";
                log_journal_submission_event("MailerLite update FAILED for $user_tracker_id. HTTP: {$ml_result['http_code']}. Resp: {$ml_result['response']}");
            } else {
                log_journal_submission_event("MailerLite update SUCCESS for $user_tracker_id. Status: $new_journey_status");
            }
        }

        // Check database status
        if (strpos($db_status['progress_update'], 'FAIL') !== false || strpos($db_status['answers_insert'], 'FAIL') !== false) {
            $overall_success = false;
            $error_message .= "Database update failed. ";
        }

        if ($overall_success || $db_status['answers_insert'] === "OK ($answers_inserted_count answers)") {
            // Even if MailerLite failed, if DB succeeded, consider it successful
            echo json_encode([
                'success' => true,
                'message' => 'Journal submitted. Status: ' . $new_journey_status,
                'next_page' => $next_page,
                'new_status' => $new_journey_status,
                'db_status' => $db_status,
                'ml_status' => $ml_result['http_code'],
                'is_anonymous' => strpos($user_tracker_id, 'anonymous_') === 0
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $error_message,
                'db_status' => $db_status,
                'ml_status' => $ml_result['http_code']
            ]);
        }
    } else {
        log_journal_submission_event("Missing parameters: Day: $current_day_submitted, Answers: " . count($journal_answers));
        echo json_encode(['success' => false, 'message' => 'Missing required day or journal answers.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
$request_duration = microtime(true) - $request_start_time;
log_journal_submission_event(sprintf("--- submit_journal.php REQUEST END. Total Duration: %.4f seconds. ---", $request_duration));
?>
