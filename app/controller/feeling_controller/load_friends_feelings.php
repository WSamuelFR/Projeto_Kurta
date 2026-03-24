<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário deslogado. Falha de request AMIGOS.']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/feelingModel.php';

$database = new Database();
$db = $database->getConnection();
$feelingModel = new feelingModel($db);

$visitorId = $_SESSION['user_id'];

// Carrega estritamente laços de amizade validados
$feelings = $feelingModel->getFriendsFeelings($visitorId);

if ($feelings !== false) {
    echo json_encode(['status' => 'success', 'data' => $feelings]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Houve um erro grave no carregamento da Aba de Amigos no Model.']);
}
?>
