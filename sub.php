<?php
// sub.php (Modified for SQLite integration, reintegrated YouTube API, and RELATIVE PATHS)
$country_names=[
    // ... (country names array as before) ...
    // ... (content of country names array) ...
    // ... (ensure all countries are listed as in the original provided file) ...
    // For brevity, assuming the full array is here.
    // Example from provided content:
    'AF'=>'Afghanistan','AX'=>'Åland Islands','AL'=>'Albania','DZ'=>'Algeria','AS'=>'American Samoa','AD'=>'Andorra','AO'=>'Angola','AI'=>'Anguilla','AQ'=>'Antarctica','AG'=>'Antigua and Barbuda','AR'=>'Argentina','AM'=>'Armenia','AW'=>'Aruba','AU'=>'Australia','AT'=>'Austria','AZ'=>'Azerbaijan','BS'=>'Bahamas','BH'=>'Bahrain','BD'=>'Bangladesh','BB'=>'Barbados','BY'=>'Belarus','BE'=>'Belgium','BZ'=>'Belize','BJ'=>'Benin','BM'=>'Bermuda','BT'=>'Bhutan','BO'=>'Bolivia','BQ'=>'Bonaire, Sint Eustatius and Saba','BA'=>'Bosnia and Herzegovina','BW'=>'Botswana','BV'=>'Bouvet Island','BR'=>'Brazil','IO'=>'British Indian Ocean Territory','BN'=>'Brunei Darussalam','BG'=>'Bulgaria','BF'=>'Burkina Faso','BI'=>'Burundi','KH'=>'Cambodia','CM'=>'Cameroon','CA'=>'Canada','CV'=>'Cape Verde','KY'=>'Cayman Islands','CF'=>'Central African Republic','TD'=>'Chad','CL'=>'Chile','CN'=>'China','CX'=>'Christmas Island','CC'=>'Cocos (Keeling) Islands','CO'=>'Colombia','KM'=>'Comoros','CG'=>'Congo','CD'=>'Congo, Democratic Republic of the','CK'=>'Cook Islands','CR'=>'Costa Rica','CI'=>"Côte d'Ivoire",'HR'=>'Croatia','CU'=>'Cuba','CW'=>'Curaçao','CY'=>'Cyprus','CZ'=>'Czech Republic','DK'=>'Denmark','DJ'=>'Djibouti','DM'=>'Dominica','DO'=>'Dominican Republic','EC'=>'Ecuador','EG'=>'Egypt','SV'=>'El Salvador','GQ'=>'Equatorial Guinea','ER'=>'Eritrea','EE'=>'Estonia','ET'=>'Ethiopia','FK'=>'Falkland Islands (Malvinas)','FO'=>'Faroe Islands','FJ'=>'Fiji','FI'=>'Finland','FR'=>'France','GF'=>'French Guiana','PF'=>'French Polynesia','TF'=>'French Southern Territories','GA'=>'Gabon','GM'=>'Gambia','GE'=>'Georgia','DE'=>'Germany','GH'=>'Ghana','GI'=>'Gibraltar','GR'=>'Greece','GL'=>'Greenland','GD'=>'Grenada','GP'=>'Guadeloupe','GU'=>'Guam','GT'=>'Guatemala','GG'=>'Guernsey','GN'=>'Guinea','GW'=>'Guinea-Bissau','GY'=>'Guyana','HT'=>'Haiti','HM'=>'Heard Island and McDonald Islands','VA'=>'Holy See (Vatican City State)','HN'=>'Honduras','HK'=>'Hong Kong','HU'=>'Hungary','IS'=>'Iceland','IN'=>'India','ID'=>'Indonesia','IR'=>'Iran, Islamic Republic of','IQ'=>'Iraq','IE'=>'Ireland','IM'=>'Isle of Man','IL'=>'Israel','IT'=>'Italy','JM'=>'Jamaica','JP'=>'Japan','JE'=>'Jersey','JO'=>'Jordan','KZ'=>'Kazakhstan','KE'=>'Kenya','KI'=>'Kiribati','KP'=>"Korea, Democratic People's Republic of",'KR'=>'Korea, Republic of','KW'=>'Kuwait','KG'=>'Kyrgyzstan','LA'=>"Lao People's Democratic Republic",'LV'=>'Latvia','LB'=>'Lebanon','LS'=>'Lesotho','LR'=>'Liberia','LY'=>'Libya','LI'=>'Liechtenstein','LT'=>'Lithuania','LU'=>'Luxembourg','MO'=>'Macao','MK'=>'Macedonia, the former Yugoslav Republic of','MG'=>'Madagascar','MW'=>'Malawi','MY'=>'Malaysia','MV'=>'Maldives','ML'=>'Mali','MT'=>'Malta','MH'=>'Marshall Islands','MQ'=>'Martinique','MR'=>'Mauritania','MU'=>'Mauritius','YT'=>'Mayotte','MX'=>'Mexico','FM'=>'Micronesia, Federated States of','MD'=>'Moldova, Republic of','MC'=>'Monaco','MN'=>'Mongolia','ME'=>'Montenegro','MS'=>'Montserrat','MA'=>'Morocco','MZ'=>'Mozambique','MM'=>'Myanmar','NA'=>'Namibia','NR'=>'Nauru','NP'=>'Nepal','NL'=>'Netherlands','NC'=>'New Caledonia','NZ'=>'New Zealand','NI'=>'Nicaragua','NE'=>'Niger','NG'=>'Nigeria','NU'=>'Niue','NF'=>'Norfolk Island','MP'=>'Northern Mariana Islands','NO'=>'Norway','OM'=>'Oman','PK'=>'Pakistan','PW'=>'Palau','PS'=>'Palestinian Territory, Occupied','PA'=>'Panama','PG'=>'Papua New Guinea','PY'=>'Paraguay','PE'=>'Peru','PH'=>'Philippines','PN'=>'Pitcairn','PL'=>'Poland','PT'=>'Portugal','PR'=>'Puerto Rico','QA'=>'Qatar','RE'=>'Réunion','RO'=>'Romania','RU'=>'Russian Federation','RW'=>'Rwanda','BL'=>'Saint Barthélemy','SH'=>'Saint Helena, Ascension and Tristan da Cunha','KN'=>'Saint Kitts and Nevis','LC'=>'Saint Lucia','MF'=>'Saint Martin (French part)','PM'=>'Saint Pierre and Miquelon','VC'=>'Saint Vincent and the Grenadines','WS'=>'Samoa','SM'=>'San Marino','ST'=>'Sao Tome and Principe','SA'=>'Saudi Arabia','SN'=>'Senegal','RS'=>'Serbia','SC'=>'Seychelles','SL'=>'Sierra Leone','SG'=>'Singapore','SX'=>'Sint Maarten (Dutch part)','SK'=>'Slovakia','SI'=>'Slovenia','SB'=>'Solomon Islands','SO'=>'Somalia','ZA'=>'South Africa','GS'=>'South Georgia and the South Sandwich Islands','SS'=>'South Sudan','ES'=>'Spain','LK'=>'Sri Lanka','SD'=>'Sudan','SR'=>'Suriname','SJ'=>'Svalbard and Jan Mayen','SZ'=>'Swaziland','SE'=>'Sweden','CH'=>'Switzerland','SY'=>'Syrian Arab Republic','TW'=>'Taiwan, Province of China','TJ'=>'Tajikistan','TZ'=>'Tanzania, United Republic of','TH'=>'Thailand','TL'=>'Timor-Leste','TG'=>'Togo','TK'=>'Tokelau','TO'=>'Tonga','TT'=>'Trinidad and Tobago','TN'=>'Tunisia','TR'=>'Turkey','TM'=>'Turkmenistan','TC'=>'Turks and Caicos Islands','TV'=>'Tuvalu','UG'=>'Uganda','UA'=>'Ukraine','AE'=>'United Arab Emirates','GB'=>'United Kingdom','US'=>'United States','UM'=>'United States Minor Outlying Islands','UY'=>'Uruguay','UZ'=>'Uzbekistan','VU'=>'Vanuatu','VE'=>'Venezuela','VN'=>'Viet Nam','VG'=>'Virgin Islands, British','VI'=>'Virgin Islands, U.S.','WF'=>'Wallis and Futuna','EH'=>'Western Sahara','YE'=>'Yemen','ZM'=>'Zambia','ZW'=>'Zimbabwe'
];

