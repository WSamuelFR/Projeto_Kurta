<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário deslogado.']);
    exit;
}

require_once '../../config/database.php';

$db = (new Database())->getConnection();
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['status' => 'success', 'users' => [], 'clans' => []]);
    exit;
}

$likeQuery = '%' . $query . '%';

// Buscar Usuários
$stmtU = $db->prepare("SELECT user_id, first_name, last_name, profile_pic FROM user WHERE (first_name LIKE :q OR last_name LIKE :q OR email LIKE :q) AND user_id != :uid LIMIT 5");
$stmtU->bindParam(':q', $likeQuery, PDO::PARAM_STR);
$stmtU->bindParam(':uid', $_SESSION['user_id'], PDO::PARAM_INT);
$stmtU->execute();
$users = $stmtU->fetchAll(PDO::FETCH_ASSOC);

// Buscar Clãs
$stmtC = $db->prepare("SELECT clan_id, name_clan, clan_pic, visibility FROM clan WHERE name_clan LIKE :q LIMIT 5");
$stmtC->bindParam(':q', $likeQuery, PDO::PARAM_STR);
$stmtC->execute();
$clans = $stmtC->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'users' => $users, 'clans' => $clans]);
?>
