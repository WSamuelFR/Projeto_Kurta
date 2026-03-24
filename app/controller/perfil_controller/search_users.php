<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Sem autenticação central.']); exit; }

require_once '../../config/database.php';
require_once '../../model/friendModel.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$term = trim($data['query'] ?? '');
if(mb_strlen($term) < 2) { 
    echo json_encode(['status'=>'empty','data'=>[]]); exit; 
} 

$db = (new Database())->getConnection();
$friendModel = new friendModel($db);

$users = $friendModel->searchUsers($term, $_SESSION['user_id']);

if($users !== false){ 
    echo json_encode(['status'=>'success', 'data'=>$users]); 
} else { 
    echo json_encode(['status'=>'error','message'=>'Falha grave do Motor Relacional na Busca de pessoas.']); 
}
?>
