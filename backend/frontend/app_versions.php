<?php
// backend/app_versions.php

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch app versions
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT * FROM app_versions WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $version = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$version) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "App version not found."]);
            exit;
        }
        // Format integer types for force_update and version_code
        $version['id'] = (int)$version['id'];
        $version['version_code'] = (int)$version['version_code'];
        $version['force_update'] = (int)$version['force_update'];
        echo json_encode(["success" => true, "app_version" => $version]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT * FROM app_versions ORDER BY id DESC");
        $stmt->execute();
        $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($versions as &$v) {
            $v['id'] = (int)$v['id'];
            $v['version_code'] = (int)$v['version_code'];
            $v['force_update'] = (int)$v['force_update'];
        }
        echo json_encode(["success" => true, "app_versions" => $versions]);
        exit;
    }
}

// PUT: Update app version
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid version ID is required for update."]);
        exit;
    }

    // Fetch existing version record
    $stmt = $db->prepare("SELECT * FROM app_versions WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "App version record not found."]);
        exit;
    }

    $appName = isset($input['app_name']) ? trim($input['app_name']) : $existing['app_name'];
    $platform = isset($input['platform']) ? trim($input['platform']) : $existing['platform'];
    $versionCode = isset($input['version_code']) ? (int)$input['version_code'] : (int)$existing['version_code'];
    $versionName = isset($input['version_name']) ? trim($input['version_name']) : $existing['version_name'];
    $forceUpdate = isset($input['force_update']) ? (int)$input['force_update'] : (int)$existing['force_update'];
    $updateMessage = isset($input['update_message']) ? trim($input['update_message']) : $existing['update_message'];
    $apkUrl = isset($input['apk_url']) ? trim($input['apk_url']) : $existing['apk_url'];
    $playstoreUrl = isset($input['playstore_url']) ? trim($input['playstore_url']) : $existing['playstore_url'];

    $updateStmt = $db->prepare("UPDATE app_versions 
        SET app_name = :app_name, platform = :platform, version_code = :version_code, version_name = :version_name, 
            force_update = :force_update, update_message = :update_message, apk_url = :apk_url, playstore_url = :playstore_url 
        WHERE id = :id");
    $updateStmt->bindParam(':app_name', $appName);
    $updateStmt->bindParam(':platform', $platform);
    $updateStmt->bindParam(':version_code', $versionCode);
    $updateStmt->bindParam(':version_name', $versionName);
    $updateStmt->bindParam(':force_update', $forceUpdate);
    $updateStmt->bindParam(':update_message', $updateMessage);
    $updateStmt->bindParam(':apk_url', $apkUrl);
    $updateStmt->bindParam(':playstore_url', $playstoreUrl);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "App version updated successfully."
    ]);
    exit;
}

// POST: Create new app version
if ($method === 'POST') {
    $user = requireAuth();
    $appName = isset($input['app_name']) ? trim($input['app_name']) : 'launcher';
    $platform = isset($input['platform']) ? trim($input['platform']) : 'android_tv';
    $versionCode = isset($input['version_code']) ? (int)$input['version_code'] : 100;
    $versionName = isset($input['version_name']) ? trim($input['version_name']) : '1.0.0';
    $forceUpdate = isset($input['force_update']) ? (int)$input['force_update'] : 0;
    $updateMessage = isset($input['update_message']) ? trim($input['update_message']) : '';
    $apkUrl = isset($input['apk_url']) ? trim($input['apk_url']) : '';
    $playstoreUrl = isset($input['playstore_url']) ? trim($input['playstore_url']) : '';

    $stmt = $db->prepare("INSERT INTO app_versions (app_name, platform, version_code, version_name, force_update, update_message, apk_url, playstore_url) 
                          VALUES (:app_name, :platform, :version_code, :version_name, :force_update, :update_message, :apk_url, :playstore_url)");
    $stmt->bindParam(':app_name', $appName);
    $stmt->bindParam(':platform', $platform);
    $stmt->bindParam(':version_code', $versionCode);
    $stmt->bindParam(':version_name', $versionName);
    $stmt->bindParam(':force_update', $forceUpdate);
    $stmt->bindParam(':update_message', $updateMessage);
    $stmt->bindParam(':apk_url', $apkUrl);
    $stmt->bindParam(':playstore_url', $playstoreUrl);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "App version created successfully.",
        "id" => (int)$db->lastInsertId()
    ]);
    exit;
}

// DELETE: Delete app version
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "App version ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM app_versions WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "App version deleted successfully."]);
    exit;
}
