<?php
// backend/frontend/transactions.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();
$currentOperatorId = $user['userId'];
$currentOperatorRole = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($currentOperatorRole === 'super_admin') {
        $query = "SELECT t.*, o.name as operator_name 
                  FROM wallet_transactions t 
                  LEFT JOIN operators o ON t.operator_id = o.id 
                  ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
    } elseif ($currentOperatorRole === 'admin') {
        // Admin sees their own transactions and transactions of their providers
        $query = "SELECT t.*, o.name as operator_name 
                  FROM wallet_transactions t 
                  LEFT JOIN operators o ON t.operator_id = o.id 
                  WHERE t.operator_id = :parent_id OR o.parent_id = :parent_id
                  ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':parent_id', $currentOperatorId);
    } else {
        // Provider sees only their own
        $query = "SELECT t.*, :op_name as operator_name 
                  FROM wallet_transactions t 
                  WHERE t.operator_id = :operator_id 
                  ORDER BY t.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':operator_id', $currentOperatorId);
        $stmt->bindValue(':op_name', $user['name']);
    }
    
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "transactions" => $transactions]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}
