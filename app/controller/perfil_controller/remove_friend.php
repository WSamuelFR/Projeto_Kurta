<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Acesso negado token logout.']); exit; }

require_once '../../config/database.php';
require_once '../../model/friendModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if(!isset($data['friend_id'])) { echo json_encode(['status'=>'error','message'=>'Usuário alvo não setado na tela MVC Unfriend.']); exit; }

$db = (new Database())->getConnection();
$friendModel = new friendModel($db);

// O User da sessão rompe relaçõe com o Target passsado no Request! Trava Córtex Model cuida da Auth
$success = $friendModel->removeFriend($_SESSION['user_id'], intval($data['friend_id']));

if($success) echo json_encode(['status'=>'success', 'message'=>'Amizade desfeita irrevogavelmente e laço SQL cortado da sua rede de Nodes.']);
else echo json_encode(['status'=>'error', 'message'=>'Database Rejeitou. Pode ser que a conexão tenha expirado, deletada ou não fosse genuína.']);
?>
