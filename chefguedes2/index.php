<?php
// public/index.php - Homepage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/Models/Recipe.php';

$recipeModel = new Recipe();
$recipes = $recipeModel->getPublicRecipes();

require_once __DIR__ . '/Views/header.php';
?>
<main class="container">
    <h1>ChefGuedes</h1>
    <p>Bem-vindo ao ChefGuedes — partilha as tuas receitas favoritas.</p>
    
    <section class="search-section">
        <input type="text" id="search" placeholder="Pesquisar receitas...">
    </section>
    
    <section id="feed" class="recipe-grid">
        <?php foreach ($recipes as $recipe): ?>
            <?php include __DIR__ . '/Views/recipe_card.php'; ?>
        <?php endforeach; ?>
    </section>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="/chefguedes2/recipe.php?action=create" class="btn btn-fixed">+ Criar Receita</a>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/Views/footer.php';