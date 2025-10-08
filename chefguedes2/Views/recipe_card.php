<?php
// src/Views/recipe_card.php
?>
<div class="recipe-card" data-recipe-id="<?= $recipe['id'] ?>">
    <img src="<?= $recipe['image'] ? '/chefguedes2/uploads/' . $recipe['image'] : '/chefguedes2/assets/images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($recipe['title']) ?>">
    <div class="recipe-card-content">
        <h3 class="recipe-title"><?= htmlspecialchars($recipe['title']) ?></h3>
        <p><?= htmlspecialchars(substr($recipe['description'], 0, 100)) ?>...</p>
        <div class="recipe-meta">
            <span class="stars"><?= str_repeat('★', round($recipe['avg_rating'] ?? 0)) ?></span>
            <span><?= $recipe['prep_time'] ?>min</span>
            <span>Por: <?= htmlspecialchars($recipe['author_name']) ?></span>
        </div>
    </div>
</div>