// CONFIGURATION
$mailerlite_api_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI0IiwianRpIjoiZGNmMDk1NmJkNzY5MmMzNWVmYzNjNjY3MzA2MTZjMGJjMWZjMmUwYjQ3OGVmNTZlNmQ2YWQ3NzIwYTMxYzFjMWRmMzdkYzJlZjYzMmQ0NzgiLCJpYXQiOjE3NDQ1MTUzODAuNjUzOTA2LCJuYmYiOjE3NDQ1MTUzODAuNjUzOTA5LCJleHAiOjQ5MDAxODg5ODAuNjUwMjAzLCJzdWIiOiIxNDU5MjMxIiwic2NvcGVzIjpbXX0.iAh78aPcvi97W3io434hho3HtWljX0BKLNwhNT2M7dB0M6np11mxNTLMXXNQC3HondoXD0T6YVN5LS5yUJwBx1FIECNvyyCLqgjoZ_1zEyvGDu5I5xDG_j-lT_1cNr1mDoa58h2cGe8-BpggesOUZ8gBGap3t3mGFWXarxzL8sAHQ932DhGSTLnO2xbnYV3jnk_4FDilIgI7tsqFqnE5lp6OXZbeTG2nvsTzufODkZhkoLMDLK9uTnT1FV54nm3ACbaryxB1MHEjiZDmSw-97JxRa1gF6r6OxRUDHs0XrMgUyy_wfLskNBEDD0MUv9GKg0Vas7WF8G2Ainsjxzv4hz98maq5oWjx0qbfoq8kgLOPfsf_AKzdngpUyaiJx5HYw956FfsS2H99fbk3XZaI_vzSGse1gsYJdx22uNArFCYOk_b-91yToiTK7See_JsSs8MNrbUk2PnALFoeobs4ZX0OntWplWRuIqWN_Mq6diSR8nZVRgBV_SvSSTYvRJmS16QtugwkUS8WB3G8v0BJ5mTcI_xez4GqqvVnb8p14TZnzcaNGSZKMg-vWX1PQmD4DxzZq5ZNdpY28Jm2uDnhohfX10hTcHZNRIbQmTbhWjjECAXJQavqGLUcfWOmTh9zjBlsNVhfgQ7ppwNkZZ9oEx0FV_LgwhXBwfjF2Vm3zao";
$mailerlite_group_id = "151384502059402766";
$log_file         = 'journey_log.txt'; // RELATIVE PATH
$youtube_api_key  = 'AIzaSyD2qGifv5SyJA0VEb3eutshlC1p5LCkc8o';
$telegram_token   = '8125599242:AAGLFxszOwUkIOLTGV0bBPYod5b9wXAbSbs';
$telegram_chat_id = '614587329';
$db_path          = 'journey_data.sqlite'; // RELATIVE PATH

