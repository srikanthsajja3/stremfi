<?php
// backend/frontend/allocate.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();
$currentOperatorId = $user['userId'];
$currentOperatorRole = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_operator_id = isset($input['operator_id']) ? $input['operator_id'] : null;
    $amount = isset($input['amount']) ? (float)$input['amount'] : 0.00;

    if (!$target_operator_id || $amount <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Target operator ID and positive amount are required."]);
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Fetch current operator balance
        $parentStmt = $db->prepare("SELECT wallet_balance, name FROM operators WHERE id = :id FOR UPDATE");
        $parentStmt->bindParam(':id', $currentOperatorId);
        $parentStmt->execute();
        $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);

        if (!$parent) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Operator profile not found."]);
            $db->rollBack();
            exit;
        }

        $parentBalance = (float)$parent['wallet_balance'];

        if ($currentOperatorRole !== 'super_admin' && $parentBalance < $amount) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Insufficient wallet balance (Required: ₹$amount, Current: ₹$parentBalance)."]);
            $db->rollBack();
            exit;
        }

        // 2. Fetch child operator balance and verify parent relationship
        $childStmt = $db->prepare("SELECT wallet_balance, name, parent_id FROM operators WHERE id = :id FOR UPDATE");
        $childStmt->bindParam(':id', $target_operator_id);
        $childStmt->execute();
        $child = $childStmt->fetch(PDO::FETCH_ASSOC);

        if (!$child) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Target operator not found."]);
            $db->rollBack();
            exit;
        }

        if ($currentOperatorRole !== 'super_admin' && $child['parent_id'] != $currentOperatorId) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "You are not authorized to allocate balance to this operator."]);
            $db->rollBack();
            exit;
        }

        $childBalance = (float)$child['wallet_balance'];

        // 3. Deduct from parent
        $newParentBalance = $parentBalance - $amount;
        $updParent = $db->prepare("UPDATE operators SET wallet_balance = :balance WHERE id = :id");
        $updParent->bindParam(':balance', $newParentBalance);
        $updParent->bindParam(':id', $currentOperatorId);
        $updParent->execute();

        // 4. Add to child
        $newChildBalance = $childBalance + $amount;
        $updChild = $db->prepare("UPDATE operators SET wallet_balance = :balance WHERE id = :id");
        $updChild->bindParam(':balance', $newChildBalance);
        $updChild->bindParam(':id', $target_operator_id);
        $updChild->execute();

        // 5. Create transaction log for parent (DEBIT)
        $txn_id_p = strtoupper(substr('TRF' . uniqid() . rand(10,99), 0, 16));
        $remarks_p = "Transferred ₹" . number_format($amount, 2) . " to operator " . $child['name'] . " (ID: $target_operator_id)";
        $txnPStmt = $db->prepare("INSERT INTO wallet_transactions (transaction_id, operator_id, transaction_type, amount, balance_before, balance_after, remarks, created_by, created_at) 
                                 VALUES (:txn_id, :op_id, 'DEBIT', :amount, :before, :after, :remarks, :by, CURRENT_TIMESTAMP)");
        $txnPStmt->bindParam(':txn_id', $txn_id_p);
        $txnPStmt->bindParam(':op_id', $currentOperatorId);
        $txnPStmt->bindParam(':amount', $amount);
        $txnPStmt->bindParam(':before', $parentBalance);
        $txnPStmt->bindParam(':after', $newParentBalance);
        $txnPStmt->bindParam(':remarks', $remarks_p);
        $txnPStmt->bindParam(':by', $currentOperatorId);
        $txnPStmt->execute();

        // 6. Create transaction log for child (CREDIT)
        $txn_id_c = strtoupper(substr('REC' . uniqid() . rand(10,99), 0, 16));
        $remarks_c = "Received ₹" . number_format($amount, 2) . " from parent " . $parent['name'];
        $txnCStmt = $db->prepare("INSERT INTO wallet_transactions (transaction_id, operator_id, transaction_type, amount, balance_before, balance_after, remarks, created_by, created_at) 
                                 VALUES (:txn_id, :op_id, 'CREDIT', :amount, :before, :after, :remarks, :by, CURRENT_TIMESTAMP)");
        $txnCStmt->bindParam(':txn_id', $txn_id_c);
        $txnCStmt->bindParam(':op_id', $target_operator_id);
        $txnCStmt->bindParam(':amount', $amount);
        $txnCStmt->bindParam(':before', $childBalance);
        $txnCStmt->bindParam(':after', $newChildBalance);
        $txnCStmt->bindParam(':remarks', $remarks_c);
        $txnCStmt->bindParam(':by', $currentOperatorId);
        $txnCStmt->execute();

        $db->commit();

        logActivity($db, $currentOperatorId, "Wallet Allocation", "Allocated ₹$amount to operator ID $target_operator_id (" . $child['name'] . ")");

        echo json_encode(["success" => true, "message" => "Transferred ₹" . number_format($amount, 2) . " successfully."]);

    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error allocating wallet funds: " . $e->getMessage()]);
    }
    exit;
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}
