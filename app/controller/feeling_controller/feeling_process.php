<?php
session_start();
header('Content-Type: application/json');

// Requeirimento Auth (Proteção contra APIs Diretas não logadas)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não autenticado. Sua sessão pode ter expirado!']);
    exit;
}

require_once '../../config/database.php';
require_once '../../model/feelingModel.php';

// Coletor Base do JSON Body enviado no Fetch Javascript
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'O formato submetido não é um texto válido.']);
    exit;
}

// Extratos
$bodyText = trim($data['body'] ?? '');
$publishTarget = $data['publish_target'] ?? 'todos'; // Identifica alvo central
$claId = $data['cla_id'] ?? null;
$publicVisibility = $data['public_visibility'] ?? 'public'; // 'public' | 'friends_only'

// Validações Base Backend vs Front Bypass
if (empty($bodyText)) {
    echo json_encode(['status' => 'error', 'message' => 'Sua caixa de texto de Feeling está vazia no banco!']);
    exit;
}

// Limite forte (5000 max size no Backend) contra hacks
if (mb_strlen($bodyText) > 5000) {
    echo json_encode(['status' => 'error', 'message' => 'Houve uma tentativa de Injeção: O texto excede 5000 caracteres mágicos!']);
    exit;
}

// Saneamento pesado HTML e Scripts (XSS Prevent)
// Impede scripts como <script>alert(1)</script> de rodarem no feed dos outros
$bodyText = htmlspecialchars($bodyText, ENT_QUOTES, 'UTF-8');

// ==== Motor Inteligente de Restrição ====
$finalVisibility = 'public';
$finalClaId = null;

if ($publishTarget === 'cla') {
    // Alvo é um grupo corporativo Clã
    if (empty($claId)) {
        echo json_encode(['status' => 'error', 'message' => 'Se você escolheu Clã, você deve marcar um Clã no menu suspenso.']);
        exit;
    }
    // Setando privativo para não vazar p/ feed global (apenas Feed do respectivo Clã mapeado)
    $finalVisibility = 'private';
    $finalClaId = intval($claId);
} else {
    // Postagem para Timeline Físicas de Pessoas e Globais
    if ($publicVisibility === 'friends_only') {
        $finalVisibility = 'friends';
    } elseif ($publicVisibility === 'private') {
        $finalVisibility = 'private';
    } else {
        $finalVisibility = 'public'; // Padrão 'Todos'
    }
    $finalClaId = null; // Ignora clãs acidentais
}

// Persistência Model vs Data Relacional
$database = new Database();
$db = $database->getConnection();
$feelingModel = new feelingModel($db);

// Autoria e Gravação Exec
$userId = $_SESSION['user_id'];
$success = $feelingModel->createFeeling($userId, $bodyText, $finalVisibility, $finalClaId);

// Result response Array -> JSON
if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Feeling criado e eternizado na sua rede social com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro interno ao tentar salvar o registro no Banco de Dados. Entre em suporte!']);
}
?>
