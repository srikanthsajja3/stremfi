<?php
// backend/frontend/customers.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();
$currentOperatorId = $user['userId'];
$currentOperatorRole = $user['role'];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($currentOperatorRole === 'super_admin') {
        $query = "SELECT c.*, o.name as operator_name 
                  FROM customers c 
                  LEFT JOIN operators o ON c.operator_id = o.id 
                  ORDER BY c.id DESC";
        $stmt = $db->prepare($query);
    } elseif ($currentOperatorRole === 'admin') {
        // Admin sees customers of their providers OR their direct customers
        $query = "SELECT c.*, o.name as operator_name 
                  FROM customers c 
                  LEFT JOIN operators o ON c.operator_id = o.id 
                  WHERE o.parent_id = :parent_id OR c.operator_id = :parent_id
                  ORDER BY c.id DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':parent_id', $currentOperatorId);
    } else {
        // Provider sees only their own customers
        $query = "SELECT c.*, :op_name as operator_name 
                  FROM customers c 
                  WHERE c.operator_id = :operator_id 
                  ORDER BY c.id DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':operator_id', $currentOperatorId);
        $stmt->bindValue(':op_name', $user['name']);
    }
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch subscriptions & devices for each customer
    foreach ($customers as &$customer) {
        $subStmt = $db->prepare("SELECT cs.*, p.plan_name, p.speed, p.data_limit 
                                 FROM customer_subscriptions cs 
                                 JOIN plans p ON cs.plan_id = p.id 
                                 WHERE cs.customer_id = :customer_id AND cs.status = 'ACTIVE' LIMIT 1");
        $subStmt->bindParam(':customer_id', $customer['id']);
        $subStmt->execute();
        $customer['active_subscription'] = $subStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $devStmt = $db->prepare("SELECT COUNT(*) as count FROM customer_devices WHERE customer_id = :customer_id");
        $devStmt->bindParam(':customer_id', $customer['id']);
        $devStmt->execute();
        $customer['device_count'] = (int)$devStmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    echo json_encode(["success" => true, "customers" => $customers]);
    exit;
}

if ($method === 'POST') {
    $operator_id = isset($input['operator_id']) ? $input['operator_id'] : null;
    
    // If not specified, default to parent, but providers always assign to themselves
    if ($currentOperatorRole === 'operator') {
        $operator_id = $currentOperatorId;
    } elseif (!$operator_id) {
        $operator_id = $currentOperatorId;
    }

    $first_name = isset($input['first_name']) ? trim($input['first_name']) : '';
    $last_name = isset($input['last_name']) ? trim($input['last_name']) : '';
    $phone_number = isset($input['phone_number']) ? trim($input['phone_number']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';
    $customer_code = isset($input['customer_code']) ? trim($input['customer_code']) : '';
    $installation_address = isset($input['installation_address']) ? trim($input['installation_address']) : '';
    $notes = isset($input['notes']) ? trim($input['notes']) : '';
    $login_devices = isset($input['login_devices']) ? (int)$input['login_devices'] : 0;
    $Max_login_devices = isset($input['Max_login_devices']) ? (int)$input['Max_login_devices'] : 4;

    if (empty($first_name) || empty($phone_number) || empty($password)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "First name, phone number, and password are required."]);
        exit;
    }

    if (empty($customer_code)) {
        $customer_code = 'CUS' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    try {
        $query = "INSERT INTO customers (operator_id, first_name, last_name, phone_number, password, customer_code, installation_address, notes, login_devices, Max_login_devices, created_at) 
                  VALUES (:operator_id, :first_name, :last_name, :phone_number, :password, :customer_code, :installation_address, :notes, :login_devices, :Max_login_devices, CURRENT_TIMESTAMP)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':operator_id', $operator_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':customer_code', $customer_code);
        $stmt->bindParam(':installation_address', $installation_address);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':login_devices', $login_devices);
        $stmt->bindParam(':Max_login_devices', $Max_login_devices);
        $stmt->execute();
        $newCustomerId = $db->lastInsertId();

        logActivity($db, $currentOperatorId, "Create Customer", "Created customer $first_name ($customer_code)");

        echo json_encode(["success" => true, "message" => "Customer created successfully.", "id" => $newCustomerId]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error creating customer: " . $e->getMessage()]);
    }
    exit;
}

if ($method === 'PUT') {
    $id = isset($input['id']) ? $input['id'] : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Customer ID is required."]);
        exit;
    }

    // Verify hierarchy access
    if ($currentOperatorRole !== 'super_admin') {
        $checkStmt = $db->prepare("SELECT c.operator_id, o.parent_id FROM customers c JOIN operators o ON c.operator_id = o.id WHERE c.id = :id");
        $checkStmt->bindParam(':id', $id);
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

    $first_name = isset($input['first_name']) ? trim($input['first_name']) : '';
    $last_name = isset($input['last_name']) ? trim($input['last_name']) : '';
    $phone_number = isset($input['phone_number']) ? trim($input['phone_number']) : '';
    $customer_code = isset($input['customer_code']) ? trim($input['customer_code']) : '';
    $installation_address = isset($input['installation_address']) ? trim($input['installation_address']) : '';
    $notes = isset($input['notes']) ? trim($input['notes']) : '';
    $Max_login_devices = isset($input['Max_login_devices']) ? (int)$input['Max_login_devices'] : 4;
    
    $passwordClause = "";
    $password = isset($input['password']) ? trim($input['password']) : '';
    if (!empty($password)) {
        $passwordClause = ", password = :password";
    }

    try {
        $query = "UPDATE customers 
                  SET first_name = :first_name, last_name = :last_name, phone_number = :phone_number,
                      customer_code = :customer_code, installation_address = :installation_address, 
                      notes = :notes, Max_login_devices = :Max_login_devices $passwordClause 
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':customer_code', $customer_code);
        $stmt->bindParam(':installation_address', $installation_address);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':Max_login_devices', $Max_login_devices);
        $stmt->bindParam(':id', $id);
        if (!empty($password)) {
            $stmt->bindParam(':password', $password);
        }
        $stmt->execute();

        logActivity($db, $currentOperatorId, "Update Customer", "Updated customer ID $id");

        echo json_encode(["success" => true, "message" => "Customer updated successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error updating customer: " . $e->getMessage()]);
    }
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Customer ID is required."]);
        exit;
    }

    // Role check: Only Super Admin can delete customers
    if ($currentOperatorRole !== 'super_admin') {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Unauthorized. Only Super Admin can delete customers."]);
        exit;
    }

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("DELETE FROM customer_devices WHERE customer_id = :customer_id");
        $stmt->bindParam(':customer_id', $id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM customer_subscriptions WHERE customer_id = :customer_id");
        $stmt->bindParam(':customer_id', $id);
        $stmt->execute();

        $stmt = $db->prepare("DELETE FROM customers WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $db->commit();
        logActivity($db, $currentOperatorId, "Delete Customer", "Deleted customer ID $id");

        echo json_encode(["success" => true, "message" => "Customer deleted successfully."]);
    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error deleting customer: " . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["success" => false, "message" => "Method not allowed."]);
exit;
