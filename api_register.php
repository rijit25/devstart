<?php
/**
 * REGISTER.PHP - CRÉATION DE COMPTE SÉCURISÉE (PDO + BCRYPT)
 * --------------------------------------------------------
 * Traite la création d'un utilisateur et démarre la session.
 */
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];

    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs."]);
        exit;
    }

    // Validation basique de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Format d'email invalide."]);
        exit;
    }

    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Le mot de passe doit faire au moins 6 caractères."]);
        exit;
    }

    try {
        // 1. Vérifier si l'utilisateur ou l'email existe déjà
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "Cet email ou nom d'utilisateur est déjà utilisé."]);
            exit;
        }

        // 2. Hacher le mot de passe
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // 3. Insérer l'utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);
        $userId = $pdo->lastInsertId();

        // 4. Initialiser la progression par défaut pour tous les modules
        $modules = ['html', 'css', 'js', 'php', 'sql', 'arch'];
        $progStmt = $pdo->prepare("INSERT INTO progress (user_id, module, labs_completed) VALUES (?, ?, 0)");
        foreach ($modules as $mod) {
            $progStmt->execute([$userId, $mod]);
        }

        // 5. Connecter l'utilisateur automatiquement après inscription
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['streak'] = 0;

        echo json_encode([
            "status" => "success", 
            "message" => "Compte créé avec succès.",
            "username" => $username
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erreur lors de la création du compte."]);
    }
}
?>
