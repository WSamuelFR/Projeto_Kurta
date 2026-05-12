<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/ClanModel.php';

$database = new Database();
$db = $database->getConnection();
$clanModel = new ClanModel($db);

$data = json_decode(file_get_contents("php://input"));
if(empty($data->clan_id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

$clanId = intval($data->clan_id);
$userId = $_SESSION['user_id'];

// Prevent duplicate joining
$existingRole = $clanModel->getUserRole($clanId, $userId);
if ($existingRole) {
    echo json_encode(['status' => 'error', 'message' => 'Você já é um membro deste clã.']);
    exit;
}

$clanInfo = $clanModel->getClanInfo($clanId);
if (!$clanInfo) {
    echo json_encode(['status' => 'error', 'message' => 'Clã não encontrado.']);
    exit;
}

if ($clanInfo['visibility'] == 'private') {
     // A better system would make this 'pending'
     echo json_encode(['status' => 'error', 'message' => 'Este clã é privado. Um líder deve adicioná-lo.']);
     exit;
}

$result = $clanModel->addMember($clanId, $userId, 'aldeao');

if ($result) {
    echo json_encode(['status' => 'success', 'message' => 'Bem-vindo ao Clã!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Problema ao entrar no clã.']);
}
?>
