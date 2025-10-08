<?php
// public/recipe.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/Controllers/RecipeController.php';
require_once __DIR__ . '/Utils/CSRF.php';

$recipeController = new RecipeController();
$error = '';
$success = '';
$recipe = null;
$action = $_GET['action'] ?? 'view';
$id = $_GET['id'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token CSRF inválido');
        }
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = $recipeController->handleImageUpload($_FILES['image']);
        }
        
        $data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'ingredients' => explode("\n", trim($_POST['ingredients'])),
            'steps' => explode("\n", trim($_POST['steps'])),
            'prep_time' => (int)$_POST['prep_time'],
            'servings' => (int)$_POST['servings'],
            'category' => $_POST['category'],
            'tags' => $_POST['tags'],
            'image' => $imagePath,
            'visibility' => $_POST['visibility'] ?? 'public'
        ];
        
        if ($action === 'create') {
            if ($recipeController->create($data)) {
                header('Location: /chefguedes2/');
                exit;
            }
        } elseif ($action === 'edit' && $id) {
            if ($recipeController->update($id, $data)) {
                header('Location: /chefguedes2/recipe.php?id=' . $id);
                exit;
            }
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle delete action
if ($action === 'delete' && $id && $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
    $recipeController->delete($id);
    header('Location: /chefguedes2/');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load recipe for view/edit
if ($id && in_array($action, ['view', 'edit'])) {
    try {
        $recipe = $recipeController->getById($id);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/Views/header.php';
?>
<main class="container">
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($action === 'create' || $action === 'edit'): ?>
        <h2><?= $action === 'create' ? 'Criar Receita' : 'Editar Receita' ?></h2>
        
        <form method="post" enctype="multipart/form-data">
            <?= CSRF::getTokenField() ?>
            
            <label>Título *
                <input type="text" name="title" required value="<?= htmlspecialchars($recipe['title'] ?? '') ?>">
            </label>
            
            <label>Descrição *
                <textarea name="description" required><?= htmlspecialchars($recipe['description'] ?? '') ?></textarea>
            </label>
            
            <label>Ingredientes * (um por linha)
                <textarea name="ingredients" required rows="5"><?= $recipe ? implode("\n", json_decode($recipe['ingredients'], true)) : '' ?></textarea>
            </label>
            
            <label>Passos * (um por linha)
                <textarea name="steps" required rows="5"><?= $recipe ? implode("\n", json_decode($recipe['steps'], true)) : '' ?></textarea>
            </label>
            
            <label>Tempo de preparação (minutos)
                <input type="number" name="prep_time" value="<?= $recipe['prep_time'] ?? '' ?>">
            </label>
            
            <label>Porções
                <input type="number" name="servings" value="<?= $recipe['servings'] ?? '' ?>">
            </label>
            
            <label>Categoria
                <select name="category">
                    <option value="">Selecionar...</option>
                    <option value="Pequeno-almoço" <?= ($recipe['category'] ?? '') === 'Pequeno-almoço' ? 'selected' : '' ?>>Pequeno-almoço</option>
                    <option value="Entrada" <?= ($recipe['category'] ?? '') === 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                    <option value="Prato principal" <?= ($recipe['category'] ?? '') === 'Prato principal' ? 'selected' : '' ?>>Prato principal</option>
                    <option value="Sobremesa" <?= ($recipe['category'] ?? '') === 'Sobremesa' ? 'selected' : '' ?>>Sobremesa</option>
                    <option value="Bebida" <?= ($recipe['category'] ?? '') === 'Bebida' ? 'selected' : '' ?>>Bebida</option>
                </select>
            </label>
            
            <label>Tags (separadas por vírgula)
                <input type="text" name="tags" value="<?= htmlspecialchars($recipe['tags'] ?? '') ?>">
            </label>
            
            <label>Imagem
                <input type="file" name="image" accept="image/*">
            </label>
            
            <label>Visibilidade
                <select name="visibility">
                    <option value="public" <?= ($recipe['visibility'] ?? 'public') === 'public' ? 'selected' : '' ?>>Pública</option>
                    <option value="private" <?= ($recipe['visibility'] ?? '') === 'private' ? 'selected' : '' ?>>Privada</option>
                </select>
            </label>
            
            <button type="submit" class="btn"><?= $action === 'create' ? 'Criar' : 'Atualizar' ?></button>
            <a href="<?= $action === 'edit' ? '/chefguedes2/recipe.php?id=' . $id : '/chefguedes2/' ?>" class="btn btn-secondary">Cancelar</a>
        </form>
        
    <?php elseif ($recipe && $action === 'view'): ?>
        <article class="recipe-detail">
            <header class="recipe-header">
                <h1><?= htmlspecialchars($recipe['title']) ?></h1>
                <div class="recipe-meta">
                    <span class="stars"><?= str_repeat('★', round($recipe['avg_rating'])) ?> (<?= $recipe['rating_count'] ?>)</span>
                    <span><?= $recipe['prep_time'] ?> min</span>
                    <span><?= $recipe['servings'] ?> porções</span>
                    <span>Por: <?= htmlspecialchars($recipe['author_name']) ?></span>
                </div>
            </header>
            
            <?php if ($recipe['image']): ?>
                <img src="/chefguedes2/uploads/<?= $recipe['image'] ?>" alt="<?= htmlspecialchars($recipe['title']) ?>" class="recipe-image">
            <?php endif; ?>
            
            <section class="recipe-description">
                <p><?= nl2br(htmlspecialchars($recipe['description'])) ?></p>
            </section>
            
            <section class="recipe-ingredients">
                <h3>Ingredientes</h3>
                <ul>
                    <?php foreach (json_decode($recipe['ingredients'], true) as $ingredient): ?>
                        <li><?= htmlspecialchars($ingredient) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
            
            <section class="recipe-steps">
                <h3>Modo de preparação</h3>
                <ol>
                    <?php foreach (json_decode($recipe['steps'], true) as $step): ?>
                        <li><?= htmlspecialchars($step) ?></li>
                    <?php endforeach; ?>
                </ol>
            </section>
            
            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $recipe['user_id'] || $_SESSION['role'] === 'admin')): ?>
                <div class="recipe-actions">
                    <a href="/chefguedes2/recipe.php?action=edit&id=<?= $recipe['id'] ?>" class="btn">Editar</a>
                    <a href="/chefguedes2/recipe.php?action=delete&id=<?= $recipe['id'] ?>" class="btn btn-secondary" onclick="return confirm('Tem certeza?')">Eliminar</a>
                </div>
            <?php endif; ?>
            
            <!-- Rating Section -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <section class="recipe-rating">
                    <h3>Sua Avaliação</h3>
                    <div class="star-rating" data-recipe-id="<?= $recipe['id'] ?>" data-rating="0">
                        <span class="star" data-value="1">★</span>
                        <span class="star" data-value="2">★</span>
                        <span class="star" data-value="3">★</span>
                        <span class="star" data-value="4">★</span>
                        <span class="star" data-value="5">★</span>
                    </div>
                </section>
            <?php endif; ?>
            
            <!-- Comments Section -->
            <section class="recipe-comments">
                <h3>Comentários</h3>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form class="comment-form" data-recipe-id="<?= $recipe['id'] ?>">
                        <?= CSRF::getTokenField() ?>
                        <textarea name="content" placeholder="Escreva seu comentário..." required></textarea>
                        <button type="submit" class="btn">Comentar</button>
                    </form>
                <?php endif; ?>
                
                <div id="comments-list" data-recipe-id="<?= $recipe['id'] ?>">
                    <!-- Comments will be loaded here -->
                </div>
            </section>
        </article>
        
    <?php else: ?>
        <h2>Receita não encontrada</h2>
    <p><a href="/chefguedes2/">Voltar ao início</a></p>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/Views/footer.php';