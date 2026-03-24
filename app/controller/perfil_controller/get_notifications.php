<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Sem autenticação.']); exit; }

require_once '../../config/database.php';
require_once '../../model/friendModel.php';

$db = (new Database())->getConnection();
$friendModel = new friendModel($db);

$invites = $friendModel->getPendingRequests($_SESSION['user_id']);
if($invites !== false) echo json_encode(['status'=>'success', 'data'=>$invites]);
else echo json_encode(['status'=>'error', 'message'=>'Erro relacional PDO no Inbox.']);
?>
