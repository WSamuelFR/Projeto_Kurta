<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Desconectado logico central.']); exit; }

require_once '../../config/database.php';
require_once '../../model/feelingModel.php';

$database = new Database();
$db = $database->getConnection();
$feelingModel = new feelingModel($db);

$visitorId = $_SESSION['user_id'];
$feelings = $feelingModel->getGlobalFeelings($visitorId);

if ($feelings !== false) {
    echo json_encode(['status' => 'success', 'data' => $feelings]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao compilar o extrato galático global do Model Córtex.']);
}
?>