// --- HELPER FUNCTIONS ---
function detect_platform($userAgent, $secChUaPlatform=null) {
    if ($secChUaPlatform && $secChUaPlatform !== '?0') return trim($secChUaPlatform, '"');
    $ua = strtolower($userAgent);
    if (strpos($ua, 'android') !== false) return 'Android';
    if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) return 'iOS';
    if (strpos($ua, 'windows') !== false) return 'Windows';
    if (strpos($ua, 'macintosh') !== false || strpos($ua, 'mac os') !== false) return 'macOS';
    if (strpos($ua, 'linux') !== false) return 'Linux';
    return 'Other';
}

function safe($str) {
    if (strpos($str, '@') !== false) return preg_replace('/[^a-zA-Z0-9@\.\-\_\+]/', '', $str);
    return preg_replace('/[^a-zA-Z0-9@\.\-\_\s\p{L}\p{N}]/u', '', $str);
}

function log_event($msg) {
    global $log_file;
    $ts = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$ts] $msg\n", FILE_APPEND | LOCK_EX);
}

function send_telegram_msg($text, &$log_status_entry) {
    global $telegram_token, $telegram_chat_id;
    $url    = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $params = [
        'chat_id' => $telegram_chat_id,
        'text'    => $text,
        'parse_mode' => 'Markdown'
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
    $result  = @file_get_contents($url, false, $context);
    if ($result === FALSE) {
        $log_status_entry = 'FAIL';
    } else {
        $log_status_entry = 'OK';
    }
}

// -------- MAIN PROCESS --------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!isset($_COOKIE['legit_visitor']) || $_COOKIE['legit_visitor'] !== 'yes') {
    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $submittedFields = print_r($_POST, true);
    log_event("BLOCKED (sub.php) - Cookie missing. IP: $ip, UA: $ua, POST: $submittedFields");
    http_response_code(403);
    exit;
}
if (!empty($_POST['website'])) { 
    log_event("BOT DETECTED (sub.php) - Honeypot field 'website' filled.");
    exit; 
}

