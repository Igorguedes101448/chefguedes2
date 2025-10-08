<?php
// src/Controllers/RecipeController.php
require_once __DIR__ . '/../Models/Recipe.php';
require_once __DIR__ . '/../Models/User.php';

class RecipeController {
    private $recipeModel;
    private $userModel;
    
    public function __construct() {
        $this->recipeModel = new Recipe();
        $this->userModel = new User();
    }
    
    public function create($data) {
        // Validate user is logged in
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Deve estar logado para criar receitas');
        }
        
        // Validate required fields
        if (empty($data['title']) || empty($data['description'])) {
            throw new Exception('Título e descrição são obrigatórios');
        }
        
        if (empty($data['ingredients']) || empty($data['steps'])) {
            throw new Exception('Ingredientes e passos são obrigatórios');
        }
        
        $data['user_id'] = $_SESSION['user_id'];
        
        return $this->recipeModel->create($data);
    }
    
    public function getById($id) {
        $recipe = $this->recipeModel->findById($id);
        
        if (!$recipe) {
            throw new Exception('Receita não encontrada');
        }
        
        // Check visibility permissions
        if ($recipe['visibility'] === 'private' && 
            (!isset($_SESSION['user_id']) || 
             ($_SESSION['user_id'] != $recipe['user_id'] && $_SESSION['role'] !== 'admin'))) {
            throw new Exception('Receita privada');
        }
        
        return $recipe;
    }
    
    public function update($id, $data) {
        $recipe = $this->recipeModel->findById($id);
        
        if (!$recipe) {
            throw new Exception('Receita não encontrada');
        }
        
        // Check permissions
        if (!isset($_SESSION['user_id']) || 
            ($_SESSION['user_id'] != $recipe['user_id'] && $_SESSION['role'] !== 'admin')) {
            throw new Exception('Não tem permissão para editar esta receita');
        }
        
        return $this->recipeModel->update($id, $data);
    }
    
    public function delete($id) {
        $recipe = $this->recipeModel->findById($id);
        
        if (!$recipe) {
            throw new Exception('Receita não encontrada');
        }
        
        // Check permissions
        if (!isset($_SESSION['user_id']) || 
            ($_SESSION['user_id'] != $recipe['user_id'] && $_SESSION['role'] !== 'admin')) {
            throw new Exception('Não tem permissão para eliminar esta receita');
        }
        
        return $this->recipeModel->delete($id);
    }
    
    public function search($query, $filters = []) {
        return $this->recipeModel->search($query, $filters);
    }
    
    public function handleImageUpload($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de ficheiro não permitido');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('Ficheiro muito grande (máx. 5MB)');
        }
        
    $uploadDir = __DIR__ . '/../uploads/recipes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Erro ao fazer upload da imagem');
        }
        
        return 'recipes/' . $filename;
    }
}