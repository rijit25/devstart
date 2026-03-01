<?php
/**
 * LOGIN.PHP - AUTHENTIFICATION SÉCURISÉE (PDO + BCRYPT)
 * -----------------------------------------------------
 * Traite la connexion utilisateur et démarre la session.
 */
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Protection contre les injections SQL (htmlspecialchars non nécessaire pour BDD mais bon réflexe)
    $email = trim($data['email']);
    $pass = $data['password'];

    if (empty($email) || empty($pass)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs."]);
        exit;
    }

    try {
        // 1. Récupérer l'utilisateur
        $stmt = $pdo->prepare("SELECT id, username, password_hash, streak_count, last_login FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            // 2. Connexion Réussie !
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['streak'] = $user['streak_count'];

            // 3. Mise à jour de la date de dernière connexion et Streak
            // Calculer si c'est une connexion consécutive (Streak +1) ou Reset
            $today = new DateTime();
            $last = $user['last_login'] ? new DateTime($user['last_login']) : null;
            
            $newStreak = $user['streak_count'];

            if ($last) {
                $days = $last->diff($today)->days;
                if ($days == 1) { // Hier -> Série continue !
                    $newStreak++;
                } else if ($days > 1) { // Manqué un jour -> Reset :(
                    $newStreak = 0; 
                }
            } else {
                $newStreak = 1; // Premier login
            }

            $update = $pdo->prepare("UPDATE users SET last_login = NOW(), streak_count = ? WHERE id = ?");
            $update->execute([$newStreak, $user['id']]);
            
            $_SESSION['streak'] = $newStreak;

            echo json_encode([
                "status" => "success", 
                "username" => $user['username'], 
                "token" => session_id(), // Session PHP normale
                "streak" => $newStreak
            ]);

        } else {
            // 4. Échec (Ne pas dire "Email introuvable" pour sécurité -> "Identifiants invalides")
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Identifiants invalides."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erreur serveur critique."]);
    }
}
?>
