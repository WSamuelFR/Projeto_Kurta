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
require_once __DIR__ . '/../../model/NotificationModel.php';

$database = new Database();
$db = $database->getConnection();
$notifModel = new NotificationModel($db);

$items = $notifModel->getNotifications($_SESSION['user_id'], 20);
$unreadCount = $notifModel->getUnreadCount($_SESSION['user_id']);

echo json_encode([
    'status' => 'success',
    'unread_count' => $unreadCount,
    'data' => $items
]);
?>
