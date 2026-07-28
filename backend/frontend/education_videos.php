<?php
// backend/education_videos.php

require_once __DIR__ . '/../bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch education videos
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id > 0) {
        $stmt = $db->prepare("SELECT v.id, v.category_id, c.name as category_name, v.subject_id, s.name as subject_name, 
                                     v.title, v.image_url, v.video_url, v.video_type, v.duration, v.description, v.is_active, v.created_at 
                              FROM education_videos v 
                              LEFT JOIN education_categories c ON v.category_id = c.id 
                              LEFT JOIN education_subjects s ON v.subject_id = s.id 
                              WHERE v.id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $video = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$video) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Education video not found."]);
            exit;
        }
        $video['id'] = (int)$video['id'];
        $video['category_id'] = (int)$video['category_id'];
        $video['subject_id'] = $video['subject_id'] !== null ? (int)$video['subject_id'] : null;
        $video['is_active'] = (int)$video['is_active'];
        echo json_encode(["success" => true, "video" => $video]);
        exit;
    } else {
        $stmt = $db->prepare("SELECT v.id, v.category_id, c.name as category_name, v.subject_id, s.name as subject_name, 
                                     v.title, v.image_url, v.video_url, v.video_type, v.duration, v.description, v.is_active, v.created_at 
                              FROM education_videos v 
                              LEFT JOIN education_categories c ON v.category_id = c.id 
                              LEFT JOIN education_subjects s ON v.subject_id = s.id 
                              ORDER BY v.id DESC");
        $stmt->execute();
        $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($videos as &$v) {
            $v['id'] = (int)$v['id'];
            $v['category_id'] = (int)$v['category_id'];
            $v['subject_id'] = $v['subject_id'] !== null ? (int)$v['subject_id'] : null;
            $v['is_active'] = (int)$v['is_active'];
        }
        echo json_encode(["success" => true, "videos" => $videos]);
        exit;
    }
}

// POST: Create video
if ($method === 'POST') {
    $user = requireAuth();
    $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : 0;
    $subjectId = (isset($input['subject_id']) && $input['subject_id'] !== null && $input['subject_id'] !== '') ? (int)$input['subject_id'] : null;
    $title = isset($input['title']) ? trim($input['title']) : '';
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : '';
    $videoUrl = isset($input['video_url']) ? trim($input['video_url']) : '';
    $videoType = isset($input['video_type']) ? trim($input['video_type']) : 'youtube';
    $duration = isset($input['duration']) ? trim($input['duration']) : '';
    $description = isset($input['description']) ? trim($input['description']) : '';
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

    if ($categoryId <= 0 || empty($title) || empty($videoUrl)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "category_id, title, and video_url are required."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO education_videos (category_id, subject_id, title, image_url, video_url, video_type, duration, description, is_active) 
                          VALUES (:category_id, :subject_id, :title, :image_url, :video_url, :video_type, :duration, :description, :is_active)");
    $stmt->bindParam(':category_id', $categoryId);
    $stmt->bindValue(':subject_id', $subjectId, $subjectId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':image_url', $imageUrl);
    $stmt->bindParam(':video_url', $videoUrl);
    $stmt->bindParam(':video_type', $videoType);
    $stmt->bindParam(':duration', $duration);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':is_active', $isActive);
    $stmt->execute();

    $newId = (int)$db->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Education video created successfully.",
        "id" => $newId
    ]);
    exit;
}

// PUT: Update video
if ($method === 'PUT') {
    $user = requireAuth();
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid video ID is required."]);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM education_videos WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Education video not found."]);
        exit;
    }

    $categoryId = isset($input['category_id']) ? (int)$input['category_id'] : (int)$existing['category_id'];
    $subjectId = array_key_exists('subject_id', $input) ? ($input['subject_id'] !== null && $input['subject_id'] !== '' ? (int)$input['subject_id'] : null) : ($existing['subject_id'] !== null ? (int)$existing['subject_id'] : null);
    $title = isset($input['title']) ? trim($input['title']) : $existing['title'];
    $imageUrl = isset($input['image_url']) ? trim($input['image_url']) : $existing['image_url'];
    $videoUrl = isset($input['video_url']) ? trim($input['video_url']) : $existing['video_url'];
    $videoType = isset($input['video_type']) ? trim($input['video_type']) : $existing['video_type'];
    $duration = isset($input['duration']) ? trim($input['duration']) : $existing['duration'];
    $description = isset($input['description']) ? trim($input['description']) : $existing['description'];
    $isActive = isset($input['is_active']) ? (int)$input['is_active'] : (int)$existing['is_active'];

    $updateStmt = $db->prepare("UPDATE education_videos 
        SET category_id = :category_id, subject_id = :subject_id, title = :title, 
            image_url = :image_url, video_url = :video_url, video_type = :video_type, 
            duration = :duration, description = :description, is_active = :is_active 
        WHERE id = :id");
    $updateStmt->bindParam(':category_id', $categoryId);
    $updateStmt->bindValue(':subject_id', $subjectId, $subjectId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $updateStmt->bindParam(':title', $title);
    $updateStmt->bindParam(':image_url', $imageUrl);
    $updateStmt->bindParam(':video_url', $videoUrl);
    $updateStmt->bindParam(':video_type', $videoType);
    $updateStmt->bindParam(':duration', $duration);
    $updateStmt->bindParam(':description', $description);
    $updateStmt->bindParam(':is_active', $isActive);
    $updateStmt->bindParam(':id', $id);
    $updateStmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Education video updated successfully."
    ]);
    exit;
}

// DELETE: Delete video
if ($method === 'DELETE') {
    $user = requireAuth();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($input['id']) ? (int)$input['id'] : 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Valid video ID is required."]);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM education_videos WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Education video deleted successfully."
    ]);
    exit;
}
