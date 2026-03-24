<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário deslogado.']);
    exit;
}

$clanId = $_GET['clan_id'] ?? null;

if (!$clanId) {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum clã fornecido no Payload de Identificação.']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/feelingModel.php';

$database = new Database();
$db = $database->getConnection();
$feelingModel = new feelingModel($db);

$visitorId = $_SESSION['user_id'];

// Buscando APENAS feeling marcados carimbados como sendo desse Clan específico
$feelings = $feelingModel->getClanFeelings($clanId, $visitorId);

if ($feelings !== false) {
    echo json_encode(['status' => 'success', 'data' => $feelings]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Houve um erro grave no carregamento da base relacional no Model para este clã.']);
}
?>
