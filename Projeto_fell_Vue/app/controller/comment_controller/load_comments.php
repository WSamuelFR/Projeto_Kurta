<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Autenticação necessária.']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/commentModel.php';

if (!isset($_GET['feeling_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Falta de Parâmetros.']);
    exit;
}

$feelingId = intval($_GET['feeling_id']);

$db = (new Database())->getConnection();
$commentModel = new commentModel($db);

$comments = $commentModel->getCommentsByFeeling($feelingId);

if ($comments !== false) {
    echo json_encode(['status' => 'success', 'data' => $comments, 'session_user' => $_SESSION['user_id']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro interno na extração PDO das árvores de conversas.']);
}
?>
