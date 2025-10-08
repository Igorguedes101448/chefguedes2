<?php
// src/Controllers/AuthController.php
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function register($data) {
        // Validate data
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new Exception('Todos os campos são obrigatórios');
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }
        
        if (strlen($data['password']) < 6) {
            throw new Exception('Password deve ter pelo menos 6 caracteres');
        }
        
        // Check if user exists
        if ($this->userModel->findByEmail($data['email'])) {
            throw new Exception('Email já está registado');
        }
        
        // Create user
        return $this->userModel->create($data);
    }
    
    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            throw new Exception('Email ou password incorretos');
        }
        
        if (!$user['is_active']) {
            throw new Exception('Conta desativada');
        }
        
        // Start session
        session_start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        
        // Update last activity
        $this->userModel->updateLastActivity($user['id']);
        
        return $user;
    }
    
    public function logout() {
        session_start();
        session_destroy();
    }
}