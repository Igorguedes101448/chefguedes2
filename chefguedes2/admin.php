<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/Controllers/AdminController.php';
require_once __DIR__ . '/Utils/CSRF.php';

$adminController = new AdminController();
$error = '';
$success = '';

try {
  $adminController->checkAdminAccess();
    
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
      throw new Exception('Token CSRF inválido');
    }
        
    $action = $_POST['action'] ?? '';
        
    switch ($action) {
      case 'toggle_user':
        $userId = $_POST['user_id'];
        $status = $_POST['status'];
        if ($adminController->toggleUserStatus($userId, $status)) {
          $success = 'Estado do utilizador atualizado';
        }
        break;
                
      case 'promote_user':
        $userId = $_POST['user_id'];
        $role = $_POST['role'];
        if ($adminController->promoteUser($userId, $role)) {
          $success = 'Papel do utilizador atualizado';
        }
        break;
                
      case 'delete_recipe':
        $recipeId = $_POST['recipe_id'];
        if ($adminController->deleteRecipe($recipeId)) {
          $success = 'Receita eliminada';
        }
        break;
                
      case 'resolve_comment':
        $commentId = $_POST['comment_id'];
        $resolution = $_POST['resolution'];
        if ($adminController->resolveReportedComment($commentId, $resolution)) {
          $success = 'Comentário resolvido';
        }
        break;
    }
  }
    
  $stats = $adminController->getDashboardStats();
  $users = $adminController->getUsers();
  $recipes = $adminController->getAllRecipes();
  $reportedComments = $adminController->getReportedComments();
    
} catch (Exception $e) {
  $error = $e->getMessage();
  if (strpos($error, 'Acesso negado') !== false) {
    header('Location: /chefguedes2/login.php');
    exit;
  }
}

require_once __DIR__ . '/Views/header.php';
?>
<main class="container">
  <h1>Painel Administrativo</h1>
    
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
    
  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
    
  <?php if (isset($stats)): ?>
    <section class="admin-stats">
      <h2>Estatísticas</h2>
      <div class="stats-grid">
        <div class="stat-card">
          <h3><?= $stats['total_users'] ?></h3>
          <p>Utilizadores Ativos</p>
        </div>
        <div class="stat-card">
          <h3><?= $stats['total_recipes'] ?></h3>
          <p>Receitas Públicas</p>
        </div>
        <div class="stat-card">
          <h3><?= $stats['total_comments'] ?></h3>
          <p>Comentários</p>
        </div>
        <div class="stat-card">
          <h3><?= $stats['reported_comments'] ?></h3>
          <p>Comentários Reportados</p>
        </div>
      </div>
    </section>
        
    <?php if (!empty($reportedComments)): ?>
      <section class="reported-comments">
        <h2>Comentários Reportados</h2>
        <?php foreach ($reportedComments as $comment): ?>
          <div class="reported-comment">
            <div class="comment-info">
              <strong>Receita:</strong> <?= htmlspecialchars($comment['recipe_title']) ?><br>
              <strong>Autor:</strong> <?= htmlspecialchars($comment['author_name']) ?><br>
              <strong>Data:</strong> <?= $comment['created_at'] ?><br>
              <strong>Conteúdo:</strong> <?= htmlspecialchars($comment['content']) ?>
            </div>
            <div class="comment-actions">
              <form method="post" style="display: inline;">
                <?= CSRF::getTokenField() ?>
                <input type="hidden" name="action" value="resolve_comment">
                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                <input type="hidden" name="resolution" value="delete">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Eliminar comentário?')">Eliminar</button>
              </form>
              <form method="post" style="display: inline;">
                <?= CSRF::getTokenField() ?>
                <input type="hidden" name="action" value="resolve_comment">
                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                <input type="hidden" name="resolution" value="ignore">
                <button type="submit" class="btn btn-secondary">Ignorar</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>
        
    <section class="users-management">
      <h2>Gestão de Utilizadores</h2>
      <div class="table-container">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Email</th>
              <th>Papel</th>
              <th>Data Criação</th>
              <th>Última Atividade</th>
              <th>Estado</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] ?></td>
                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                <td><?= $user['last_activity'] ? date('d/m/Y H:i', strtotime($user['last_activity'])) : 'Nunca' ?></td>
                <td>
                  <span class="status-dot <?= $user['is_active'] ? 'status-online' : 'status-offline' ?>"></span>
                  <?= $user['is_active'] ? 'Ativo' : 'Inativo' ?>
                </td>
                <td>
                  <form method="post" style="display: inline;">
                    <?= CSRF::getTokenField() ?>
                    <input type="hidden" name="action" value="toggle_user">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="status" value="<?= $user['is_active'] ? 0 : 1 ?>">
                    <button type="submit" class="btn btn-sm">
                      <?= $user['is_active'] ? 'Desativar' : 'Ativar' ?>
                    </button>
                  </form>
                                    
                  <?php if ($user['role'] !== 'admin'): ?>
                    <form method="post" style="display: inline;">
                      <?= CSRF::getTokenField() ?>
                      <input type="hidden" name="action" value="promote_user">
                      <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                      <input type="hidden" name="role" value="admin">
                      <button type="submit" class="btn btn-sm" onclick="return confirm('Promover a admin?')">Promover</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
        
    <section class="recipes-management">
      <h2>Gestão de Receitas</h2>
      <div class="table-container">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Título</th>
              <th>Autor</th>
              <th>Categoria</th>
              <th>Visibilidade</th>
              <th>Data Criação</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recipes as $recipe): ?>
              <tr>
                <td>
                  <a href="/chefguedes2/recipe.php?id=<?= $recipe['id'] ?>" target="_blank">
                    <?= htmlspecialchars($recipe['title']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($recipe['author_name']) ?></td>
                <td><?= htmlspecialchars($recipe['category']) ?></td>
                <td><?= $recipe['visibility'] ?></td>
                <td><?= date('d/m/Y', strtotime($recipe['created_at'])) ?></td>
                <td>
                  <a href="/chefguedes2/recipe.php?action=edit&id=<?= $recipe['id'] ?>" class="btn btn-sm">Editar</a>
                  <form method="post" style="display: inline;">
                    <?= CSRF::getTokenField() ?>
                    <input type="hidden" name="action" value="delete_recipe">
                    <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar receita?')">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>
</main>
<?php require_once __DIR__ . '/Views/footer.php';