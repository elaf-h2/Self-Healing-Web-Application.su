<?php
// includes/Logger.php

// Load database connection from config.php in the root folder
require_once __DIR__ . '/../config.php';

function logApiRequest($apiId, $responseTime, $httpStatus) {
    global $pdo;

    try {
        // Insert log data into database
        $stmt = $pdo->prepare("INSERT INTO api_requests (api_id, response_time, http_status) VALUES (?, ?, ?)");
        
        // Execute the query
        $stmt->execute([$apiId, $responseTime, $httpStatus]);

        return true;
    } catch (PDOException $e) {
        // Handle database errors
        error_log("DB Error: " . $e->getMessage());
        return false;
    }
}
?>