<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário deslogado. Falha de autenticação REST.']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/feelingModel.php';

$database = new Database();
$db = $database->getConnection();
$feelingModel = new feelingModel($db);

$targetId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
$visitorId = $_SESSION['user_id'];
$feelings = $feelingModel->getMyFeelings($targetId, $visitorId);

if ($feelings !== false) {
    echo json_encode(['status' => 'success', 'data' => $feelings]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Houve um erro grave no carregamento da base relacional no Model.']);
}
?>
