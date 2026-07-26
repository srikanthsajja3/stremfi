<?php
// backend/frontend/login.php

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = isset($input['identity']) ? trim($input['identity']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';

    if (empty($identity) || empty($password)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Identity and password are required."]);
        exit;
    }

    $query = "SELECT id, name, role, email, mobile, password, wallet_balance, company_name, is_active 
              FROM operators 
              WHERE email = :identity OR mobile = :identity LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':identity', $identity);
    $stmt->execute();
    $operator = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($operator && ($password === $operator['password'] || password_verify($password, $operator['password']))) {
        if (!$operator['is_active']) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Account is inactive."]);
            exit;
        }

        $token = generateToken($operator['id'], $operator['role'], $operator['name']);
        logActivity($db, $operator['id'], "Login", "Operator successfully logged in.");

        unset($operator['password']);
        echo json_encode([
            "success" => true,
            "message" => "Authentication successful.",
            "token" => $token,
            "user" => $operator
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    }
    exit;
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}
