<?php
/**
 * Health Check Endpoint
 * Minimal endpoint that doesn't require database
 */

http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'app' => 'BrainToper',
    'version' => '1.0.0',
    'timestamp' => date('Y-m-d H:i:s')
]);
