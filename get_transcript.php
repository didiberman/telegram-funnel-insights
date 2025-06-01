<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Security check - require password
$password = $_GET['pwd'] ?? $_POST['pwd'] ?? '';
if ($password !== 'abcabc123') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access'
    ]);
    exit;
}

// Configuration
$CACHE_DIR = 'transcript_cache';
$MAX_RETRIES = 3;
$BASE_DELAY = 2; // seconds
$PROXY_URL = "http://didibeing-rotate:ex7s65d96p41@p.webshare.io:80";

// Ensure cache directory exists
if (!is_dir($CACHE_DIR)) {
    mkdir($CACHE_DIR, 0755, true);
}

function getVideoId($input) {
    // If it's already just a video ID (11 characters, alphanumeric + underscore/dash)
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
        return $input;
    }
    
    // Extract from various YouTube URL formats
    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/',
        '/youtube\.com\/watch\?.*v=([A-Za-z0-9_-]{11})/',
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

function getCachedTranscript($videoId) {
    global $CACHE_DIR;
    $cacheFile = $CACHE_DIR . '/transcript_' . $videoId . '.txt';
    
    if (file_exists($cacheFile)) {
        return file_get_contents($cacheFile);
    }
    
    return null;
}

function cacheTranscript($videoId, $transcript) {
    global $CACHE_DIR;
    $cacheFile = $CACHE_DIR . '/transcript_' . $videoId . '.txt';
    file_put_contents($cacheFile, $transcript);
}

function fetchTranscript($videoId, $retries = 0) {
    global $MAX_RETRIES, $BASE_DELAY, $PROXY_URL;
    
    // YouTube's transcript API endpoint
    $url = "https://www.youtube.com/api/timedtext?lang=en&v=" . $videoId;
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    // Use proxy
    if ($PROXY_URL) {
        curl_setopt($ch, CURLOPT_PROXY, $PROXY_URL);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        // Parse XML transcript
        $transcript = parseTranscriptXML($response);
        if ($transcript) {
            return $transcript;
        }
    }
    
    // If failed and we have retries left, try again with exponential backoff
    if ($retries < $MAX_RETRIES) {
        $delay = $BASE_DELAY * pow(2, $retries);
        sleep($delay);
        return fetchTranscript($videoId, $retries + 1);
    }
    
    // Try alternative method - YouTube's auto-generated captions
    return fetchAlternativeTranscript($videoId);
}

function parseTranscriptXML($xmlContent) {
    // Suppress XML parsing errors
    libxml_use_internal_errors(true);
    
    $xml = simplexml_load_string($xmlContent);
    if ($xml === false) {
        return null;
    }
    
    $transcript = '';
    foreach ($xml->text as $textNode) {
        $text = html_entity_decode((string)$textNode, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Clean up the text
        $text = preg_replace('/\s+/', ' ', $text);
        $transcript .= trim($text) . ' ';
    }
    
    return trim($transcript);
}

function fetchAlternativeTranscript($videoId) {
    global $PROXY_URL;
    
    // Try to get transcript via YouTube's watch page
    $watchUrl = "https://www.youtube.com/watch?v=" . $videoId;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $watchUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    // Use proxy
    if ($PROXY_URL) {
        curl_setopt($ch, CURLOPT_PROXY, $PROXY_URL);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    }
    
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$html) {
        return null;
    }
    
    // Look for caption track URLs in the page
    if (preg_match('/"captionTracks":\[(.*?)\]/', $html, $matches)) {
        $captionData = json_decode('[' . $matches[1] . ']', true);
        
        // Find English captions
        foreach ($captionData as $track) {
            if (isset($track['languageCode']) && $track['languageCode'] === 'en') {
                $captionUrl = $track['baseUrl'];
                
                // Fetch the caption file
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $captionUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                // Use proxy for caption fetch too
                if ($PROXY_URL) {
                    curl_setopt($ch, CURLOPT_PROXY, $PROXY_URL);
                    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                }
                
                $captionXml = curl_exec($ch);
                curl_close($ch);
                
                if ($captionXml) {
                    return parseTranscriptXML($captionXml);
                }
            }
        }
    }
    
    return null;
}

// Main execution
try {
    // Get video ID from GET or POST
    $videoInput = $_GET['v'] ?? $_POST['v'] ?? $_GET['video_id'] ?? $_POST['video_id'] ?? '';
    
    if (empty($videoInput)) {
        throw new Exception('No video ID provided. Use ?v=VIDEO_ID or ?video_id=VIDEO_ID');
    }
    
    $videoId = getVideoId($videoInput);
    if (!$videoId) {
        throw new Exception('Invalid YouTube video ID or URL');
    }
    
    // Check cache first
    $cachedTranscript = getCachedTranscript($videoId);
    if ($cachedTranscript) {
        echo json_encode([
            'success' => true,
            'video_id' => $videoId,
            'transcript' => $cachedTranscript,
            'cached' => true,
            'word_count' => str_word_count($cachedTranscript)
        ]);
        exit;
    }
    
    // Fetch transcript
    $transcript = fetchTranscript($videoId);
    
    if ($transcript) {
        // Cache the result
        cacheTranscript($videoId, $transcript);
        
        echo json_encode([
            'success' => true,
            'video_id' => $videoId,
            'transcript' => $transcript,
            'cached' => false,
            'word_count' => str_word_count($transcript)
        ]);
    } else {
        // Return success with empty transcript
        echo json_encode([
            'success' => true,
            'video_id' => $videoId,
            'transcript' => '',
            'cached' => false,
            'word_count' => 0
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'video_id' => $videoId ?? null
    ]);
}
?>