<?php
// backend/frontend/plans.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->query("SELECT p.*, pr.name as provider_name 
                        FROM plans p 
                        LEFT JOIN providers pr ON p.provider_id = pr.id 
                        WHERE p.is_active = 1");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "plans" => $plans]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}
