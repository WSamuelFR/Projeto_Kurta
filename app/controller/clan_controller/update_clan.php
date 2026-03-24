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
require_once __DIR__ . '/../../model/ClanModel.php';

$db = (new Database())->getConnection();
$clanModel = new ClanModel($db);

$data = json_decode(file_get_contents("php://input"));
if(empty($data->clan_id)) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

$clanId = intval($data->clan_id);
$userId = $_SESSION['user_id'];
$action = $data->action ?? ''; // update or delete

$role = $clanModel->getUserRole($clanId, $userId);
if ($role !== 'rei') {
    echo json_encode(['status' => 'error', 'message' => 'Somente a Coroa tem poder para gerenciar o clã.']);
    exit;
}

if ($action === 'delete') {
    // Delete Process
    $sql = "DELETE FROM clan WHERE clan_id = :id";
    $s = $db->prepare($sql);
    if($s->execute([':id' => $clanId])) {
        echo json_encode(['status' => 'success', 'message' => 'Clã exterminado.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Falha ao excluir base.']);
    }
    exit;
} else if ($action === 'update') {
    // Basic Details update
    $n = htmlspecialchars(strip_tags($data->name));
    $d = htmlspecialchars(strip_tags($data->desc));
    $v = in_array($data->vis, ['public', 'private']) ? $data->vis : 'public';
    
    $sql = "UPDATE clan SET name_clan=:n, description=:d, visibility=:v WHERE clan_id=:id";
    $s = $db->prepare($sql);
    if ($s->execute([':n'=>$n, ':d'=>$d, ':v'=>$v, ':id'=>$clanId])) {
        echo json_encode(['status' => 'success', 'message' => 'Clã atualizado!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Falha DB ao atualizar']);
    }
}
?>
