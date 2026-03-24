<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Sem autenticação.']); exit; }

require_once '../../config/database.php';
require_once '../../model/friendModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if(!isset($data['friendship_id']) || !isset($data['action'])) {
    echo json_encode(['status'=>'error','message'=>'Payload malformada de aceite.']); exit;
}
$action = $data['action']; // 'accepted' ou 'rejected'
if($action !== 'accepted' && $action !== 'rejected') { echo json_encode(['status'=>'error','message'=>'Ação ilegal']); exit; }

$db = (new Database())->getConnection();
$friendModel = new friendModel($db);

$success = $friendModel->respondInvite($data['friendship_id'], $_SESSION['user_id'], $action);
if($success) {
    $msg = $action === 'accepted' ? 'Amizade Consagrada!' : 'Relação Bloqueada Rejeitada.';
    echo json_encode(['status'=>'success', 'message'=> $msg]);
} else {
    echo json_encode(['status'=>'error', 'message'=>'Falha ou Convite já expirado/resolvido anteriormente.']);
}
?>
