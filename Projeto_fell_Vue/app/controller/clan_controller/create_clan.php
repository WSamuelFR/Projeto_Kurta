<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/ClanModel.php';

$database = new Database();
$db = $database->getConnection();
$clanModel = new ClanModel($db);

$name = $_POST['name'] ?? '';
$desc = $_POST['description'] ?? '';
$vis = $_POST['visibility'] ?? 'public';
$userId = $_SESSION['user_id'];

if (empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Nome do clã não informado.']);
    exit;
}

$picPath = 'assets/files/default_clan.png';

// File upload
if(isset($_FILES['clan_pic']) && $_FILES['clan_pic']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $filename = $_FILES['clan_pic']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if(in_array($ext, $allowed)) {
        // Must upload to public/assets/files/
        $uploadDir = __DIR__ . '/../../../public/assets/files/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $newFilename = uniqid('clan_') . '.' . $ext;
        if(move_uploaded_file($_FILES['clan_pic']['tmp_name'], $uploadDir . $newFilename)) {
            $picPath = 'assets/files/' . $newFilename;
        }
    }
}

// Create Clan via Model
$clanId = $clanModel->createClan($name, $desc, $vis, $userId, $picPath);

if ($clanId) {
    echo json_encode(['status' => 'success', 'message' => 'Clã criado com sucesso!', 'clan_id' => $clanId]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao criar clã no banco de dados.']);
}
?>
