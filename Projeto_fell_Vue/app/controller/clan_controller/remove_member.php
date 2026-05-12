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

if (!isset($data->clan_id) || !isset($data->target_user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Alvo não identificado para expulsão.']); exit;
}

$clanId = $data->clan_id;
$targetUserId = $data->target_user_id;

$db = (new Database())->getConnection();

// ==== VERIFICAÇÃO OBRIGATÓRIA REI/LÍDER ====
$stmt = $db->prepare("SELECT role FROM clan_member WHERE clan_id = :c AND user_id = :u");
$stmt->execute([':c' => $clanId, ':u' => $_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole !== 'rei') {
    // Poderia adicionar Lider aqui se lideres puderem expulsar. Pela regra: 'lider pode adicionar e remover postagens, membros e comentarios!'
    // Sim, O Lider tbm pode remover membros! 
    if ($userRole !== 'lider') {
        echo json_encode(['status' => 'error', 'message' => 'Apenas Reis e Líderes podem exilar aldeões.']); exit;
    }
}

// ==== VERIFICAÇÃO DO ALVO (Impedir que lider expulse outro lider ou rei) ====
$stmt = $db->prepare("SELECT role FROM clan_member WHERE clan_id = :c AND user_id = :u");
$stmt->execute([':c' => $clanId, ':u' => $targetUserId]);
$targetRole = $stmt->fetchColumn();

if ($targetRole === 'rei') {
    echo json_encode(['status' => 'error', 'message' => 'O Supremo Rei não pode ser banido de seu próprio império.']); exit;
}
if ($userRole === 'lider' && $targetRole === 'lider') {
    echo json_encode(['status' => 'error', 'message' => 'Um Líder não tem jurisdição para expulsar outro Líder.']); exit;
}

// DELETE CORE
$stmt = $db->prepare("DELETE FROM clan_member WHERE clan_id = :c AND user_id = :u");
$res = $stmt->execute([':c' => $clanId, ':u' => $targetUserId]);

if ($res) {
    echo json_encode(['status' => 'success', 'message' => 'O membro foi banido das terras do Clã com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro interno ao exilar.']);
}
?>
