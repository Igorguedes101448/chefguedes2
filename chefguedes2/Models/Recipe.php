<?php
// src/Models/Recipe.php
require_once __DIR__ . '/Database.php';

class Recipe {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO recipes (user_id, title, description, ingredients, steps, prep_time, servings, category, tags, image, visibility) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['description'],
            json_encode($data['ingredients']),
            json_encode($data['steps']),
            $data['prep_time'],
            $data['servings'],
            $data['category'],
            $data['tags'],
            $data['image'] ?? null,
            $data['visibility'] ?? 'public'
        ]);
    }
    
    public function getPublicRecipes($limit = 20) {
        $sql = "SELECT r.*, u.name as author_name, 
                       COALESCE(AVG(rt.stars), 0) as avg_rating
                FROM recipes r 
                JOIN users u ON r.user_id = u.id 
                LEFT JOIN ratings rt ON r.id = rt.recipe_id
                WHERE r.visibility = 'public' 
                GROUP BY r.id
                ORDER BY r.created_at DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $sql = "SELECT r.*, u.name as author_name, 
                       COALESCE(AVG(rt.stars), 0) as avg_rating,
                       COUNT(rt.id) as rating_count
                FROM recipes r 
                JOIN users u ON r.user_id = u.id 
                LEFT JOIN ratings rt ON r.id = rt.recipe_id
                WHERE r.id = ?
                GROUP BY r.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByUserId($userId) {
        $sql = "SELECT r.*, COALESCE(AVG(rt.stars), 0) as avg_rating
                FROM recipes r 
                LEFT JOIN ratings rt ON r.id = rt.recipe_id
                WHERE r.user_id = ?
                GROUP BY r.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE recipes SET title = ?, description = ?, ingredients = ?, steps = ?, 
                prep_time = ?, servings = ?, category = ?, tags = ?, image = ?, visibility = ?, 
                updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $data['title'],
            $data['description'],
            json_encode($data['ingredients']),
            json_encode($data['steps']),
            $data['prep_time'],
            $data['servings'],
            $data['category'],
            $data['tags'],
            $data['image'],
            $data['visibility'],
            $id
        ]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM recipes WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function search($query, $filters = []) {
        $sql = "SELECT r.*, u.name as author_name, 
                       COALESCE(AVG(rt.stars), 0) as avg_rating
                FROM recipes r 
                JOIN users u ON r.user_id = u.id 
                LEFT JOIN ratings rt ON r.id = rt.recipe_id
                WHERE r.visibility = 'public'";
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (r.title LIKE ? OR r.description LIKE ? OR r.tags LIKE ?)";
            $searchParam = "%$query%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND r.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['max_time'])) {
            $sql .= " AND r.prep_time <= ?";
            $params[] = $filters['max_time'];
        }
        
        $sql .= " GROUP BY r.id ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}