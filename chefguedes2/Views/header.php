<?php
// src/Views/header.php
if (!class_exists('CSRF')) {
    require_once __DIR__ . '/../Utils/CSRF.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(CSRF::getToken(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= $pageTitle ?? 'ChefGuedes' ?></title>
    <link rel="stylesheet" href="/chefguedes2/assets/css/styles.css">
</head>
<body data-user-id="<?= $_SESSION['user_id'] ?? '' ?>">
    <header>
        <div class="container">
            <div class="header-content">
                <a href="/chefguedes2/" class="logo">ChefGuedes</a>
                <nav>
                    <ul>
                        <li><a href="/chefguedes2/">Início</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="/chefguedes2/profile.php">Perfil</a></li>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <li><a href="/chefguedes2/admin.php">Admin</a></li>
                            <?php endif; ?>
                            <li><a href="/chefguedes2/logout.php">Sair</a></li>
                        <?php else: ?>
                            <li><a href="/chefguedes2/login.php">Entrar</a></li>
                            <li><a href="/chefguedes2/register.php">Registar</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>