<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Sessao Interrompida API.']); exit; }

require_once '../../config/database.php';
require_once '../../model/friendModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if(!isset($data['receiver_id'])) { echo json_encode(['status'=>'error','message'=>'Disparo target ID perdido e inválido na navegação.']); exit; }

$db = (new Database())->getConnection();
$friendModel = new friendModel($db);

$result = $friendModel->sendInvite($_SESSION['user_id'], intval($data['receiver_id']));

if($result === 'success') {
    echo json_encode(['status'=>'success', 'message'=>'Aviso de convite diplomático notificado ao usuário alvo com glória! 📨']);
} else if($result === 'exists') {
    echo json_encode(['status'=>'error', 'message'=>'Já constam tramitações e convites passados circulando ou em julgamento entre os perfis! 🚧']);
} else {
    echo json_encode(['status'=>'error', 'message'=>'Falha catastrófica de Base de dados no Endpoint Insert.']);
}
?>
