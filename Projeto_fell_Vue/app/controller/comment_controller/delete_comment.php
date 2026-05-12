<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Autenticação revogada.']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/commentModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['comment_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID de comentário inválido.']);
    exit;
}

$commentId = intval($data['comment_id']);
$userId = $_SESSION['user_id'];

$db = (new Database())->getConnection();
$commentModel = new commentModel($db);

$success = $commentModel->deleteComment($commentId, $userId);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Comentário removido com sucesso.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Acesso negado: Você não é nem o Dono deste Comentário nem o Dono do Post Original!']);
}
?>
