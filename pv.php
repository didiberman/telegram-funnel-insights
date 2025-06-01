<?php
$country_names=['AF'=>'Afghanistan','AX'=>'Åland Islands','AL'=>'Albania','DZ'=>'Algeria','AS'=>'American Samoa','AD'=>'Andorra','AO'=>'Angola','AI'=>'Anguilla','AQ'=>'Antarctica','AG'=>'Antigua and Barbuda','AR'=>'Argentina','AM'=>'Armenia','AW'=>'Aruba','AU'=>'Australia','AT'=>'Austria','AZ'=>'Azerbaijan','BS'=>'Bahamas','BH'=>'Bahrain','BD'=>'Bangladesh','BB'=>'Barbados','BY'=>'Belarus','BE'=>'Belgium','BZ'=>'Belize','BJ'=>'Benin','BM'=>'Bermuda','BT'=>'Bhutan','BO'=>'Bolivia','BQ'=>'Bonaire, Sint Eustatius and Saba','BA'=>'Bosnia and Herzegovina','BW'=>'Botswana','BV'=>'Bouvet Island','BR'=>'Brazil','IO'=>'British Indian Ocean Territory','BN'=>'Brunei Darussalam','BG'=>'Bulgaria','BF'=>'Burkina Faso','BI'=>'Burundi','KH'=>'Cambodia','CM'=>'Cameroon','CA'=>'Canada','CV'=>'Cape Verde','KY'=>'Cayman Islands','CF'=>'Central African Republic','TD'=>'Chad','CL'=>'Chile','CN'=>'China','CX'=>'Christmas Island','CC'=>'Cocos (Keeling) Islands','CO'=>'Colombia','KM'=>'Comoros','CG'=>'Congo','CD'=>'Congo, Democratic Republic of the','CK'=>'Cook Islands','CR'=>'Costa Rica','CI'=>"Côte d'Ivoire",'HR'=>'Croatia','CU'=>'Cuba','CW'=>'Curaçao','CY'=>'Cyprus','CZ'=>'Czech Republic','DK'=>'Denmark','DJ'=>'Djibouti','DM'=>'Dominica','DO'=>'Dominican Republic','EC'=>'Ecuador','EG'=>'Egypt','SV'=>'El Salvador','GQ'=>'Equatorial Guinea','ER'=>'Eritrea','EE'=>'Estonia','ET'=>'Ethiopia','FK'=>'Falkland Islands (Malvinas)','FO'=>'Faroe Islands','FJ'=>'Fiji','FI'=>'Finland','FR'=>'France','GF'=>'French Guiana','PF'=>'French Polynesia','TF'=>'French Southern Territories','GA'=>'Gabon','GM'=>'Gambia','GE'=>'Georgia','DE'=>'Germany','GH'=>'Ghana','GI'=>'Gibraltar','GR'=>'Greece','GL'=>'Greenland','GD'=>'Grenada','GP'=>'Guadeloupe','GU'=>'Guam','GT'=>'Guatemala','GG'=>'Guernsey','GN'=>'Guinea','GW'=>'Guinea-Bissau','GY'=>'Guyana','HT'=>'Haiti','HM'=>'Heard Island and McDonald Islands','VA'=>'Holy See (Vatican City State)','HN'=>'Honduras','HK'=>'Hong Kong','HU'=>'Hungary','IS'=>'Iceland','IN'=>'India','ID'=>'Indonesia','IR'=>'Iran, Islamic Republic of','IQ'=>'Iraq','IE'=>'Ireland','IM'=>'Isle of Man','IL'=>'Israel','IT'=>'Italy','JM'=>'Jamaica','JP'=>'Japan','JE'=>'Jersey','JO'=>'Jordan','KZ'=>'Kazakhstan','KE'=>'Kenya','KI'=>'Kiribati','KP'=>"Korea, Democratic People's Republic of",'KR'=>'Korea, Republic of','KW'=>'Kuwait','KG'=>'Kyrgyzstan','LA'=>"Lao People's Democratic Republic",'LV'=>'Latvia','LB'=>'Lebanon','LS'=>'Lesotho','LR'=>'Liberia','LY'=>'Libya','LI'=>'Liechtenstein','LT'=>'Lithuania','LU'=>'Luxembourg','MO'=>'Macao','MK'=>'Macedonia, the former Yugoslav Republic of','MG'=>'Madagascar','MW'=>'Malawi','MY'=>'Malaysia','MV'=>'Maldives','ML'=>'Mali','MT'=>'Malta','MH'=>'Marshall Islands','MQ'=>'Martinique','MR'=>'Mauritania','MU'=>'Mauritius','YT'=>'Mayotte','MX'=>'Mexico','FM'=>'Micronesia, Federated States of','MD'=>'Moldova, Republic of','MC'=>'Monaco','MN'=>'Mongolia','ME'=>'Montenegro','MS'=>'Montserrat','MA'=>'Morocco','MZ'=>'Mozambique','MM'=>'Myanmar','NA'=>'Namibia','NR'=>'Nauru','NP'=>'Nepal','NL'=>'Netherlands','NC'=>'New Caledonia','NZ'=>'New Zealand','NI'=>'Nicaragua','NE'=>'Niger','NG'=>'Nigeria','NU'=>'Niue','NF'=>'Norfolk Island','MP'=>'Northern Mariana Islands','NO'=>'Norway','OM'=>'Oman','PK'=>'Pakistan','PW'=>'Palau','PS'=>'Palestinian Territory, Occupied','PA'=>'Panama','PG'=>'Papua New Guinea','PY'=>'Paraguay','PE'=>'Peru','PH'=>'Philippines','PN'=>'Pitcairn','PL'=>'Poland','PT'=>'Portugal','PR'=>'Puerto Rico','QA'=>'Qatar','RE'=>'Réunion','RO'=>'Romania','RU'=>'Russian Federation','RW'=>'Rwanda','BL'=>'Saint Barthélemy','SH'=>'Saint Helena, Ascension and Tristan da Cunha','KN'=>'Saint Kitts and Nevis','LC'=>'Saint Lucia','MF'=>'Saint Martin (French part)','PM'=>'Saint Pierre and Miquelon','VC'=>'Saint Vincent and the Grenadines','WS'=>'Samoa','SM'=>'San Marino','ST'=>'Sao Tome and Principe','SA'=>'Saudi Arabia','SN'=>'Senegal','RS'=>'Serbia','SC'=>'Seychelles','SL'=>'Sierra Leone','SG'=>'Singapore','SX'=>'Sint Maarten (Dutch part)','SK'=>'Slovakia','SI'=>'Slovenia','SB'=>'Solomon Islands','SO'=>'Somalia','ZA'=>'South Africa','GS'=>'South Georgia and the South Sandwich Islands','SS'=>'South Sudan','ES'=>'Spain','LK'=>'Sri Lanka','SD'=>'Sudan','SR'=>'Suriname','SJ'=>'Svalbard and Jan Mayen','SZ'=>'Swaziland','SE'=>'Sweden','CH'=>'Switzerland','SY'=>'Syrian Arab Republic','TW'=>'Taiwan, Province of China','TJ'=>'Tajikistan','TZ'=>'Tanzania, United Republic of','TH'=>'Thailand','TL'=>'Timor-Leste','TG'=>'Togo','TK'=>'Tokelau','TO'=>'Tonga','TT'=>'Trinidad and Tobago','TN'=>'Tunisia','TR'=>'Turkey','TM'=>'Turkmenistan','TC'=>'Turks and Caicos Islands','TV'=>'Tuvalu','UG'=>'Uganda','UA'=>'Ukraine','AE'=>'United Arab Emirates','GB'=>'United Kingdom','US'=>'United States','UM'=>'United States Minor Outlying Islands','UY'=>'Uruguay','UZ'=>'Uzbekistan','VU'=>'Vanuatu','VE'=>'Venezuela','VN'=>'Viet Nam','VG'=>'Virgin Islands, British','VI'=>'Virgin Islands, U.S.','WF'=>'Wallis and Futuna','EH'=>'Western Sahara','YE'=>'Yemen','ZM'=>'Zambia','ZW'=>'Zimbabwe'];

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

