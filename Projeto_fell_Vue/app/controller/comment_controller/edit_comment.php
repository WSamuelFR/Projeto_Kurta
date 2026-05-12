<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Autenticação necessária.']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/commentModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['comment_id']) || !isset($data['coment'])) {
    echo json_encode(['status' => 'error', 'message' => 'Pacotes JSON inválidos.']);
    exit;
}

$commentId = intval($data['comment_id']);
$userId = $_SESSION['user_id'];
$text = trim($data['coment']);

if (empty($text)) {
    echo json_encode(['status' => 'error', 'message' => 'Comentário vazio.']);
    exit;
}

if (mb_strlen($text) > 1500) {
    echo json_encode(['status' => 'error', 'message' => 'O comentário excede os impressionantes 1500 caracteres.']);
    exit;
}

$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

$db = (new Database())->getConnection();
$commentModel = new commentModel($db);

$success = $commentModel->editComment($commentId, $userId, $text);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Comentário reescrito nas estrelas!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro PDO ou Comentário não pertence a você na Matrix.']);
}
?>
