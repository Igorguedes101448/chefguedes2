<?php
// src/Models/Rating.php
require_once __DIR__ . '/Database.php';

class Rating {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        // Check if user already rated this recipe
        if ($this->getUserRating($data['recipe_id'], $data['user_id'])) {
            // Update existing rating
            return $this->update($data['recipe_id'], $data['user_id'], $data['stars']);
        }
        
        $sql = "INSERT INTO ratings (recipe_id, user_id, stars) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['recipe_id'],
            $data['user_id'],
            $data['stars']
        ]);
    }
    
    public function update($recipeId, $userId, $stars) {
        $sql = "UPDATE ratings SET stars = ?, created_at = NOW() WHERE recipe_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$stars, $recipeId, $userId]);
    }
    
    public function getUserRating($recipeId, $userId) {
        $sql = "SELECT * FROM ratings WHERE recipe_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId, $userId]);
        return $stmt->fetch();
    }
    
    public function getRecipeRating($recipeId) {
        $sql = "SELECT AVG(stars) as avg_rating, COUNT(*) as total_ratings FROM ratings WHERE recipe_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId]);
        return $stmt->fetch();
    }
    
    public function getByRecipeId($recipeId) {
        $sql = "SELECT r.*, u.name as author_name 
                FROM ratings r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.recipe_id = ? 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }
}