<?php
session_start();
header('Content-Type: application/json');

// Rest API Auth Token Simples Base Session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Rejeitado por timeout de autenticação!']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/feelingModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['feeling_id']) || !isset($data['body'])) {
    echo json_encode(['status' => 'error', 'message' => 'O empacotamento JS da edição enviou as chaves de string erradas. Tente reabrir a modal.']);
    exit;
}

$bodyText = trim($data['body']);

if (empty($bodyText)) {
    echo json_encode(['status' => 'error', 'message' => 'Você não pode atualizar seu texto para ser completamente vazio. Delete-o ou redija a caneta corretamente.']);
    exit;
}

if (mb_strlen($bodyText) > 5000) {
    echo json_encode(['status' => 'error', 'message' => 'Bloqueado na API Anti-Spam: Limite máximo superado nos bytes atualizados.']);
    exit;
}

// Córtex XSS Protector: Escuda os caracteres < > e & " para formatação limpa string entity!
$bodyText = htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8');

$database = new Database();
$db = $database->getConnection();
$feelingModel = new feelingModel($db);

$success = $feelingModel->updateFeelingBody($data['feeling_id'], $_SESSION['user_id'], $bodyText);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'As suas palavras mágicas foram gravadas perfeitamente por cima original.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Sem alterações. Permissão negada pra forçar sobrescrita num card alheio, ou texto é idêntico.']);
}
?>
