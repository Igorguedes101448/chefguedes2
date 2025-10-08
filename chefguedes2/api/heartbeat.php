<?php
// public/api/heartbeat.php
session_start();
require_once __DIR__ . '/../Models/User.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not logged in');
    }
    
    $userModel = new User();
    $userModel->updateLastActivity($_SESSION['user_id']);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}