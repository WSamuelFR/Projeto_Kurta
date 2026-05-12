<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado.']);
    exit;
}

require_once __DIR__ . '/../../model/perfilModel.php';
require_once __DIR__ . '/../../config/criptography.php';

$userId = $_SESSION['user_id'];
$model = new PerfilModel();

// Coleta dados do POST (vêm via FormData)
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';

$success = true;
$messages = [];

// Atualiza dados básicos
if ($model->updateProfile($userId, ['first_name' => $firstName, 'last_name' => $lastName, 'phone' => $phone])) {
    $messages[] = "Dados atualizados.";
} else {
    $success = false;
}

// Atualiza senha se fornecida
if (!empty($password)) {
    $hashed = Criptography::hashPassword($password);
    if ($model->updatePassword($userId, $hashed)) {
        $messages[] = "Senha alterada.";
    }
}

// Processa Upload de Avatar
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $newName = "avatar_" . $userId . "_" . time() . "." . $ext;
    $targetPath = __DIR__ . "/../../../public/assets/files/" . $newName;
    $dbPath = "assets/files/" . $newName;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
        $model->updateProfilePic($userId, $dbPath);
        $messages[] = "Avatar atualizado.";
    }
}

// Processa Upload de Wallpaper
if (isset($_FILES['wallpaper']) && $_FILES['wallpaper']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['wallpaper']['name'], PATHINFO_EXTENSION);
    $newName = "wall_" . $userId . "_" . time() . "." . $ext;
    $targetPath = __DIR__ . "/../../../public/assets/files/" . $newName;
    $dbPath = "assets/files/" . $newName;

    if (move_uploaded_file($_FILES['wallpaper']['tmp_name'], $targetPath)) {
        $model->updateWallpaperPic($userId, $dbPath);
        $messages[] = "Capa atualizada.";
    }
}

echo json_encode([
    'success' => $success,
    'message' => implode(" ", $messages) ?: "Nenhuma alteração detectada."
]);
