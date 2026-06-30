<?php
require 'vendor/autoload.php';
require 'config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateToken($user) {
    global $secretKey;
    $payload = [
        'iss' => 'blog-app',
        'aud' => 'blog-users',
        'iat' => time(),
        'exp' => time() + 3600,
        'data' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email']
        ]
    ];
    return JWT::encode($payload, $secretKey, 'HS256');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'];
    $password = $input['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => true,
            'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']],
            'token' => generateToken($user)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
}
?>