<?php
header('Content-Type: application/json');

echo json_encode([
    "message" => "Welcome to My REST API",
    "available_endpoints" => [
        "/api/admin",
        "/api/student"
    ]
]);
?>