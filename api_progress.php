<?php
/**
 * PROGRESS.PHP - GESTION DE LA PROGRESSION & GAMIFICATION
 */
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$userId) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Connectez-vous pour sauvegarder."]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $module = $data['module'] ?? '';
    $labs = intval($data['labs_completed'] ?? 0);

    if (empty($module)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Module manquant."]);
        exit;
    }

    try {
        // Mettre à jour si labs >= valeur actuelle (pour éviter les retours en arrière)
        $stmt = $pdo->prepare("INSERT INTO progress (user_id, module, labs_completed) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE labs_completed = GREATEST(labs_completed, VALUES(labs_completed))");
        $stmt->execute([$userId, $module, $labs]);

        echo json_encode(["status" => "success", "message" => "Progression synchronisée."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erreur de synchro."]);
    }
} 
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$userId) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Non connecté."]);
        exit;
    }

    try {
        // 1. Récupérer toutes les progressions
        $stmt = $pdo->prepare("SELECT module, labs_completed FROM progress WHERE user_id = ?");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalLabs = 0;
        $stats = [];
        foreach ($rows as $row) {
            $totalLabs += $row['labs_completed'];
            $stats[$row['module']] = $row['labs_completed'];
        }

        // 2. Calcul du Niveau & Titre
        $levelData = getLevelTitle($totalLabs);

        // 3. Calcul des Badges
        $badges = getBadges($stats, $_SESSION['streak'] ?? 0);

        echo json_encode([
            "status" => "success",
            "total_labs" => $totalLabs,
            "level_name" => $levelData['name'],
            "level_icon" => $levelData['icon'],
            "stats" => $stats,
            "badges" => $badges
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erreur de lecture."]);
    }
}

function getLevelTitle($total) {
    if ($total <= 50) return ["name" => "Novice du Code", "icon" => "🌱"];
    if ($total <= 150) return ["name" => "Apprenti Développeur", "icon" => "📖"];
    if ($total <= 300) return ["name" => "Artisan du Web", "icon" => "🛠️"];
    if ($total <= 600) return ["name" => "Sorcier de la Syntaxe", "icon" => "🧙‍♂️"];
    return ["name" => "Légende Fullstack", "icon" => "👑"];
}

function getBadges($stats, $streak) {
    $badges = [];
    $total = array_sum($stats);

    if ($total >= 1) $badges[] = ["id" => "first_blood", "name" => "Premier Sang", "icon" => "🏆"];
    if (($stats['php'] ?? 0) >= 50) $badges[] = ["id" => "php_pro", "name" => "Éléphant d'Or", "icon" => "🐘"];
    if (($stats['sql'] ?? 0) >= 50) $badges[] = ["id" => "sql_master", "name" => "Maître MySQL", "icon" => "🗄️"];
    if (($stats['js'] ?? 0) >= 50) $badges[] = ["id" => "js_ninja", "name" => "Ninja JS", "icon" => "🚀"];
    if ($streak >= 7) $badges[] = ["id" => "persévérant", "name" => "Persévérant", "icon" => "🔥"];
    
    // Badge Polyglotte : au moins 20 labs dans 4 langages
    $polyCount = 0;
    foreach($stats as $val) if($val >= 20) $polyCount++;
    if($polyCount >= 4) $badges[] = ["id" => "polyglotte", "name" => "Polyglotte", "icon" => "🌐"];

    return $badges;
}
?>
