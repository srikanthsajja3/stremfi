<?php
// backend/app_store.php

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch app store apps
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT * FROM app_store WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$app) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "App not found."]);
            exit;
        }
        $app['id'] = (int)$app['id'];
        $app['is_active'] = (int)$app['is_active'];
        echo json_encode(["success" => true, "app" => $app]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT * FROM app_store ORDER BY id DESC");
        $stmt->execute();
        $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($apps as &$a) {
            $a['id'] = (int)$a['id'];
            $a['is_active'] = (int)$a['is_active'];
        }
        echo json_encode(["success" => true, "apps" => $apps]);
        exit;
    }
}

// POST: Add new app
if ($method === 'POST') {
    $user = requireAuth();
    $name = isset($input['name']) ? trim($input['name']) : '';
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : '';
    $packageName = isset($input['package_name']) ? trim($input['package_name']) : '';
    $playStoreId = isset($input['play_store_id']) ? trim($input['play_store_id']) : '';
    $amazonAppId = isset($input['amazon_app_id']) ? trim($input['amazon_app_id']) : '';
    $apkUrl = isset($input['apk_url']) ? trim($input['apk_url']) : '';
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if (empty($name) || empty($packageName)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "App name and package_name are required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO app_store (name, image_url, package_name, play_store_id, amazon_app_id, apk_url, is_active) 
                          VALUES (:name, :image_url, :package_name, :play_store_id, :amazon_app_id, :apk_url, :is_active)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':image_url', $imageUrl);
    $stmt->bindParam(':package_name', $packageName);
    $stmt->bindParam(':play_store_id', $playStoreId);
    $stmt->bindParam(':amazon_app_id', $amazonAppId);
    $stmt->bindParam(':apk_url', $apkUrl);
    $stmt->bindParam(':is_active', $isActive);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "App added successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update app
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid app ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM app_store WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "App not found."]);
        exit;
    }

    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : $existing['image_url'];
    $packageName = isset($input['package_name']) ? trim($input['package_name']) : $existing['package_name'];
    $playStoreId = isset($input['play_store_id']) ? trim($input['play_store_id']) : $existing['play_store_id'];
    $amazonAppId = isset($input['amazon_app_id']) ? trim($input['amazon_app_id']) : $existing['amazon_app_id'];
    $apkUrl = isset($input['apk_url']) ? trim($input['apk_url']) : $existing['apk_url'];
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : (int)$existing['is_active'];

    $updateStmt = $db->prepare("UPDATE app_store 
        SET name = :name, image_url = :image_url, package_name = :package_name, 
            play_store_id = :play_store_id, amazon_app_id = :amazon_app_id, apk_url = :apk_url, is_active = :is_active 
        WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':image_url', $imageUrl);
    $updateStmt->bindParam(':package_name', $packageName);
    $updateStmt->bindParam(':play_store_id', $playStoreId);
    $updateStmt->bindParam(':amazon_app_id', $amazonAppId);
    $updateStmt->bindParam(':apk_url', $apkUrl);
    $updateStmt->bindParam(':is_active', $isActive);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "App updated successfully."
    ]);
    exit;
}

// DELETE: Delete app
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid app ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM app_store WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "App deleted successfully."
    ]);
    exit;
}
