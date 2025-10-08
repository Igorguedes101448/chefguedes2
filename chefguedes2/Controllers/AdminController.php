<?php
// src/Controllers/AdminController.php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Recipe.php';
require_once __DIR__ . '/../Models/Comment.php';

class AdminController {
    private $userModel;
    private $recipeModel;
    private $commentModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->recipeModel = new Recipe();
        $this->commentModel = new Comment();
    }
    
    public function checkAdminAccess() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            throw new Exception('Acesso negado - apenas administradores');
        }
    }
    
    public function getDashboardStats() {
        $this->checkAdminAccess();
        
        $sql = "SELECT 
                   (SELECT COUNT(*) FROM users WHERE is_active = 1) as total_users,
                   (SELECT COUNT(*) FROM recipes WHERE visibility = 'public') as total_recipes,
                   (SELECT COUNT(*) FROM comments WHERE is_reported = 0) as total_comments,
                   (SELECT COUNT(*) FROM comments WHERE is_reported = 1) as reported_comments";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query($sql);
        return $stmt->fetch();
    }
    
    public function getUsers($limit = 50) {
        $this->checkAdminAccess();
        
        $sql = "SELECT id, name, email, role, created_at, last_activity, is_active 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getAllRecipes($limit = 50) {
        $this->checkAdminAccess();
        
        $sql = "SELECT r.*, u.name as author_name 
                FROM recipes r 
                JOIN users u ON r.user_id = u.id 
                ORDER BY r.created_at DESC 
                LIMIT ?";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getReportedComments() {
        $this->checkAdminAccess();
        return $this->commentModel->getReported();
    }
    
    public function toggleUserStatus($userId, $status) {
        $this->checkAdminAccess();
        
        $sql = "UPDATE users SET is_active = ? WHERE id = ?";
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        return $stmt->execute([$status, $userId]);
    }
    
    public function promoteUser($userId, $role) {
        $this->checkAdminAccess();
        
        if (!in_array($role, ['user', 'admin'])) {
            throw new Exception('Papel inválido');
        }
        
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        return $stmt->execute([$role, $userId]);
    }
    
    public function deleteRecipe($recipeId) {
        $this->checkAdminAccess();
        return $this->recipeModel->delete($recipeId);
    }
    
    public function resolveReportedComment($commentId, $action) {
        $this->checkAdminAccess();
        
        if ($action === 'delete') {
            return $this->commentModel->delete($commentId);
        } elseif ($action === 'ignore') {
            $sql = "UPDATE comments SET is_reported = 0 WHERE id = ?";
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare($sql);
            return $stmt->execute([$commentId]);
        }
        
        return false;
    }
}