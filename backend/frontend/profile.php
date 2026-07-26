<?php
// backend/frontend/profile.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();
$currentOperatorId = $user['userId'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT id, name, role, email, mobile, wallet_balance, company_name, address, city, state, pincode, profile_image 
              FROM operators WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $currentOperatorId);
    $stmt->execute();
    $operator = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "profile" => $operator]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}
