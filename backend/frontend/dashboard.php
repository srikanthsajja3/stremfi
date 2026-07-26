<?php
// backend/frontend/dashboard.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();
$currentOperatorId = $user['userId'];
$currentOperatorRole = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Determine filters based on hierarchy
    if ($currentOperatorRole === 'super_admin') {
        // Super Admin sees everything
        $custCountQuery = "SELECT COUNT(*) as count FROM customers";
        $devCountQuery = "SELECT COUNT(*) as count FROM customer_devices WHERE status = 'ACTIVE'";
        $subCountQuery = "SELECT COUNT(*) as count FROM customer_subscriptions WHERE status = 'ACTIVE'";
        $activityQuery = "SELECT l.*, o.name as operator_name FROM activity_logs l LEFT JOIN operators o ON l.operator_id = o.id ORDER BY l.created_at DESC LIMIT 5";
    } elseif ($currentOperatorRole === 'admin') {
        // Admin sees direct customers AND customers managed by their child providers (operators)
        $custCountQuery = "SELECT COUNT(*) as count FROM customers c JOIN operators o ON c.operator_id = o.id WHERE o.parent_id = $currentOperatorId OR c.operator_id = $currentOperatorId";
        $devCountQuery = "SELECT COUNT(*) as count FROM customer_devices cd JOIN customers c ON cd.customer_id = c.id JOIN operators o ON c.operator_id = o.id WHERE (o.parent_id = $currentOperatorId OR c.operator_id = $currentOperatorId) AND cd.status = 'ACTIVE'";
        $subCountQuery = "SELECT COUNT(*) as count FROM customer_subscriptions cs JOIN customers c ON cs.customer_id = c.id JOIN operators o ON c.operator_id = o.id WHERE (o.parent_id = $currentOperatorId OR c.operator_id = $currentOperatorId) AND cs.status = 'ACTIVE'";
        $activityQuery = "SELECT l.*, o.name as operator_name FROM activity_logs l LEFT JOIN operators o ON l.operator_id = o.id WHERE l.operator_id = $currentOperatorId OR o.parent_id = $currentOperatorId ORDER BY l.created_at DESC LIMIT 5";
    } else {
        // Provider (operator) sees only their own customers/devices
        $custCountQuery = "SELECT COUNT(*) as count FROM customers WHERE operator_id = $currentOperatorId";
        $devCountQuery = "SELECT COUNT(*) as count FROM customer_devices cd JOIN customers c ON cd.customer_id = c.id WHERE c.operator_id = $currentOperatorId AND cd.status = 'ACTIVE'";
        $subCountQuery = "SELECT COUNT(*) as count FROM customer_subscriptions cs JOIN customers c ON cs.customer_id = c.id WHERE c.operator_id = $currentOperatorId AND cs.status = 'ACTIVE'";
        $activityQuery = "SELECT l.*, o.name as operator_name FROM activity_logs l LEFT JOIN operators o ON l.operator_id = o.id WHERE l.operator_id = $currentOperatorId ORDER BY l.created_at DESC LIMIT 5";
    }

    $stmt = $db->query($custCountQuery);
    $totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $db->query($devCountQuery);
    $activeDevices = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $db->query($subCountQuery);
    $activeSubscriptions = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Operator Wallet balance
    $stmt = $db->prepare("SELECT wallet_balance FROM operators WHERE id = :id");
    $stmt->bindParam(':id', $currentOperatorId);
    $stmt->execute();
    $walletBalance = $stmt->fetch(PDO::FETCH_ASSOC)['wallet_balance'];

    $stmt = $db->query($activityQuery);
    $recentActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "stats" => [
            "totalCustomers" => (int)$totalCustomers,
            "activeDevices" => (int)$activeDevices,
            "activeSubscriptions" => (int)$activeSubscriptions,
            "walletBalance" => (float)$walletBalance
        ],
        "recentActivity" => $recentActivity
    ]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}
