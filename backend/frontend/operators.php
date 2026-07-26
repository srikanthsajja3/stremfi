<?php
// backend/frontend/operators.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();
$currentOperatorId = $user['userId'];
$currentOperatorRole = $user['role'];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($currentOperatorRole === 'super_admin') {
        // Super Admin gets all Admins and Operators, showing parent name
        $query = "SELECT o1.id, o1.parent_id, o1.role, o1.name, o1.mobile, o1.email, o1.wallet_balance, o1.company_name, o1.is_active, o1.created_at, o2.name as parent_name 
                  FROM operators o1 
                  LEFT JOIN operators o2 ON o1.parent_id = o2.id 
                  WHERE o1.role IN ('admin', 'operator') AND o1.id != :super_id
                  ORDER BY o1.id DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':super_id', $currentOperatorId);
    } elseif ($currentOperatorRole === 'admin') {
        // Admin manages Providers (role operator)
        $query = "SELECT id, parent_id, role, name, mobile, email, wallet_balance, company_name, is_active, created_at 
                  FROM operators WHERE role = 'operator' AND parent_id = :parent_id ORDER BY id DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':parent_id', $currentOperatorId);
    } else {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Unauthorized access."]);
        exit;
    }

    $stmt->execute();
    $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "operators" => $operators]);
    exit;
}

if ($method === 'POST') {
    $name = isset($input['name']) ? trim($input['name']) : '';
    $mobile = isset($input['mobile']) ? trim($input['mobile']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';
    $company_name = isset($input['company_name']) ? trim($input['company_name']) : '';
    $wallet_balance = isset($input['wallet_balance']) ? (float)$input['wallet_balance'] : 0.00;

    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Name, email, and password are required."]);
        exit;
    }

    // Determine target role and parent based on hierarchy
    if ($currentOperatorRole === 'super_admin') {
        $role = isset($input['role']) ? trim($input['role']) : 'admin';
        if ($role === 'operator') {
            $parent_id = isset($input['parent_id']) ? (int)$input['parent_id'] : $currentOperatorId;
        } else {
            $parent_id = $currentOperatorId;
        }
    } elseif ($currentOperatorRole === 'admin') {
        $role = 'operator'; // Admin creates operators
        $parent_id = $currentOperatorId;
    } else {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Unauthorized."]);
        exit;
    }

    try {
        $query = "INSERT INTO operators (parent_id, role, name, mobile, email, password, wallet_balance, company_name, is_active, created_at) 
                  VALUES (:parent_id, :role, :name, :mobile, :email, :password, :wallet_balance, :company_name, 1, CURRENT_TIMESTAMP)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':wallet_balance', $wallet_balance);
        $stmt->bindParam(':company_name', $company_name);
        $stmt->execute();

        logActivity($db, $currentOperatorId, "Create Operator", "Created operator $name ($role)");

        echo json_encode(["success" => true, "message" => "Operator created successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error creating operator: " . $e->getMessage()]);
    }
    exit;
}

if ($method === 'PUT') {
    $id = isset($input['id']) ? $input['id'] : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "ID is required."]);
        exit;
    }

    // Verification: ensure the target is indeed managed by the logged-in operator
    if ($currentOperatorRole !== 'super_admin') {
        $checkStmt = $db->prepare("SELECT parent_id FROM operators WHERE id = :id");
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        $target = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$target || $target['parent_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized modification."]);
            exit;
        }
    }

    $name = isset($input['name']) ? trim($input['name']) : '';
    $mobile = isset($input['mobile']) ? trim($input['mobile']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $company_name = isset($input['company_name']) ? trim($input['company_name']) : '';
    $is_active = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    $passwordClause = "";
    $password = isset($input['password']) ? trim($input['password']) : '';
    if (!empty($password)) {
        $passwordClause = ", password = :password";
    }

    try {
        $query = "UPDATE operators 
                  SET name = :name, mobile = :mobile, email = :email, company_name = :company_name, is_active = :is_active $passwordClause 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':id', $id);
        if (!empty($password)) {
            $stmt->bindParam(':password', $password);
        }
        $stmt->execute();

        logActivity($db, $currentOperatorId, "Update Operator", "Updated operator ID $id");

        echo json_encode(["success" => true, "message" => "Operator updated successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error updating operator: " . $e->getMessage()]);
    }
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "ID is required."]);
        exit;
    }

    // Verification
    if ($currentOperatorRole !== 'super_admin') {
        $checkStmt = $db->prepare("SELECT parent_id FROM operators WHERE id = :id");
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        $target = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$target || $target['parent_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Unauthorized delete."]);
            exit;
        }
    }

    try {
        $stmt = $db->prepare("DELETE FROM operators WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        logActivity($db, $currentOperatorId, "Delete Operator", "Deleted operator ID $id");

        echo json_encode(["success" => true, "message" => "Operator deleted successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Cannot delete operator. Make sure there are no depending child tables."]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["success" => false, "message" => "Method not allowed."]);
exit;