function detect_platform($userAgent, $secChUaPlatform=null) {
    if ($secChUaPlatform && $secChUaPlatform !== '?0') return $secChUaPlatform;
    $ua = strtolower($userAgent);
    if (strpos($ua, 'android') !== false) return 'Android';
    if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) return 'iOS';
    if (strpos($ua, 'windows') !== false) return 'Windows';
    if (strpos($ua, 'macintosh') !== false || strpos($ua, 'mac os') !== false) return 'macOS';
    if (strpos($ua, 'linux') !== false) return 'Linux';
    return 'Other';
}
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$secChUaPlatform = $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? null;
$platform = detect_platform($userAgent, $secChUaPlatform);
$platform = trim($platform, '"');

// Get POST data
$video_id = isset($_POST['video_id']) ? $_POST['video_id'] : 'unknown';
$timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : date('Y-m-d\TH:i:s.v\Z', time());

// Get IP address (handle proxy headers if present)
$ip_address = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = trim(explode(",", $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
}

// Get referer
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'unknown';

// Look up city/country server-side using ipinfo.io
$city = 'unknown';
$country_code = 'unknown';
$country = 'unknown';

try {
    $ipinfo_json = @file_get_contents("https://ipinfo.io/" . urlencode($ip_address) . "/json");
    if ($ipinfo_json !== false) {
        $ipinfo = json_decode($ipinfo_json, true);
        $city = isset($ipinfo['city']) ? $ipinfo['city'] : 'unknown';
        $country_code = isset($ipinfo['country']) ? $ipinfo['country'] : 'unknown';
        $country = (isset($country_names[$country_code]) ? $country_names[$country_code] : $country_code);
    }
} catch (Exception $e) {
    // If lookup fails, fallback to 'unknown'
    $city = 'unknown';
    $country_code = 'unknown';
    $country = 'unknown';
}

// YouTube API Configuration
$youtube_api_key = 'AIzaSyD2qGifv5SyJA0VEb3eutshlC1p5LCkc8o'; // Replace with your actual YouTube API key

// Telegram Configuration
$telegram_bot_token = '8125599242:AAGLFxszOwUkIOLTGV0bBPYod5b9wXAbSbs'; // Replace with your actual Telegram bot token
$telegram_chat_id = '614587329'; // Replace with your actual Telegram chat ID

// Fetch video title from YouTube API
function getYouTubeVideoTitle($video_id, $api_key) {
    if (empty($video_id) || $video_id == 'unknown' || $video_id == 'null') {
        return "Unknown Video";
    }

    $url = "https://www.googleapis.com/youtube/v3/videos?id=$video_id&key=$api_key&part=snippet";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return "Video Title Lookup Failed: " . curl_error($ch);
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['items'][0]['snippet']['title'])) {
        return $data['items'][0]['snippet']['title'];
    } else {
        return "Video Not Found";
    }
}

