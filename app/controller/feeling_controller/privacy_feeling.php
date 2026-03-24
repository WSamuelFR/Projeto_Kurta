<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Permissão revogada. Token Null.']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/feelingModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['feeling_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Pacote ID Corrompido.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$feelingModel = new feelingModel($db);

// Action Invokations Toggle
$success = $feelingModel->updateFeelingPrivacy($data['feeling_id'], $_SESSION['user_id']);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Barreira Trancada/Livre ativada pra essa postagem.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Não conseguimos engatar as trancas em uma conta que não existe no seu array.']);
}
?>