$name      = isset($_POST['fields']['name'])     ? safe($_POST['fields']['name']) : '';
$email     = isset($_POST['fields']['email'])    ? safe($_POST['fields']['email']) : '';
$video_id  = isset($_POST['fields']['video_id']) ? safe($_POST['fields']['video_id']) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    log_event("INVALID EMAIL (sub.php): $email");
    http_response_code(400);
    exit;
}

$log_status = ['ipinfo' => 'SKIP', 'youtube' => 'PENDING', 'mailerlite' => 'PENDING', 'telegram' => 'PENDING', 'db_insert' => 'PENDING'];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$country = $city = $country_code = "";
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secChUaPlatform = $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? null;
$platform = detect_platform($userAgent, $secChUaPlatform);
$current_iso_timestamp = date('c');

$ipinfo_url = "https://ipinfo.io/" . urlencode($ip) . "/json";
$ipinfo_res = @file_get_contents($ipinfo_url);
if ($ipinfo_res === FALSE) {
    $log_status['ipinfo'] = 'FAIL';
} else {
    $data = json_decode($ipinfo_res, true);
    $city = isset($data['city']) ? safe($data['city']) : '';
    $country_code = isset($data['country']) ? safe($data['country']) : '';
    $country = isset($country_names[$country_code]) ? $country_names[$country_code] : $country_code;
    $log_status['ipinfo'] = 'OK';
}

$video_title = '';
if ($video_id && isset($youtube_api_key) && strlen($youtube_api_key)) {
    $yt_api_url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id=" . urlencode($video_id) . "&key=" . $youtube_api_key;
    $yt_api_res = @file_get_contents($yt_api_url);
    if ($yt_api_res === FALSE) {
        $log_status['youtube'] = 'FAIL_FETCH';
        log_event("YouTube API fetch failed for video ID: $video_id. URL: $yt_api_url");
    } else {
        $yt_data = json_decode($yt_api_res, true);
        if (isset($yt_data['items'][0]['snippet']['title'])) {
            $video_title = safe($yt_data['items'][0]['snippet']['title']);
            $log_status['youtube'] = 'OK';
        } else {
            $log_status['youtube'] = 'FAIL_PARSE';
            log_event("YouTube API parse failed for video ID: $video_id. Response: $yt_api_res");
        }
    }
} else {
    $log_status['youtube'] = 'SKIP_NO_ID_OR_KEY';
    if (!$video_id) log_event("YouTube API skipped: No video_id provided.");
    if (empty($youtube_api_key)) log_event("YouTube API skipped: API key is empty.");
}
$video_title_for_field = $video_title ?: 'Video Title Unavailable';
$video_title_for_field = mb_substr($video_title_for_field, 0, 250);

$subscriber_data = [
    'email' => $email,
    'fields' => [
        'name' => $name,
        'video_id' => $video_id, 
        'video_title' => $video_title_for_field,
        'country' => $country,
        'city' => $city,
        'ip' => $ip,
        'os' => $platform,
        'journey_status' => 'signed_up' 
    ],
    "groups" => [$mailerlite_group_id],
    'resubscribe' => true,
    'autoresponders' => true
];

$ml_curl = curl_init();
curl_setopt($ml_curl, CURLOPT_URL, 'https://api.mailerlite.com/api/v2/subscribers');
curl_setopt($ml_curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ml_curl, CURLOPT_POST, true);
curl_setopt($ml_curl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-MailerLite-ApiKey: ' . $mailerlite_api_key,
]);
curl_setopt($ml_curl, CURLOPT_POSTFIELDS, json_encode($subscriber_data));
curl_setopt($ml_curl, CURLOPT_TIMEOUT, 15);
$ml_response_body = curl_exec($ml_curl);
$ml_http_code = curl_getinfo($ml_curl, CURLINFO_HTTP_CODE);
$mailerlite_subscriber_id = null;

