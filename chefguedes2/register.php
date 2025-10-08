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
        
        // Handle avatar upload
        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarPath = handleAvatarUpload($_FILES['avatar']);
        }
        
        $data = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'bio' => $_POST['bio'] ?? '',
            'avatar' => $avatarPath
        ];
        
        $authController = new AuthController();
        if ($authController->register($data)) {
            $success = 'Conta criada com sucesso! Pode fazer login.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function handleAvatarUpload($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipo de ficheiro não permitido');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('Ficheiro muito grande (máx. 2MB)');
    }
    
    $uploadDir = __DIR__ . '/uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Erro ao fazer upload do ficheiro');
    }
    
    return 'avatars/' . $filename;
}

require_once __DIR__ . '/Views/header.php';
?>
<main class="container">
    <h2>Registo</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post" action="/chefguedes2/register.php" enctype="multipart/form-data">
        <?= CSRF::getTokenField() ?>
        <label>Nome 
            <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </label>
        <label>Email 
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </label>
        <label>Password 
            <input type="password" name="password" required>
        </label>
        <label>Bio (opcional) 
            <textarea name="bio"><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
        </label>
        <label>Foto de perfil (opcional) 
            <input type="file" name="avatar" accept="image/*">
        </label>
        <button type="submit" class="btn">Registar</button>
    </form>
    
    <p class="text-center mt-2">
    Já tem conta? <a href="/chefguedes2/login.php">Entrar aqui</a>
    </p>
</main>
<?php require_once __DIR__ . '/Views/footer.php';