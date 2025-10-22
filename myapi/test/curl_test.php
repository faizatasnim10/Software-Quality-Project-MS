<?php
$base_admin = "http://127.0.0.1:9000/api/admin.php";
$base_student = "http://127.0.0.1:9000/api/student.php";
$headers_json = ['Content-Type: application/json'];

function send_request($method, $url, $headers = [], $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($data !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// ----------------------
// Admin API
// ----------------------
$uniqueAdmin = "admin_" . time();
echo "=== Admin Register ===\n";
$registerData = ["username"=>$uniqueAdmin, "password"=>"123456"];
$response = send_request('POST', $base_admin."?action=register", $headers_json, $registerData);
echo $response."\n\n";
$registerJson = json_decode($response,true);
$adminToken = $registerJson['token'] ?? null;

echo "=== Admin Login ===\n";
$loginData = ["username"=>$uniqueAdmin, "password"=>"123456"];
$response = send_request('POST', $base_admin."?action=login", $headers_json, $loginData);
echo $response."\n\n";
$loginJson = json_decode($response,true);
$adminToken = $loginJson['token'] ?? $adminToken;

$authHeaders = ["Content-Type: application/json","Authorization: Bearer $adminToken"];

echo "=== Get All Admins ===\n";
echo send_request('GET',$base_admin,$authHeaders)."\n\n";

echo "=== Update Admin ===\n";
echo send_request('PUT',$base_admin."?id=1",$authHeaders,["username"=>$uniqueAdmin."_upd","password"=>"newpass"])."\n\n";

echo "=== Delete Admin ===\n";
echo send_request('DELETE',$base_admin."?id=1",$authHeaders)."\n\n";

// ----------------------
// Student API
// ----------------------
$uniqueEmail = "john_".time()."@example.com";
echo "=== Add Student ===\n";
$addData = ["name"=>"John Doe","email"=>$uniqueEmail,"age"=>22];
$response = send_request('POST',$base_student,$authHeaders,$addData);
echo $response."\n\n";
$studentJson = json_decode($response,true);
$studentId = $studentJson['id'] ?? null;

echo "=== Get All Students ===\n";
echo send_request('GET',$base_student,$authHeaders)."\n\n";

$studentId = $studentId ?? 1; // fallback
echo "=== Get Student by ID ===\n";
echo send_request('GET',$base_student."?id=$studentId",$authHeaders)."\n\n";

echo "=== Update Student ===\n";
$updEmail = "john_upd_".time()."@example.com";
echo send_request('PUT',$base_student."?id=$studentId",$authHeaders,["name"=>"John Updated","email"=>$updEmail,"age"=>23])."\n\n";

echo "=== Delete Student ===\n";
echo send_request('DELETE',$base_student."?id=$studentId",$authHeaders)."\n\n";
?>