if ($ml_response_body === false) {
    $log_status['mailerlite'] = "FAIL: Curl error - " . curl_error($ml_curl);
} elseif ($ml_http_code == 200 || $ml_http_code == 201) {
    $log_status['mailerlite'] = 'OK';
    $ml_response_data = json_decode($ml_response_body, true);
    if (isset($ml_response_data['data']['id'])) {
        $mailerlite_subscriber_id = $ml_response_data['data']['id'];
    } elseif (isset($ml_response_data['id'])) {
        $mailerlite_subscriber_id = $ml_response_data['id'];
    }

    if ($mailerlite_subscriber_id) {
        $cookie_expiry = time() + (86400 * 365);
        setcookie('user_tracker_id', $mailerlite_subscriber_id, $cookie_expiry, "/");
        setcookie('user_email', $email, $cookie_expiry, "/");
        setcookie('journey_status', 'signed_up', $cookie_expiry, "/");
        setcookie('v_variable', $video_id, $cookie_expiry, "/");
        setcookie('last_activity_timestamp', $current_iso_timestamp, $cookie_expiry, "/");

        try {
            $pdo = new PDO('sqlite:' . $db_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("
                INSERT INTO user_journey_progress (
                    user_tracker_id, email, v_variable, current_journey_status, 
                    is_subscribed_to_mailerlite, first_seen_timestamp, last_activity_timestamp, video_title
                ) VALUES (
                    :user_tracker_id, :email, :v_variable, :current_journey_status, 
                    :is_subscribed, :first_seen, :last_activity, :video_title
                )
                ON CONFLICT(user_tracker_id) DO UPDATE SET
                    email = excluded.email,
                    v_variable = excluded.v_variable, 
                    current_journey_status = excluded.current_journey_status,
                    is_subscribed_to_mailerlite = excluded.is_subscribed_to_mailerlite,
                    last_activity_timestamp = excluded.last_activity_timestamp,
                    video_title = excluded.video_title;
            ");
            $stmt->execute([
                ':user_tracker_id' => $mailerlite_subscriber_id,
                ':email' => $email,
                ':v_variable' => $video_id,
                ':current_journey_status' => 'signed_up',
                ':is_subscribed' => 1,
                ':first_seen' => $current_iso_timestamp,
                ':last_activity' => $current_iso_timestamp,
                ':video_title' => $video_title_for_field
            ]);
            $log_status['db_insert'] = 'OK';
        } catch (PDOException $e) {
            $log_status['db_insert'] = 'FAIL: ' . $e->getMessage();
            log_event("SQLite Error (sub.php): " . $e->getMessage());
        }
    } else {
        $log_status['mailerlite'] .= ' - ID MISSING FROM RESPONSE';
        log_event("MailerLite Error (sub.php): Subscriber ID missing from response. Body: $ml_response_body");
    }
} else {
    $log_status['mailerlite'] = "FAIL: HTTP $ml_http_code - $ml_response_body";
}
curl_close($ml_curl);

$tg_msg = "✅ New Lead: {$email}\n"
          . "🎥 Video: *" . ($video_title_for_field ?: '[No Title]') . "* (`{$video_id}`)\n"
          . "📍 Location: {$city}, {$country}\n"
          . "📱 Platform: {$platform}\n"
          . "🆔 ML ID: {$mailerlite_subscriber_id}\n";

send_telegram_msg($tg_msg, $log_status['telegram']);

$log_summary = "SUBMISSION (sub.php): Email=$email, Name=$name, VideoID=$video_id, VideoTitle='$video_title_for_field', IP=$ip, Country=$country, City=$city, Platform=$platform, ML_ID=$mailerlite_subscriber_id, IPinfo={$log_status['ipinfo']}, YouTube={$log_status['youtube']}, MailerLite={$log_status['mailerlite']}, DB_Insert={$log_status['db_insert']}, Telegram={$log_status['telegram']}";
log_event($log_summary);

if ($log_status['mailerlite'] === 'OK' && $log_status['db_insert'] === 'OK') {
    echo json_encode(['status' => 'success', 'message' => 'Subscription successful!', 'redirect' => 'day1.html']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Subscription failed. Please check logs.']);
    http_response_code(500);
}
?>
