<?php
// includes/HttpClient.php

function sendApiRequest($url, $timeout = 5) {
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, false);

    // Record the start time for latency calculation
    $startTime = microtime(true);

    // Execute the cURL request and fetch the response
    $response = curl_exec($ch);

    // Calculate response time (latency) in seconds
    $endTime = microtime(true);
    $responseTime = $endTime - $startTime;

    // Get the HTTP status code (e.g., 200, 500)
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for any cURL errors
    $error = curl_error($ch);

    // Close the cURL session
    curl_close($ch);

    // Return the results as an associative array
    return [
        'response' => $response,
        'response_time' => round($responseTime, 4), // Round to 4 decimal places
        'http_status' => $httpStatus,
        'error' => $error
    ];
}