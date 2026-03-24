<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

session_start();
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/NotificationModel.php';

$database = new Database();
$db = $database->getConnection();
$notifModel = new NotificationModel($db);

$marked = $notifModel->markAllAsRead($_SESSION['user_id']);

echo json_encode(['status' => $marked ? 'success' : 'error']);
?>
