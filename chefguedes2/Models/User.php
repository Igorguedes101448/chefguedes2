<?php
// src/Models/User.php
require_once __DIR__ . '/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (name, email, password, bio, preferences, avatar) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $preferences = json_encode($data['preferences'] ?? []);
        
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['bio'] ?? '',
            $preferences,
            $data['avatar'] ?? null
        ]);
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateLastActivity($userId) {
        $sql = "UPDATE users SET last_activity = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}