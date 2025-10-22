<?php
header('Content-Type: application/json');
require __DIR__ . '/../config.php';
require __DIR__ . '/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$user = verifyToken();

switch ($method) {
    case 'POST':
        if ($user['username'] ?? false) { // admin
            $stmt = $conn->prepare("INSERT INTO students (name,email,age,token) VALUES (?,?,?,?)");
            $token = generateToken();
            $stmt->execute([$input['name'], $input['email'], $input['age'], $token]);
            $id = $conn->lastInsertId();
            echo json_encode(["message" => "Student added successfully", "token" => $token, "id" => $id]);
        } else {
            http_response_code(403);
            echo json_encode(["message" => "Only admin can add students"]);
        }
        break;

    case 'GET':
        $headers = getallheaders();
        $authToken = $headers['Authorization'] ?? '';
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($student ?: ["message"=>"Student not found"]);
        } else {
            $stmt = $conn->query("SELECT * FROM students");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($students);
        }
        break;

    case 'PUT':
        $idToUpdate = $_GET['id'] ?? '';
        if (($user['id'] ?? '') != $idToUpdate && !($user['username'] ?? false)) {
            http_response_code(403);
            echo json_encode(["message"=>"Cannot update other students"]);
            exit;
        }
        $stmt = $conn->prepare("UPDATE students SET name=?, email=?, age=? WHERE id=?");
        $stmt->execute([$input['name'], $input['email'], $input['age'], $idToUpdate]);
        echo json_encode(["message"=>"Student updated successfully"]);
        break;

    case 'DELETE':
        if (!($user['username'] ?? false)) { // admin only
            http_response_code(403);
            echo json_encode(["message"=>"Only admin can delete students"]);
            exit;
        }
        $idToDelete = $_GET['id'] ?? '';
        $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
        $stmt->execute([$idToDelete]);
        echo json_encode(["message"=>"Student deleted successfully"]);
        break;
}
?>