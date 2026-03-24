<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Acesso negado token inexistente.']); exit; }

require_once '../../config/database.php';
require_once '../../model/friendModel.php';

$db = (new Database())->getConnection();
$friendModel = new friendModel($db);

// Magia Universal Router: Descobre se quem requisitou isto Javascript via URL?
// Se tiver URL "?user_id=89", a engine entende que ele ta visitando fulano e carrega amizades do FULANO, senão carrega a do Dito-Cujo (Sessão).
$targetId = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];

$friends = $friendModel->getMyFriends($targetId);

if($friends !== false) echo json_encode(['status'=>'success', 'data'=>$friends]);
else echo json_encode(['status'=>'error', 'message'=>'Erro relacional PDO ao puxar contatos da grade friend.']);
?>
