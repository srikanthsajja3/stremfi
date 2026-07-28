<?php
// backend/ip_whitelist.php

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: List IP Whitelist
if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM ip_whitelist ORDER BY id DESC");
    $stmt->execute();
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
    }
    echo json_encode(["success" => true, "ip_whitelist" => $list]);
    exit;
}

// POST: Add IP Address
if ($method === 'POST') {
    $user = requireAuth();
    $ipAddress = isset($input['ip_address']) ? trim($input['ip_address']) : (isset($input['ipAddress']) ? trim($input['ipAddress']) : '');
    $description = isset($input['description']) ? trim($input['description']) : '';
    $status = isset($input['status']) && in_array(strtolower($input['status']), ['enabled', 'disabled']) ? strtolower($input['status']) : 'enabled';

    if (empty($ipAddress)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "IP address is required."]);
        exit;
    }

    // Check duplicate IP
    $chk = $db->prepare("SELECT id FROM ip_whitelist WHERE ip_address = :ip LIMIT 1");
    $chk->bindParam(':ip', $ipAddress);
    $chk->execute();
    if ($chk->fetch()) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "IP address already exists in whitelist."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO ip_whitelist (ip_address, description, status) VALUES (:ip, :desc, :status)");
    $stmt->bindParam(':ip', $ipAddress);
    $stmt->bindParam(':desc', $description);
    $stmt->bindParam(':status', $status);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();
    echo json_encode(["success" => true, "message" => "IP address added to whitelist successfully.", "id" => $newId]);
    exit;
}

// PUT: Update IP Address or Toggle Status (Enable/Disable)
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid IP entry ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM ip_whitelist WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "IP entry not found."]);
        exit;
    }

    $ipAddress = isset($input['ip_address']) ? trim($input['ip_address']) : (isset($input['ipAddress']) ? trim($input['ipAddress']) : $existing['ip_address']);
    $description = isset($input['description']) ? trim($input['description']) : $existing['description'];
    $status = isset($input['status']) && in_array(strtolower($input['status']), ['enabled', 'disabled']) ? strtolower($input['status']) : $existing['status'];

    $updateStmt = $db->prepare("UPDATE ip_whitelist SET ip_address = :ip, description = :desc, status = :status WHERE id = :id");
    $updateStmt->bindParam(':ip', $ipAddress);
    $updateStmt->bindParam(':desc', $description);
    $updateStmt->bindParam(':status', $status);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode(["success" => true, "message" => "IP whitelist entry updated successfully."]);
    exit;
}

// DELETE: Delete IP Address
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid IP entry ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM ip_whitelist WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "IP address removed from whitelist successfully."]);
    exit;
}
