<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->clan_id) || !isset($data->target_user_id) || !isset($data->new_role)) {
    echo json_encode(['status' => 'error', 'message' => 'Parâmetros de Promoção Incompletos!']); exit;
}

$clanId = $data->clan_id;
$targetUserId = $data->target_user_id;
$newRole = $data->new_role; // 'lider' or 'aldeao'

// Security Role Limit check
if (!in_array($newRole, ['lider', 'aldeao'])) {
    echo json_encode(['status' => 'error', 'message' => 'Patente Inválida Rejeitada pelo SQL.']); exit;
}

$db = (new Database())->getConnection();

// ==== VERIFICAÇÃO ABSOLUTA DA COROA (Você é o Rei?) ====
$stmt = $db->prepare("SELECT role FROM clan_member WHERE clan_id = :c AND user_id = :u");
$stmt->execute([':c' => $clanId, ':u' => $_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole !== 'rei') {
    echo json_encode(['status' => 'error', 'message' => 'Apenas o Rei do Clã pode promover ou rebaixar generais.']); exit;
}

// ==== VERIFICAÇÃO DO ALVO ====
$stmt = $db->prepare("SELECT role FROM clan_member WHERE clan_id = :c AND user_id = :u");
$stmt->execute([':c' => $clanId, ':u' => $targetUserId]);
$targetRole = $stmt->fetchColumn();

if ($targetRole === 'rei') {
    echo json_encode(['status' => 'error', 'message' => 'O Rei não pode rebaixar a si mesmo através deste módulo.']); exit;
}

// UPDATE CORE
$stmt = $db->prepare("UPDATE clan_member SET role = :r WHERE clan_id = :c AND user_id = :u");
$res = $stmt->execute([':r' => $newRole, ':c' => $clanId, ':u' => $targetUserId]);

if ($res) {
    echo json_encode(['status' => 'success', 'message' => 'A Patente foi atualizada na sala do trono com glória!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro interno BD ao tentar modificar a patente.']);
}
?>
