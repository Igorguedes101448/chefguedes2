<?php
// public/api/comments.php
session_start();
require_once __DIR__ . '/../Models/Comment.php';
require_once __DIR__ . '/../Utils/CSRF.php';

header('Content-Type: application/json');

try {
    $commentModel = new Comment();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create comment
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Deve estar logado para comentar');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!CSRF::validateToken($input['csrf_token'] ?? '')) {
            throw new Exception('Token CSRF inválido');
        }
        
        if (empty($input['content']) || empty($input['recipe_id'])) {
            throw new Exception('Conteúdo e ID da receita são obrigatórios');
        }
        
        $data = [
            'recipe_id' => $input['recipe_id'],
            'user_id' => $_SESSION['user_id'],
            'parent_id' => $input['parent_id'] ?? null,
            'content' => $input['content']
        ];
        
        if ($commentModel->create($data)) {
            echo json_encode(['success' => true, 'message' => 'Comentário adicionado']);
        } else {
            throw new Exception('Erro ao adicionar comentário');
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get comments for recipe
        $recipeId = $_GET['recipe_id'] ?? null;
        
        if (!$recipeId) {
            throw new Exception('ID da receita é obrigatório');
        }
        
        $comments = $commentModel->getByRecipeId($recipeId);
        echo json_encode(['success' => true, 'comments' => $comments]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Delete comment
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Deve estar logado');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $commentId = $input['id'] ?? null;
        
        if (!$commentId) {
            throw new Exception('ID do comentário é obrigatório');
        }
        
        $comment = $commentModel->findById($commentId);
        if (!$comment) {
            throw new Exception('Comentário não encontrado');
        }
        
        // Check permissions
        if ($_SESSION['user_id'] != $comment['user_id'] && $_SESSION['role'] !== 'admin') {
            throw new Exception('Não tem permissão para eliminar este comentário');
        }
        
        if ($commentModel->delete($commentId)) {
            echo json_encode(['success' => true, 'message' => 'Comentário eliminado']);
        } else {
            throw new Exception('Erro ao eliminar comentário');
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Report comment
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Deve estar logado');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $commentId = $input['id'] ?? null;
        
        if (!$commentId) {
            throw new Exception('ID do comentário é obrigatório');
        }
        
        if ($commentModel->report($commentId)) {
            echo json_encode(['success' => true, 'message' => 'Comentário reportado']);
        } else {
            throw new Exception('Erro ao reportar comentário');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}