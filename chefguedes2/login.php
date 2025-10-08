<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Utils/CSRF.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token CSRF inválido');
        }
        
        $authController = new AuthController();
        $user = $authController->login($_POST['email'], $_POST['password']);
        
    header('Location: /chefguedes2/');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/Views/header.php';
?>
<main class="container">
    <h2>Login</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="post" action="/chefguedes2/login.php">
        <?= CSRF::getTokenField() ?>
        <label>Email 
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </label>
        <label>Password 
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn">Entrar</button>
    </form>
    
    <p class="text-center mt-2">
    Não tem conta? <a href="/chefguedes2/register.php">Registar aqui</a>
    </p>
</main>
<?php require_once __DIR__ . '/Views/footer.php';