<?php
// public/api/ratings.php
session_start();
require_once __DIR__ . '/../Models/Rating.php';
require_once __DIR__ . '/../Utils/CSRF.php';

header('Content-Type: application/json');

try {
    $ratingModel = new Rating();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create/update rating
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Deve estar logado para avaliar');
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!CSRF::validateToken($input['csrf_token'] ?? '')) {
            throw new Exception('Token CSRF inválido');
        }
        
        if (empty($input['recipe_id']) || empty($input['stars'])) {
            throw new Exception('ID da receita e avaliação são obrigatórios');
        }
        
        $stars = (int)$input['stars'];
        if ($stars < 1 || $stars > 5) {
            throw new Exception('Avaliação deve ser entre 1 e 5 estrelas');
        }
        
        $data = [
            'recipe_id' => $input['recipe_id'],
            'user_id' => $_SESSION['user_id'],
            'stars' => $stars
        ];
        
        if ($ratingModel->create($data)) {
            $recipeRating = $ratingModel->getRecipeRating($input['recipe_id']);
            echo json_encode([
                'success' => true, 
                'message' => 'Avaliação registada',
                'avg_rating' => round($recipeRating['avg_rating'], 1),
                'total_ratings' => $recipeRating['total_ratings']
            ]);
        } else {
            throw new Exception('Erro ao registar avaliação');
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get ratings for recipe
        $recipeId = $_GET['recipe_id'] ?? null;
        
        if (!$recipeId) {
            throw new Exception('ID da receita é obrigatório');
        }
        
        $recipeRating = $ratingModel->getRecipeRating($recipeId);
        $userRating = null;
        
        if (isset($_SESSION['user_id'])) {
            $userRating = $ratingModel->getUserRating($recipeId, $_SESSION['user_id']);
        }
        
        echo json_encode([
            'success' => true, 
            'avg_rating' => round($recipeRating['avg_rating'], 1),
            'total_ratings' => $recipeRating['total_ratings'],
            'user_rating' => $userRating ? $userRating['stars'] : null
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}