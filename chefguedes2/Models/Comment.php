<?php
// src/Models/Comment.php
require_once __DIR__ . '/Database.php';

class Comment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO comments (recipe_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['recipe_id'],
            $data['user_id'],
            $data['parent_id'] ?? null,
            $data['content']
        ]);
    }
    
    public function getByRecipeId($recipeId) {
        $sql = "SELECT c.*, u.name as author_name, u.avatar as author_avatar
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.recipe_id = ? AND c.is_reported = 0
                ORDER BY c.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM comments WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function report($id) {
        $sql = "UPDATE comments SET is_reported = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function findById($id) {
        $sql = "SELECT c.*, u.name as author_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getReported() {
        $sql = "SELECT c.*, u.name as author_name, r.title as recipe_title
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                JOIN recipes r ON c.recipe_id = r.id
                WHERE c.is_reported = 1 
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}