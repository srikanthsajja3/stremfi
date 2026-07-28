<?php
// backend/actors.php

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch all or single actor
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT id, name, image, actor_order, is_category, created_at FROM actors WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $actor = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$actor) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Actor not found."]);
            exit;
        }
        $actor['id'] = (int)$actor['id'];
        $actor['actor_order'] = (int)$actor['actor_order'];
        $actor['is_category'] = (int)$actor['is_category'];
        echo json_encode(["success" => true, "actor" => $actor]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT id, name, image, actor_order, is_category, created_at FROM actors ORDER BY actor_order ASC, id DESC");
        $stmt->execute();
        $actors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($actors as &$a) {
            $a['id'] = (int)$a['id'];
            $a['actor_order'] = (int)$a['actor_order'];
            $a['is_category'] = (int)$a['is_category'];
        }
        echo json_encode(["success" => true, "actors" => $actors]);
        exit;
    }
}

// POST: Create actor
if ($method === 'POST') {
    $user = requireAuth();
    $name = isset($input['name']) ? trim($input['name']) : '';
    $image = isset($input['image']) ? trim($input['image']) : '';
    $actorOrder = isset($input['actor_order']) ? (int)$input['actor_order'] : 1;
    $isCategory = isset($input['is_category']) ? (int)$input['is_category'] : 0;

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Actor name is required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO actors (name, image, actor_order, is_category) VALUES (:name, :image, :actor_order, :is_category)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':actor_order', $actorOrder);
    $stmt->bindParam(':is_category', $isCategory);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Actor created successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update actor
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid actor ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM actors WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Actor not found."]);
        exit;
    }

    $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
    $image = isset($input['image']) ? trim($input['image']) : $existing['image'];
    $actorOrder = isset($input['actor_order']) ? (int)$input['actor_order'] : (int)$existing['actor_order'];
    $isCategory = isset($input['is_category']) ? (int)$input['is_category'] : (int)$existing['is_category'];

    $updateStmt = $db->prepare("UPDATE actors SET name = :name, image = :image, actor_order = :actor_order, is_category = :is_category WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':image', $image);
    $updateStmt->bindParam(':actor_order', $actorOrder);
    $updateStmt->bindParam(':is_category', $isCategory);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Actor updated successfully."
    ]);
    exit;
}

// DELETE: Delete actor
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid actor ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM actors WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Actor deleted successfully."
    ]);
    exit;
}
