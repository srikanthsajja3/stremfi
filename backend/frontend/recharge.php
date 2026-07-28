<?php
// backend/frontend/recharge.php

require_once __DIR__ . '/../bootstrap.php';

$user = requireAuth();
$currentOperatorId = $user['userId'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = isset($input['customer_id']) ? $input['customer_id'] : null;
    $plan_id = isset($input['plan_id']) ? $input['plan_id'] : null;
    $payment_mode = isset($input['payment_mode']) ? trim($input['payment_mode']) : 'WALLET';

    if (!$customer_id || !$plan_id) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Customer ID and Plan ID are required."]);
        exit;
    }

    try {
        // Verify customer exists first
        $custCheck = $db->prepare("SELECT id FROM customers WHERE id = :id LIMIT 1");
        $custCheck->bindParam(':id', $customer_id);
        $custCheck->execute();
        if (!$custCheck->fetch()) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Customer not found."]);
            exit;
        }

        $db->beginTransaction();

        $planStmt = $db->prepare("SELECT price, validity_days, plan_name FROM plans WHERE id = :id LIMIT 1");
        $planStmt->bindParam(':id', $plan_id);
        $planStmt->execute();
        $plan = $planStmt->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Plan not found."]);
            $db->rollBack();
            exit;
        }

        $price = (float)$plan['price'];
        $validity_days = (int)$plan['validity_days'];

        if ($payment_mode === 'WALLET') {
            $opStmt = $db->prepare("SELECT wallet_balance FROM operators WHERE id = :id FOR UPDATE");
            $opStmt->bindParam(':id', $currentOperatorId);
            $opStmt->execute();
            $operator = $opStmt->fetch(PDO::FETCH_ASSOC);

            $balance = (float)$operator['wallet_balance'];
            if ($balance < $price) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Insufficient wallet balance (Required: ₹$price, Current: ₹$balance)."]);
                $db->rollBack();
                exit;
            }

            $newBalance = $balance - $price;
            $updateOpStmt = $db->prepare("UPDATE operators SET wallet_balance = :new_balance WHERE id = :id");
            $updateOpStmt->bindParam(':new_balance', $newBalance);
            $updateOpStmt->bindParam(':id', $currentOperatorId);
            $updateOpStmt->execute();

            $txn_id = strtoupper(substr('TXN' . uniqid() . rand(10,99), 0, 16));
            
            $txnQuery = "INSERT INTO wallet_transactions (transaction_id, operator_id, transaction_type, amount, balance_before, balance_after, remarks, created_by, created_at) 
                         VALUES (:transaction_id, :operator_id, 'DEBIT', :amount, :balance_before, :balance_after, :remarks, :created_by, CURRENT_TIMESTAMP)";
            $txnStmt = $db->prepare($txnQuery);
            $txnStmt->bindParam(':transaction_id', $txn_id);
            $txnStmt->bindParam(':operator_id', $currentOperatorId);
            $txnStmt->bindParam(':amount', $price);
            $txnStmt->bindParam(':balance_before', $balance);
            $txnStmt->bindParam(':balance_after', $newBalance);
            $remarks = "Recharged Customer ID $customer_id for Plan: " . $plan['plan_name'];
            $txnStmt->bindParam(':remarks', $remarks);
            $txnStmt->bindParam(':created_by', $currentOperatorId);
            $txnStmt->execute();
        } else {
            $txn_id = strtoupper(substr('EXT' . uniqid() . rand(10,99), 0, 16));
        }

        $subStmt = $db->prepare("SELECT id, expiry_date FROM customer_subscriptions WHERE customer_id = :customer_id AND status = 'ACTIVE' LIMIT 1");
        $subStmt->bindParam(':customer_id', $customer_id);
        $subStmt->execute();
        $existingSub = $subStmt->fetch(PDO::FETCH_ASSOC);

        $activation_date = date('Y-m-d');
        if ($existingSub) {
            $expiry_timestamp = strtotime($existingSub['expiry_date']);
            $start_timestamp = ($expiry_timestamp > time()) ? $expiry_timestamp : time();
            $activation_date = date('Y-m-d', $start_timestamp);
            $expiry_date = date('Y-m-d', strtotime("+$validity_days days", $start_timestamp));
            
            $deactStmt = $db->prepare("UPDATE customer_subscriptions SET status = 'EXPIRED' WHERE customer_id = :customer_id");
            $deactStmt->bindParam(':customer_id', $customer_id);
            $deactStmt->execute();
        } else {
            $expiry_date = date('Y-m-d', strtotime("+$validity_days days"));
        }

        $newSubQuery = "INSERT INTO customer_subscriptions (customer_id, plan_id, activated_by, activation_date, expiry_date, status, auto_renew, created_at) 
                        VALUES (:customer_id, :plan_id, :activated_by, :activation_date, :expiry_date, 'ACTIVE', 0, CURRENT_TIMESTAMP)";
        $newSubStmt = $db->prepare($newSubQuery);
        $newSubStmt->bindParam(':customer_id', $customer_id);
        $newSubStmt->bindParam(':plan_id', $plan_id);
        $newSubStmt->bindParam(':activated_by', $currentOperatorId);
        $newSubStmt->bindParam(':activation_date', $activation_date);
        $newSubStmt->bindParam(':expiry_date', $expiry_date);
        $newSubStmt->execute();
        $newSubId = $db->lastInsertId();

        $histQuery = "INSERT INTO recharge_history (customer_id, subscription_id, plan_id, amount, payment_mode, recharge_type, recharged_by, recharge_date) 
                      VALUES (:customer_id, :subscription_id, :plan_id, :amount, :payment_mode, :recharge_type, :recharged_by, CURRENT_TIMESTAMP)";
        $histStmt = $db->prepare($histQuery);
        $histStmt->bindParam(':customer_id', $customer_id);
        $histStmt->bindParam(':subscription_id', $newSubId);
        $histStmt->bindParam(':plan_id', $plan_id);
        $histStmt->bindParam(':amount', $price);
        $histStmt->bindParam(':payment_mode', $payment_mode);
        $recharge_type = $existingSub ? 'RENEWAL' : 'NEW';
        $histStmt->bindParam(':recharge_type', $recharge_type);
        $histStmt->bindParam(':recharged_by', $currentOperatorId);
        $histStmt->execute();

        $db->commit();

        logActivity($db, $currentOperatorId, "Recharge", "Recharged Customer ID $customer_id with Plan: " . $plan['plan_name']);

        echo json_encode([
            "success" => true,
            "message" => "Recharge successful. Subscription activated until $expiry_date.",
            "transaction_id" => $txn_id,
            "expiry_date" => $expiry_date
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error performing recharge: " . $e->getMessage()]);
    }
    exit;
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}
