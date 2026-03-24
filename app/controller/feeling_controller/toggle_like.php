<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

// Validar se o usuário está logado
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("status" => "error", "message" => "Autenticação requerida."));
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->feeling_id)) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../model/likeModel.php';

    require_once __DIR__ . '/../../model/NotificationModel.php';

    $database = new Database();
    $db = $database->getConnection();

    $likeModel = new LikeModel($db);
    $notifModel = new NotificationModel($db);
    
    $feeling_id = intval($data->feeling_id);
    $user_id = $_SESSION['user_id'];
    
    // Altera o like
    $result = $likeModel->toggleLike($user_id, $feeling_id);
    
    if ($result) {
        // Pega dono do post pra notificação usando query rápida
        $stmtOwner = $db->prepare("SELECT user FROM feeling WHERE feeling_id = ?");
        $stmtOwner->execute([$feeling_id]);
        $owner_id = $stmtOwner->fetchColumn();

        if ($owner_id && $owner_id != $user_id) {
            if ($result['action'] === 'liked') {
                $notifModel->createNotification($owner_id, $user_id, 'like', $feeling_id);
            } else {
                $notifModel->removeNotification($owner_id, $user_id, 'like', $feeling_id);
            }
        }
        // Pega a nova contagem para mandar ao frontend
        $newCount = $likeModel->getLikesCount($feeling_id);
        
        http_response_code(200);
        echo json_encode(array(
            "status" => "success",
            "action" => $result['action'],
            "new_count" => $newCount,
            "message" => "Like atualizado com sucesso."
        ));
    } else {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Ocorreu um problema ao registrar sua curtida no servidor."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Dados incompletos. ID do feeling não fornecido."));
}
?>
