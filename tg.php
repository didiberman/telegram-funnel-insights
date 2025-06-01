<?php
// === CONFIG ===
$telegramBotToken = '8125599242:AAGLFxszOwUkIOLTGV0bBPYod5b9wXAbSbs';
$telegramChatId = '614587329';

// === GET USER INFO ===
$ip        = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
$referer   = $_SERVER['HTTP_REFERER'] ?? 'Unknown Referer';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// === LOCATION LOOKUP ===
$city = $country = 'Unknown';
try {
    $response = @file_get_contents("https://ipinfo.io/{$ip}/json");
    if ($response) {
        $details = json_decode($response, true);
        $city = $details['city'] ?? 'Unknown';
        $country = $details['country'] ?? 'Unknown';
    }
} catch (Exception $e) {
    // Silent fail
}

// === BOT PROTECTION ===
$isBot = false;
if (!empty($_POST['website'])) $isBot = true;
if (!isset($_COOKIE['legit_visitor'])) $isBot = true;

// === HANDLE PAGE VIEW TRACKING ===
if (isset($_POST['page']) && preg_match('/^day[1-4]-view$/', $_POST['page'])) {
    if ($isBot) {
        http_response_code(204);
        exit;
    }

    $day = strtoupper(str_replace('-view', '', $_POST['page']));
    $time = date("Y-m-d H:i:s");
    
    // Get user data from POST parameters
    $userEmail = $_POST['email'] ?? 'Unknown Email';
    $userTrackerId = $_POST['user_tracker_id'] ?? 'Unknown ID';
    $v_variable = $_POST['v_variable'] ?? 'Unknown Video';

    $message = "👀 <b>Visited $day</b>\n\n"
             . "📧 <b>Email:</b> $userEmail\n"
             . "🆔 <b>User ID:</b> $userTrackerId\n"
             . "🎥 <b>Video:</b> $v_variable\n"
             . "🌍 <b>Location:</b> $city, $country\n"
             . "📡 <b>IP:</b> $ip\n"
             . "🕒 <b>Time:</b> $time";

    file_get_contents("https://api.telegram.org/bot$telegramBotToken/sendMessage?" . http_build_query([
        'chat_id' => $telegramChatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ]));

    http_response_code(204);
    exit;
}

// === FORM SUBMISSION HANDLER ===
if ($isBot || empty($_POST)) {
    http_response_code(204);
    exit;
}

// Try to detect the day
$day = 'Unknown Day';
if (isset($_POST['day'])) {
    $day = htmlspecialchars($_POST['day']);
} elseif (preg_match('/day\s?(\d)/i', $referer, $match)) {
    $day = 'Day ' . $match[1];
}

$time = date("Y-m-d H:i:s");

// === Build message
$message = "🧘‍♂️ <b>$day Journal Submission</b>\n\n"
         . "🌍 <b>Location:</b> $city, $country\n"
         . "📡 <b>IP:</b> $ip\n"
         . "🔗 <b>Referer:</b> $referer\n"
         . "🧠 <b>User-Agent:</b>\n<code>$userAgent</code>\n"
         . "🕒 <b>Time:</b> $time\n\n";

// === Loop through form fields
$count = 1;
foreach ($_POST as $key => $value) {
    if ($key === 'website' || trim($value) === '') continue;
    $label = strtoupper(str_replace(['_', '-'], ' ', $key));
    $short = mb_substr(trim($value), 0, 1000);
    $message .= "──────\n<b>$count. $label</b>\n$short\n\n";
    $count++;
}

// === Send message to Telegram
file_get_contents("https://api.telegram.org/bot$telegramBotToken/sendMessage?" . http_build_query([
    'chat_id' => $telegramChatId,
    'text' => $message,
    'parse_mode' => 'HTML'
]));

http_response_code(200);
exit;
?>