<?php
header('Content-Type: application/json');
require __DIR__ . '/../config.php';
require __DIR__ . '/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        if ($_GET['action'] == 'register') {
            $stmt = $conn->prepare("INSERT INTO admins (username, password, token) VALUES (?, ?, ?)");
            $token = generateToken();
            $stmt->execute([$input['username'], password_hash($input['password'], PASSWORD_DEFAULT), $token]);
            echo json_encode(["message" => "Admin registered", "token" => $token]);
        } elseif ($_GET['action'] == 'login') {
            $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$input['username']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($admin && password_verify($input['password'], $admin['password'])) {
                if (!$admin['token']) {
                    $token = generateToken();
                    $update = $conn->prepare("UPDATE admins SET token = ? WHERE id = ?");
                    $update->execute([$token, $admin['id']]);
                    $admin['token'] = $token;
                }
                echo json_encode(["token" => $admin['token']]);
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Invalid credentials"]);
            }
        }
        break;

    case 'GET':
        $user = verifyToken('admin');
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT id, username FROM admins WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($admin ?: ["message"=>"Admin not found"]);
        } else {
            $stmt = $conn->query("SELECT id, username FROM admins");
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($admins);
        }
        break;

    case 'PUT':
        $user = verifyToken('admin');
        $idToUpdate = $_GET['id'] ?? '';
        $stmt = $conn->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$input['username'], password_hash($input['password'], PASSWORD_DEFAULT), $idToUpdate]);
        echo json_encode(["message" => "Admin updated"]);
        break;

    case 'DELETE':
        $user = verifyToken('admin');
        $idToDelete = $_GET['id'] ?? '';
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$idToDelete]);
        echo json_encode(["message" => "Admin deleted"]);
        break;
}
?>