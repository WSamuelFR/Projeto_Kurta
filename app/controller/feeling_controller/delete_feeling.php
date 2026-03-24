<?php
session_start();
header('Content-Type: application/json');

// Requeirimento Auth (Proteção Restrita API)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado. Sua sessão expirou!']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/feelingModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['feeling_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum ID da postagem alvo foi emitido pelo sistema!']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$feelingModel = new feelingModel($db);

// O Delete requer a validação de Identidade da PDO Model pra executar o rowCount
$success = $feelingModel->deleteFeeling($data['feeling_id'], $_SESSION['user_id']);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Feeling completamente exterminado e apagado dos registros centrais!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Exclusão foi negada. Esta postagem pode não te pertencer de fato ou já ter sido varrida pelo backend.']);
}
?>