// Send message to Telegram
function sendTelegramMessage($bot_token, $chat_id, $message) {
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";

    $post_fields = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML',
	'disable_web_page_preview' => true
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $success = false;

    if (!curl_errno($ch) && json_decode($response, true)['ok'] === true) {
        $success = true;
    } else {
        file_put_contents('telegram_error_log.txt',
            date('Y-m-d H:i:s') . " - Error: " . curl_error($ch) . " - Response: " . $response . "\n",
            FILE_APPEND);
    }

    curl_close($ch);

    return $success;
}

// Main execution
try {
  $ip_log_file = __DIR__ . '/pv_ip_timestamps.json';
  $now = time();
  $wait_seconds = 300; // 5 minutes

  // Load last seen data (or start new if file does not exist)
  if (file_exists($ip_log_file)) {
      $ip_times = json_decode(file_get_contents($ip_log_file), true);
      if (!is_array($ip_times)) $ip_times = [];
  } else {
      $ip_times = [];
  }

  // Check if this IP has triggered in the last 5 mins
  if (isset($ip_times[$ip_address]) && ($now - $ip_times[$ip_address]) < $wait_seconds) {
      // Don't send Telegram, just exit or continue without notify
      exit;
  }

  // Otherwise, update the last-seen time and continue
  $ip_times[$ip_address] = $now;

  // Optionally clean up old entries to keep the file small
  foreach ($ip_times as $ip_addr => $timestamp) {
      if ($now - $timestamp > $wait_seconds) {
          unset($ip_times[$ip_addr]);
      }
  }

  // Save back the updated timestamps
  file_put_contents($ip_log_file, json_encode($ip_times));


      // Get video title
    $video_title = getYouTubeVideoTitle($video_id, $youtube_api_key);

    // Format the message according to the specified template
    $message = "🌱 New visitor from $city, $country";
    $message .= "\n🎥 $video_title ($video_id)";
    $message .= "\n🕒 " . date("Y-m-d H:i:s", $timestamp);
    $message .= "\nɪᴘ: $ip_address";
    $message .= "\nOS: $platform";
    $message .= "\nReferer: $referer";

    // Send to Telegram
    $telegram_sent = sendTelegramMessage($telegram_bot_token, $telegram_chat_id, $message);

    // Log the request with IP and telegram status
    $log_message = date('Y-m-d H:i:s') .
                  " - IP: $ip_address" .
                  " - Video ID: $video_id" .
                  " - Country: $country" .
                  " - City: $city" .
                  " - OS: $platform" .
                  " - Referer: $referer" .
                  " - Telegram sent: " . ($telegram_sent ? "Yes" : "No") . "\n";

    file_put_contents('webhook_log.txt', $log_message, FILE_APPEND);

    // Return success (empty response with 200 status)
    http_response_code(200);

} catch (Exception $e) {
    // Log any errors
    file_put_contents('error_log.txt',
        date('Y-m-d H:i:s') . " - IP: $ip_address - Error: " . $e->getMessage() . "\n",
        FILE_APPEND);

    // Return error status
    http_response_code(500);
}
?>
