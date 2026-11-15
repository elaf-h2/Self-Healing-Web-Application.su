<?php
/**
* Validates API JSON responses and classifies failures.
 */

function validateAndClassifyResponse($apiResult) {
    // 1. Initialize default baseline state
    $diagnosticInfo = [
        'is_valid_json'    => false,
        'failure_category' => 'NONE',
        'is_retryable'     => false,
        'diagnostic_notes' => 'Response is healthy and valid.',
        'error_code'       => $apiResult['http_status'] ?? 0
    ];

    // 2. Check for lower-level cURL connection failures
    if (!empty($apiResult['curl_error'])) {
        return [
            'is_valid_json'    => false,
            'failure_category' => 'NETWORK_ERROR',
            'is_retryable'     => true,
            'diagnostic_notes' => 'cURL connection failure: ' . $apiResult['curl_error'],
            'error_code'       => 0
        ];
    }

    $httpStatus = $apiResult['http_status'] ?? 0;
    $rawBody    = $apiResult['body'] ?? '';

    // 3. Handle Authorization errors (401, 403)
    if ($httpStatus == 401 || $httpStatus == 403) {
        return [
            'is_valid_json'    => false,
            'failure_category' => 'AUTHORIZATION_ERROR',
            'is_retryable'     => false,
            'diagnostic_notes' => "Access denied. HTTP Status: {$httpStatus}. Check credentials.",
            'error_code'       => $httpStatus
        ];
    }

    // 4. Handle Server errors (500 and above)
    if ($httpStatus >= 500) {
        return [
            'is_valid_json'    => false,
            'failure_category' => 'TRANSIENT_SERVER_ERROR',
            'is_retryable'     => true,
            'diagnostic_notes' => "Remote server error. HTTP Status: {$httpStatus}.",
            'error_code'       => $httpStatus
        ];
    }

    // 5. Handle Rate Limit (HTTP 429)
    if ($httpStatus == 429) {
        return [
            'is_valid_json'    => false,
            'failure_category' => 'RATE_LIMIT_ERROR',
            'is_retryable'     => true,
            'diagnostic_notes' => 'Rate limit threshold exceeded (HTTP 429).',
            'error_code'       => 429
        ];
    }

    // 6. Validate JSON for HTTP 200 OK responses
    if ($httpStatus == 200) {
        json_decode($rawBody);
        $jsonError = json_last_error();

        if ($jsonError === JSON_ERROR_NONE) {
            $diagnosticInfo['is_valid_json'] = true;
            $diagnosticInfo['failure_category'] = 'SUCCESS';
            $diagnosticInfo['diagnostic_notes'] = 'JSON payload parsed successfully.';
        } else {
            $diagnosticInfo['is_valid_json'] = false;
            $diagnosticInfo['failure_category'] = 'MALFORMED_JSON';
            $diagnosticInfo['is_retryable'] = false;
            $diagnosticInfo['diagnostic_notes'] = 'Invalid JSON syntax: ' . json_last_error_msg();
        }
    } else {
        // Handle unhandled client errors (e.g., 404)
        $diagnosticInfo['failure_category'] = 'CLIENT_ERROR';
        $diagnosticInfo['is_retryable'] = false;
        $diagnosticInfo['diagnostic_notes'] = "Client-side error with HTTP code: {$httpStatus}";
    }

    return $diagnosticInfo;
}
?>