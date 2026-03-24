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

if (!isset($data['feeling_id']) || !isset($data['coment'])) {
    echo json_encode(['status' => 'error', 'message' => 'O sistema falhou ao transmitir os dados.']);
    exit;
}

$text = trim($data['coment']);
$feelingId = intval($data['feeling_id']);
$parentId = isset($data['parent_id']) && $data['parent_id'] !== "" ? intval($data['parent_id']) : null;
$userId = $_SESSION['user_id'];

if (empty($text)) {
    echo json_encode(['status' => 'error', 'message' => 'O comentário está vazio.']);
    exit;
}

if (mb_strlen($text) > 1500) {
    echo json_encode(['status' => 'error', 'message' => 'Seu comentário excede o limite estrito de 1500 caracteres.']);
    exit;
}

$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

$db = (new Database())->getConnection();
$commentModel = new commentModel($db);

$insertedId = $commentModel->addComment($feelingId, $userId, $text, $parentId);

if ($insertedId) {
    echo json_encode(['status' => 'success', 'message' => 'Comentário publicado!', 'insert_id' => $insertedId]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro interno PDO ao salvar comentário.']);
}
?>
