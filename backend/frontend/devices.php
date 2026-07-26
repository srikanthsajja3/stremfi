<?php
// backend/frontend/devices.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();
$currentOperatorId = $user['userId'];
$currentOperatorRole = $user['role'];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $customer_id = isset($_GET['customer_id']) ? $_GET['customer_id'] : null;
    if (!$customer_id) {
        if ($currentOperatorRole === 'super_admin') {
            $query = "SELECT cd.*, c.first_name, c.last_name, c.customer_code 
                      FROM customer_devices cd 
                      JOIN customers c ON cd.customer_id = c.id 
                      ORDER BY cd.id DESC";
            $stmt = $db->prepare($query);
        } elseif ($currentOperatorRole === 'admin') {
            $query = "SELECT cd.*, c.first_name, c.last_name, c.customer_code 
                      FROM customer_devices cd 
                      JOIN customers c ON cd.customer_id = c.id 
                      JOIN operators o ON c.operator_id = o.id
                      WHERE o.parent_id = :parent_id OR c.operator_id = :parent_id
                      ORDER BY cd.id DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':parent_id', $currentOperatorId);
        } else {
            $query = "SELECT cd.*, c.first_name, c.last_name, c.customer_code 
                      FROM customer_devices cd 
                      JOIN customers c ON cd.customer_id = c.id 
                      WHERE c.operator_id = :operator_id 
                      ORDER BY cd.id DESC";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':operator_id', $currentOperatorId);
        }
    } else {
        $query = "SELECT cd.*, c.first_name, c.last_name, c.customer_code 
                  FROM customer_devices cd 
                  JOIN customers c ON cd.customer_id = c.id 
                  WHERE cd.customer_id = :customer_id 
                  ORDER BY cd.id DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
    }

    $stmt->execute();
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "devices" => $devices]);
    exit;
}

if ($method === 'POST') {
    $customer_id = isset($input['customer_id']) ? $input['customer_id'] : null;
    $device_name = isset($input['device_name']) ? trim($input['device_name']) : '';
    $device_uuid = isset($input['device_uuid']) ? trim($input['device_uuid']) : '';
    $mac_address = isset($input['mac_address']) ? trim($input['mac_address']) : '';
    $serial_number = isset($input['serial_number']) ? trim($input['serial_number']) : '';
    $android_id = isset($input['android_id']) ? trim($input['android_id']) : '';

    if (!$customer_id || empty($device_uuid)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Customer ID and Device UUID are required."]);
        exit;
    }

    // Verify hierarchy access
    if ($currentOperatorRole !== 'super_admin') {
        $checkStmt = $db->prepare("SELECT c.operator_id, o.parent_id FROM customers c JOIN operators o ON c.operator_id = o.id WHERE c.id = :id");
        $checkStmt->bindParam(':id', $customer_id);
        $checkStmt->execute();
        $cust = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$cust) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Customer not found."]);
            exit;
        }
        if ($currentOperatorRole === 'admin' && $cust['parent_id'] != $currentOperatorId && $cust['operator_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized."]);
            exit;
        }
        if ($currentOperatorRole === 'operator' && $cust['operator_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized."]);
            exit;
        }
    }

    try {
        // Check device limits
        $limitStmt = $db->prepare("SELECT COUNT(*) as count FROM customer_devices WHERE customer_id = :customer_id");
        $limitStmt->bindParam(':customer_id', $customer_id);
        $limitStmt->execute();
        $deviceCount = $limitStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $maxStmt = $db->prepare("SELECT Max_login_devices FROM customers WHERE id = :id");
        $maxStmt->bindParam(':id', $customer_id);
        $maxStmt->execute();
        $maxDevices = $maxStmt->fetch(PDO::FETCH_ASSOC)['Max_login_devices'];

        if ($deviceCount >= $maxDevices) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Device limit reached ($maxDevices devices max)."]);
            exit;
        }

        $query = "INSERT INTO customer_devices (customer_id, device_uuid, device_name, mac_address, serial_number, android_id, status, created_at) 
                  VALUES (:customer_id, :device_uuid, :device_name, :mac_address, :serial_number, :android_id, 'ACTIVE', CURRENT_TIMESTAMP)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->bindParam(':device_uuid', $device_uuid);
        $stmt->bindParam(':device_name', $device_name);
        $stmt->bindParam(':mac_address', $mac_address);
        $stmt->bindParam(':serial_number', $serial_number);
        $stmt->bindParam(':android_id', $android_id);
        $stmt->execute();

        logActivity($db, $currentOperatorId, "Register Device", "Registered device $device_name for customer ID $customer_id");

        echo json_encode(["success" => true, "message" => "Device registered successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error registering device: " . $e->getMessage()]);
    }
    exit;
}

if ($method === 'PUT') {
    $id = isset($input['id']) ? $input['id'] : null;
    $status = isset($input['status']) ? trim($input['status']) : '';

    if (!$id || empty($status)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Device ID and status are required."]);
        exit;
    }

    // Verify hierarchy access
    if ($currentOperatorRole !== 'super_admin') {
        $checkStmt = $db->prepare("SELECT c.operator_id, o.parent_id FROM customer_devices cd JOIN customers c ON cd.customer_id = c.id JOIN operators o ON c.operator_id = o.id WHERE cd.id = :id");
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        $owner = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$owner) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Device not found."]);
            exit;
        }
        if ($currentOperatorRole === 'admin' && $owner['parent_id'] != $currentOperatorId && $owner['operator_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized."]);
            exit;
        }
        if ($currentOperatorRole === 'operator' && $owner['operator_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized."]);
            exit;
        }
    }

    try {
        $stmt = $db->prepare("UPDATE customer_devices SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        logActivity($db, $currentOperatorId, "Update Device Status", "Updated device ID $id status to $status");

        echo json_encode(["success" => true, "message" => "Device status updated to $status successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error updating device status: " . $e->getMessage()]);
    }
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Device ID is required."]);
        exit;
    }

    // Role check: Only Super Admin and Admin can delete devices
    if ($currentOperatorRole !== 'super_admin' && $currentOperatorRole !== 'admin') {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Unauthorized. Only Admins can delete devices."]);
        exit;
    }

    // Verify hierarchy access to the device's customer (for Admin role)
    if ($currentOperatorRole === 'admin') {
        $checkStmt = $db->prepare("SELECT c.operator_id, o.parent_id FROM customer_devices cd JOIN customers c ON cd.customer_id = c.id JOIN operators o ON c.operator_id = o.id WHERE cd.id = :id");
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        $owner = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$owner) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Device not found."]);
            exit;
        }
        if ($owner['parent_id'] != $currentOperatorId && $owner['operator_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized."]);
            exit;
        }
    }

    try {
        $stmt = $db->prepare("DELETE FROM customer_devices WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        logActivity($db, $currentOperatorId, "Delete Device", "Deleted device ID $id");

        echo json_encode(["success" => true, "message" => "Device deleted successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error deleting device: " . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["success" => false, "message" => "Method not allowed."]);
exit;
