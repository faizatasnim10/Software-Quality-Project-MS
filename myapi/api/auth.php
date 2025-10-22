<?php
header('Content-Type: application/json');
require __DIR__ . '/../config.php';

function generateToken() {
    return bin2hex(random_bytes(16));
}

function verifyToken($requiredRole = null) {
    global $conn;
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["message" => "Authorization header missing"]);
        exit;
    }
    $token = str_replace("Bearer ", "", $headers['Authorization']);

    if ($requiredRole === 'admin') {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            http_response_code(403);
            echo json_encode(["message" => "Invalid admin token"]);
            exit;
        }
        return $user;
    } else {
        // check admin first
        $stmt = $conn->prepare("SELECT * FROM admins WHERE token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) return $user;

        // check student
        $stmt = $conn->prepare("SELECT * FROM students WHERE token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) return $user;

        http_response_code(403);
        echo json_encode(["message" => "Invalid token"]);
        exit;
    }
}
?>