<?php
// backend/tv_channels.php

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch channels
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, name, imageUrl, channelUrl, category, language, player, channelNumber FROM tv_channels WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $ch = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$ch) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Channel not found."]);
            exit;
        }
        $ch['id'] = (int)$ch['id'];
        $ch['channelNumber'] = (int)$ch['channelNumber'];
        echo json_encode(["success" => true, "channel" => $ch]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT id, name, imageUrl, channelUrl, category, language, player, channelNumber FROM tv_channels ORDER BY channelNumber ASC, id DESC");
        $stmt->execute();
        $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($channels as &$ch) {
            $ch['id'] = (int)$ch['id'];
            $ch['channelNumber'] = (int)$ch['channelNumber'];
        }
        echo json_encode(["success" => true, "channels" => $channels]);
        exit;
    }
}

// POST: Create channel
if ($method === 'POST') {
    $user = requireAuth();

    $name = isset($input['name']) ? trim($input['name']) : '';
    $imageUrl = isset($input['imageUrl']) ? trim($input['imageUrl']) : (isset($input['image_url']) ? trim($input['image_url']) : '');
    $channelUrl = isset($input['channelUrl']) ? trim($input['channelUrl']) : (isset($input['channel_url']) ? trim($input['channel_url']) : '');
    $category = isset($input['category']) ? trim($input['category']) : 'Entertainment';
    $language = isset($input['language']) ? trim($input['language']) : 'Telugu';
    $player = isset($input['player']) ? trim($input['player']) : 'internal';
    $channelNumber = isset($input['channelNumber']) ? (int)$input['channelNumber'] : (isset($input['channel_number']) ? (int)$input['channel_number'] : 0);

    if (empty($name) || empty($channelUrl) || $channelNumber <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Name, channelUrl, and valid channelNumber are required."]);
        exit;
    }

    // Check Duplicate Channel Number
    $chkNum = $db->prepare("SELECT id FROM tv_channels WHERE channelNumber = :cnum LIMIT 1");
    $chkNum->bindParam(':cnum', $channelNumber);
    $chkNum->execute();
    if ($chkNum->fetch()) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Channel number already exists."]);
        exit;
    }

    // Check Duplicate Channel URL
    $chkUrl = $db->prepare("SELECT id FROM tv_channels WHERE channelUrl = :curl LIMIT 1");
    $chkUrl->bindParam(':curl', $channelUrl);
    $chkUrl->execute();
    if ($chkUrl->fetch()) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Channel URL already exists."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO tv_channels (name, imageUrl, channelUrl, category, language, player, channelNumber) 
                          VALUES (:name, :imageUrl, :channelUrl, :category, :language, :player, :channelNumber)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':imageUrl', $imageUrl);
    $stmt->bindParam(':channelUrl', $channelUrl);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':language', $language);
    $stmt->bindParam(':player', $player);
    $stmt->bindParam(':channelNumber', $channelNumber);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Channel created successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update channel
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Channel not found."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM tv_channels WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Channel not found."]);
        exit;
    }

    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $imageUrl = isset($input['imageUrl']) ? trim($input['imageUrl']) : (isset($input['image_url']) ? trim($input['image_url']) : $existing['imageUrl']);
    $channelUrl = isset($input['channelUrl']) ? trim($input['channelUrl']) : (isset($input['channel_url']) ? trim($input['channel_url']) : $existing['channelUrl']);
    $category = isset($input['category']) ? trim($input['category']) : $existing['category'];
    $language = isset($input['language']) ? trim($input['language']) : $existing['language'];
    $player = isset($input['player']) ? trim($input['player']) : $existing['player'];
    $channelNumber = isset($input['channelNumber']) ? (int)$input['channelNumber'] : (isset($input['channel_number']) ? (int)$input['channel_number'] : (int)$existing['channelNumber']);

    // Check Duplicate Channel Number
    $chkNum = $db->prepare("SELECT id FROM tv_channels WHERE channelNumber = :cnum AND id != :id LIMIT 1");
    $chkNum->bindParam(':cnum', $channelNumber);
    $chkNum->bindParam(':id', $id);
    $chkNum->execute();
    if ($chkNum->fetch()) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Channel number already exists."]);
        exit;
    }

    // Check Duplicate Channel URL
    $chkUrl = $db->prepare("SELECT id FROM tv_channels WHERE channelUrl = :curl AND id != :id LIMIT 1");
    $chkUrl->bindParam(':curl', $channelUrl);
    $chkUrl->bindParam(':id', $id);
    $chkUrl->execute();
    if ($chkUrl->fetch()) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Channel URL already exists."]);
        exit;
    }

    $updateStmt = $db->prepare("UPDATE tv_channels 
        SET name = :name, imageUrl = :imageUrl, channelUrl = :channelUrl, category = :category, 
            language = :language, player = :player, channelNumber = :channelNumber 
        WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':imageUrl', $imageUrl);
    $updateStmt->bindParam(':channelUrl', $channelUrl);
    $updateStmt->bindParam(':category', $category);
    $updateStmt->bindParam(':language', $language);
    $updateStmt->bindParam(':player', $player);
    $updateStmt->bindParam(':channelNumber', $channelNumber);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Channel updated successfully."
    ]);
    exit;
}

// DELETE: Delete channel
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Channel not found."]);
        exit;
    }

    $chk = $db->prepare("SELECT id FROM tv_channels WHERE id = :id LIMIT 1");
    $chk->bindParam(':id', $id);
    $chk->execute();
    if (!$chk->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Channel not found."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM tv_channels WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Channel deleted successfully."
    ]);
    exit;
}
