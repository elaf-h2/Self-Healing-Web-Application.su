<?php


function detectApiFailure($apiResult) {
    $responseCode = $apiResult['http_status'] ?? 0;
    
    $failureInfo = [
        'is_failure'   => false,
        'error_type'   => 'NONE',
        'is_retryable' => false,
        'message'      => 'Request was successful.'
    ];

    // Check for network or cURL errors
    if (isset($apiResult['curl_error']) && !empty($apiResult['curl_error'])) {
        return [
            'is_failure'   => true,
            'error_type'   => 'NETWORK_OR_TIMEOUT',
            'is_retryable' => true,
            'message'      => 'Network error or timeout occurred: ' . $apiResult['curl_error']
        ];
    }

    // Check HTTP status codes
    switch ($responseCode) {
        case 500:
        case 502:
        case 503:
            $failureInfo = [
                'is_failure'   => true,
                'error_type'   => 'SERVER_ERROR',
                'is_retryable' => true,
                'message'      => "Server error encountered with status code: {$responseCode}"
            ];
            break;
            
        case 429:
            $failureInfo = [
                'is_failure'   => true,
                'error_type'   => 'RATE_LIMIT',
                'is_retryable' => true,
                'message'      => 'Rate limit exceeded (HTTP 429).'
            ];
            break;

        default:
            if ($responseCode >= 400) {
                $failureInfo = [
                    'is_failure'   => true,
                    'error_type'   => 'CLIENT_OR_OTHER_ERROR',
                    'is_retryable' => false,
                    'message'      => "HTTP error code received: {$responseCode}"
                ];
            }
            break;
    }

    return $failureInfo;
}
?>