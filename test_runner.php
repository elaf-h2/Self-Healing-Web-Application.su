<?php
// test_runner.php

// 1. Require core files (including the new JsonValidator)
require_once 'includes/HttpClient.php';
require_once 'includes/Logger.php';
require_once 'includes/ErrorDetector.php';
require_once 'includes/JsonValidator.php';
require_once 'config.php';

// 2. Define target API URL
$apiUrl = 'https://jsonplaceholder.typicode.com/posts/1';

echo "<h3>Starting API Test & Error Detection Process...</h3>";

// 3. Send cURL request
$apiResult = sendApiRequest($apiUrl);

// 4. Detect failure using ErrorDetector
$failureAnalysis = detectApiFailure($apiResult);

// 5. Handle failure or success results
if ($failureAnalysis['is_failure']) {
    echo "<b style='color: red;'>Failure Detected:</b> " . $failureAnalysis['message'] . "<br>";
    echo "- Error Type: " . $failureAnalysis['error_type'] . "<br>";
    echo "- Is Retryable: " . ($failureAnalysis['is_retryable'] ? 'Yes' : 'No') . "<br>";
} else {
    echo "<b style='color: green;'>Success:</b> " . $failureAnalysis['message'] . "<br>";
    
    // 6. Validate JSON format and structure using JsonValidator
    $diagnosticPayload = validateAndClassifyResponse($apiResult);
    
    if ($diagnosticPayload['is_valid_json']) {
        echo "<b style='color: green;'>JSON Validation:</b> Payload parsed successfully without syntax errors.<br>";
    } else {
        echo "<b style='color: red;'>JSON Validation Failed:</b> " . $diagnosticPayload['diagnostic_notes'] . "<br>";
    }

    // Log metrics to database on success
    $apiId = 1;
    $isLogged = logApiRequest($apiId, $apiResult['response_time'], $apiResult['http_status']);
    
    if ($isLogged) {
        echo "<b style='color: green;'>Metrics successfully logged to database!</b>";
    } else {
        echo "<b style='color: red;'>Failed to log into database.</b>";
    }
}
?>