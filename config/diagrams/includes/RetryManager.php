<?php


require_once 'HttpClient.php';


function executeWithRetry($url, $maxRetries = 3) {
    $attempt = 0;
    $backoff = 1; // Initial delay in seconds

    while ($attempt < $maxRetries) {
        $attempt++;
        
        // Send request
        $result = sendApiRequest($url);
        
        $httpStatus = $result['http_status'] ?? 0;
        $hasCurlError = !empty($result['curl_error']);

        // Return on success
        if ($httpStatus == 200 && !$hasCurlError) {
            return $result;
        }

        // Check if error is retryable
        $isRetryable = ($httpStatus >= 500 || $httpStatus == 429 || $hasCurlError);

        // Stop if not retryable or max attempts reached
        if (!$isRetryable || $attempt >= $maxRetries) {
            return $result;
        }

        // Add backoff and jitter
        $jitter = rand(0, 1000) / 1000;
        $sleepSeconds = $backoff + $jitter;
        
        // Wait before next retry
        usleep((int)($sleepSeconds * 1000000));

        // Double backoff time
        $backoff *= 2;
    }

    return $result;
}
?>