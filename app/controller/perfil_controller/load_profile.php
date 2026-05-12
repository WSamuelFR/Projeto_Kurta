<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit;
}

require_once __DIR__ . '/../../model/perfilModel.php';

$userId = $_SESSION['user_id'];
$model = new PerfilModel();
$userData = $model->getUserData($userId);

if ($userData) {
    echo json_encode(['success' => true, 'data' => $userData]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
}
